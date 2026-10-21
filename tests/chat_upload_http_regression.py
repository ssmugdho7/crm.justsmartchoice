#!/usr/bin/env python3
"""Real multipart HTTP through native CI Upload and PRChat controllers.
Only loopback, temporary storage and connection-local MySQL fixture tables are used.
"""
import base64, hashlib, http.client, io, json, os, pathlib, shutil, socket, struct, subprocess, tempfile, time, wave, zlib
ROOT=pathlib.Path(__file__).resolve().parent.parent
checks=0
def check(ok,label):
    global checks
    if not ok: raise AssertionError(label)
    checks+=1
with tempfile.TemporaryDirectory(prefix='prchat-upload-') as tmp:
    store=pathlib.Path(tmp);(store/'fixture.marker').touch()
    with socket.socket() as sock:
        sock.bind(('127.0.0.1',0));port=sock.getsockname()[1]
    env=dict(os.environ,PRCHAT_UPLOAD_FIXTURE_ROOT=tmp)
    with open(store/'server.log','w+') as log:
        server=subprocess.Popen([shutil.which('php'),'-d','upload_max_filesize=16M','-d','post_max_size=20M','-S',f'127.0.0.1:{port}',str(ROOT/'tests/fixtures/chat_upload_http_router.php')],cwd=ROOT,env=env,stdout=log,stderr=log)
        try:
            for _ in range(50):
                try:
                    with socket.create_connection(('127.0.0.1',port),timeout=.2):break
                except OSError:time.sleep(.1)
            def upload(scope,name,content,fields=None,action='uploadMethod',field='userfile',extra=''):
                if scope=='client-preview': extra+='&chat_context=client'
                boundary='PRChatFixtureBoundary';parts=[]
                for key,value in (fields or {}).items():
                    parts.append(f'--{boundary}\r\nContent-Disposition: form-data; name="{key}"\r\n\r\n{value}\r\n'.encode())
                if name:
                    parts.append(f'--{boundary}\r\nContent-Disposition: form-data; name="{field}"; filename="{name}"\r\nContent-Type: application/octet-stream\r\n\r\n'.encode()+content+b'\r\n')
                parts.append(f'--{boundary}--\r\n'.encode());body=b''.join(parts)
                conn=http.client.HTTPConnection('127.0.0.1',port,timeout=20)
                conn.request('POST',f'/?scope={scope}&action={action}{extra}',body,{'Content-Type':f'multipart/form-data; boundary={boundary}','X-Requested-With':'XMLHttpRequest'})
                res=conn.getresponse();raw=res.read();status=res.status;conn.close()
                try:return status,json.loads(raw)
                except Exception:raise AssertionError(f'Non-JSON response ({status}): {raw[:1200]!r}')
            pdf=b'%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\n%%EOF\n'
            for scope,path,fields,action in [('staff','staff',{},'uploadMethod'),('client','modules/prchat/uploads/clients',{'send_to':'33'},'uploadMethod'),('client-portal','modules/prchat/uploads/clients',{'send_to':'7'},'uploadMethod'),('client-preview','modules/prchat/uploads/clients',{'send_to':'7'},'uploadMethod'),('group','groups',{'to_group':'fixture'},'groupUploadMethod')]:
                status,r=upload(scope,'sample.pdf',pdf,fields,action)
                check(status==200 and r.get('upload_data',{}).get('file_name'),f'{scope} PDF upload: {status} {r}')
                check((store/path/r['upload_data']['file_name']).read_bytes()==pdf,f'{scope} original bytes retained')
                check('full_path' not in r['upload_data'] and 'file_path' not in r['upload_data'],f'{scope} no server path disclosure')
            wav=io.BytesIO()
            with wave.open(wav,'wb') as f:f.setnchannels(1);f.setsampwidth(2);f.setframerate(8000);f.writeframes(b'\0\0'*8000)
            for scope,sub,fields,action in [('staff','staff',{},'uploadMethod'),('client','clients',{'send_to':'33'},'uploadMethod'),('client-portal','clients',{'send_to':'staff_7'},'uploadMethod'),('client-preview','clients',{'send_to':'staff_7'},'uploadMethod'),('group','groups',{'to_group':'fixture'},'groupUploadMethod')]:
                status,r=upload(scope,'voice.wav',wav.getvalue(),dict(fields,prchat_voice_upload='1'),action)
                check(status==200 and r.get('upload_data',{}).get('subfolder')==sub,f'{scope} voice upload: {status} {r}')
                check((store/'audio'/sub/r['upload_data']['file_name']).read_bytes()==wav.getvalue(),f'{scope} recorded audio bytes retained')
            for name,content in [('bad.php',b'<?php echo 1;'),('fake.png',b'not an image')]:
                status,r=upload('client',name,content,{'send_to':'33'})
                check(status==422 and r.get('error'),f'Unsafe attachment rejected: {name}')
            status,r=upload('client','voice.wav',b'RIFF'+b'\0'*(11*1024*1024),{'send_to':'33','prchat_voice_upload':'1'})
            check(status==422,'Oversize voice upload rejected')
            # Staff has global customer-view rights in this fixture; deny a nonexistent contact.
            status,r=upload('client','sample.pdf',pdf,{'send_to':'client_9999'})
            check(status==403,'Nonexistent client upload denied')
            status,r=upload('client-portal','sample.pdf',pdf,{'send_to':'staff_8'})
            check(status==403,'Client portal upload to hidden staff denied')
            status,r=upload('client-preview','sample.pdf',pdf,{'send_to':'staff_8'})
            check(status==403,'Client preview cannot use the concurrent admin session to upload to hidden staff')
            # A wide PNG exercises circular cover rendering without needing a production photo.
            def png(w,h):
                def chunk(t,b):return struct.pack('>I',len(b))+t+b+struct.pack('>I',zlib.crc32(t+b)&0xffffffff)
                return b'\x89PNG\r\n\x1a\n'+chunk(b'IHDR',struct.pack('>IIBBBBB',w,h,8,2,0,0,0))+chunk(b'IDAT',zlib.compress((b'\0'+b'\x22\x88\xaa'*w)*h))+chunk(b'IEND',b'')
            photo=png(240,60)
            status,r=upload('staff','wide.png',photo,{'group_id':'fixture'},'uploadGroupAvatar','group_avatar')
            check(status==200 and r.get('success') and r.get('image'),f'Group photo upload: {status} {r}')
            first=json.loads((store/'avatar.json').read_text());check((store/'groups'/first).read_bytes()==photo,'Group photo bytes saved with unique name')
            status,r=upload('staff',None,b'',{'group_id':'fixture'},'removeGroupAvatar')
            check(status==200 and r.get('success') and not (store/'groups'/first).exists(),'Photo removal updates DB and deletes only managed photo')
            status,r=upload('staff','wide.png',photo,{'group_id':'fixture'},'uploadGroupAvatar','group_avatar')
            second=json.loads((store/'avatar.json').read_text());check(status==200 and second!=first,'Photo can be re-added without stale cache URL')
            status,r=upload('staff','wide.png',photo,{'group_id':'fixture'},'uploadGroupAvatar','group_avatar')
            third=json.loads((store/'avatar.json').read_text());check(status==200 and third!=second and not(store/'groups'/second).exists(),'Replacement preserves new photo and cleans prior managed photo')
            status,r=upload('staff','fake.png',b'not an image',{'group_id':'fixture'},'uploadGroupAvatar','group_avatar')
            check(status==422 and json.loads((store/'avatar.json').read_text())==third,'Invalid replacement preserves existing photo')
            status,r=upload('staff','bad.svg',b'<svg xmlns="http://www.w3.org/2000/svg"/>',{'group_id':'fixture'},'uploadGroupAvatar','group_avatar')
            check(status==422,'Active SVG content cannot be used as group photo')
            status,r=upload('staff','wide.png',photo,{'group_id':'9999'},'uploadGroupAvatar','group_avatar')
            check(status==403,'Unknown group avatar upload denied before storage')
            # Separate request with a failing native DB constraint must not fabricate success.
            upload('staff',None,b'',{'group_id':'fixture'},'removeGroupAvatar')
            status,r=upload('staff','wide.png',photo,{'group_id':'fixture'},'uploadGroupAvatar','group_avatar','&fail=db')
            check(status==500 and r.get('success') is False and not list((store/'groups/avatars').glob('*')),'Failed photo DB save rolls back uploaded file')
            print(f'PASS {checks} real multipart file/voice/avatar, remove/re-add, authorization and failure checks (temporary storage only)')
        except Exception:
            log.flush();log.seek(0);print(log.read()[-4000:]);raise
        finally:server.terminate();server.wait(timeout=5)

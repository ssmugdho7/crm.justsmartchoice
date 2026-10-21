'use strict';
const assert=require('node:assert/strict'),{spawnSync}=require('node:child_process'),vm=require('node:vm');
// Render the actual settings and localized label with the native sprintf contract.
const rendered=spawnSync('php',[],{encoding:'utf8',input:`<?php
function site_url($path){return 'https://fixture.test/'.$path;}
function base_url($path){return site_url($path);}
function _l($key,$label=''){return sprintf($key==='chat_voice_upload_http'?'Upload failed (HTTP %s).':$key,$label);}
$source=file_get_contents('modules/prchat/assets/module_includes/mutual_and_helper_functions.php');
preg_match('/var customerSettings = \\{[\\s\\S]+?\\n  \\};/',$source,$settings);
preg_match('/window\\.prchatI18n\\.chat_voice_upload_http = [^\\n]+;/',$source,$label);
eval('?>'.$settings[0]."\\n".$label[0]);
`});
assert.equal(rendered.status,0,rendered.stderr);
const scope={window:{prchatI18n:{}}};vm.runInNewContext(rendered.stdout,scope);
for(const key of ['clientPusherAuth','getMutualMessages','clientsMessagesPath','getStaffUnreadMessages','updateStaffUnread','uploadMethod','editClientMessage','addReaction']){
 const url=new URL(scope.customerSettings[key]);
 assert.equal(url.searchParams.get('chat_context'),'client',key+' selects the authenticated portal actor');
 assert.ok(url.pathname.startsWith('/prchat/Prchat_ClientsController/'));
}
assert.equal(new URL(scope.customerSettings.clientUploadsBase).search,'','File storage URL stays unchanged');
assert.equal(scope.window.prchatI18n.chat_voice_upload_http.replace('%s','403'),'Upload failed (HTTP 403).','Localized voice errors retain the actual HTTP status');
console.log('PASS portal endpoint context and rendered voice HTTP status label');

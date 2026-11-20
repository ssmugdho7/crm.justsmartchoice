<?php
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
require dirname(__DIR__).'/application/libraries/recovery/DatabaseArchive.php';
mysqli_report(MYSQLI_REPORT_ERROR|MYSQLI_REPORT_STRICT);$db=new mysqli('localhost','root','');$name='crm_recovery_test_'.bin2hex(random_bytes(5));$db->query("CREATE DATABASE `$name`");$db->select_db($name);$checks=0;
function verify($ok,$label){global $checks;if(!$ok)throw new RuntimeException($label);$checks++;echo "PASS $label\n";}
try{
 $db->query('CREATE TABLE tblparents(id INT PRIMARY KEY,total DECIMAL(15,2),name VARCHAR(100),empty_value TEXT NULL) ENGINE=InnoDB');
 $db->query('CREATE TABLE tblfiles(id INT PRIMARY KEY,parent_id INT,bytes LONGBLOB,note TEXT) ENGINE=InnoDB');
 $db->query('CREATE TABLE tblsessions(id VARCHAR(100),data BLOB) ENGINE=InnoDB');
 $archive=new ScDatabaseArchive($db,'tbl');$install=$archive->install();verify($install['protected_tables']===2,'business tables covered; volatile sessions excluded');verify($archive->install()['unchanged']===2,'installation idempotent');
 $db->query("SET @sc_recovery_batch='test-batch-123456789',@sc_recovery_actor='staff:999'");
 $db->query("INSERT INTO tblparents VALUES(1,123.45,'Client ''quoted'' 😀',NULL)");$q=$db->prepare('INSERT INTO tblfiles VALUES(1,1,?,?)');$bytes="\x89PNG\0binary\xff";$note='attachment';$q->bind_param('ss',$bytes,$note);$q->execute();
 $db->begin_transaction();$db->query('DELETE FROM tblparents WHERE id=1');$db->rollback();verify((int)$db->query('SELECT COUNT(*) n FROM tblsc_recovery_rows')->fetch_assoc()['n']===0,'rollback rolls back both deletion and archive');
 $db->begin_transaction();$db->query('DELETE FROM tblfiles WHERE parent_id=1');$db->query('DELETE FROM tblparents WHERE id=1');$db->commit();
 $rows=$db->query('SELECT * FROM tblsc_recovery_rows ORDER BY id')->fetch_all(MYSQLI_ASSOC);verify(count($rows)===2&&$rows[0]['batch_id']===$rows[1]['batch_id'],'related deletions retained as one operation');
 $decoded=json_decode($rows[0]['row_data'],true);verify(base64_decode($decoded['bytes'],true)===$bytes,'binary data retained exactly');$decoded=json_decode($rows[1]['row_data'],true);verify(base64_decode($decoded['total'],true)==='123.45','money precision preserved');verify($decoded['empty_value']===null,'NULL values preserved');verify(base64_decode($decoded['name'],true)==="Client 'quoted' 😀",'Unicode and quoting preserved');
 try{$db->query('DELETE FROM tblsc_recovery_rows');$blocked=false;}catch(mysqli_sql_exception $e){$blocked=true;}verify($blocked,'archive cannot be purged even by a raw SQL delete');
 try{$db->query("UPDATE tblsc_recovery_rows SET actor='changed'");$blocked=false;}catch(mysqli_sql_exception $e){$blocked=true;}verify($blocked,'archived evidence cannot be edited');
 verify($archive->health()['missing_tables']===[],'installed coverage verified');$db->query('ALTER TABLE tblparents ADD new_field TEXT');verify($archive->health()['changed_tables']===['tblparents'],'schema drift detected');$archive->install();verify($archive->health()['changed_tables']===[],'changed schema protection refreshed');
 $db->query('INSERT INTO tblsessions VALUES("test","temporary")');$db->query('DELETE FROM tblsessions');verify(count($db->query('SELECT * FROM tblsc_recovery_rows')->fetch_all())===2,'session cleanup does not retain expired credentials');
 $db->query('CREATE TABLE tblcascade_parent(id INT PRIMARY KEY) ENGINE=InnoDB');
 $db->query('CREATE TABLE tblcascade_child(id INT PRIMARY KEY,parent_id INT,FOREIGN KEY(parent_id) REFERENCES tblcascade_parent(id) ON DELETE CASCADE) ENGINE=InnoDB');
 $db->query('CREATE TABLE tblcascade_grandchild(id INT PRIMARY KEY,child_id INT,data TEXT,FOREIGN KEY(child_id) REFERENCES tblcascade_child(id) ON DELETE CASCADE) ENGINE=InnoDB');
 $db->query('CREATE TABLE tblcascade_nullable(id INT PRIMARY KEY,parent_id INT NULL,FOREIGN KEY(parent_id) REFERENCES tblcascade_parent(id) ON DELETE SET NULL) ENGINE=InnoDB');
 $archive->install();$db->query("SET @sc_recovery_batch='cascade-batch-123456789'");
 $db->query('INSERT INTO tblcascade_parent VALUES(5)');$db->query('INSERT INTO tblcascade_child VALUES(6,5)');$db->query("INSERT INTO tblcascade_grandchild VALUES(7,6,'nested attachment')");$db->query('INSERT INTO tblcascade_nullable VALUES(8,5)');
 $db->query('DELETE FROM tblcascade_parent WHERE id=5');
 $copies=$db->query("SELECT table_name,reason,row_data FROM tblsc_recovery_rows WHERE batch_id='cascade-batch-123456789'")->fetch_all(MYSQLI_ASSOC);
 verify(count($copies)===4,'parent, child, nested child and cleared reference archived');
 verify(count(array_filter($copies,fn($r)=>$r['reason']==='cascade_delete'))===2,'cascaded deletes explicitly retained');
 verify(count(array_filter($copies,fn($r)=>$r['reason']==='reference_clear'))===1,'SET NULL retains the former relationship');
 verify((int)$db->query('SELECT COUNT(*) n FROM tblcascade_child')->fetch_assoc()['n']===0,'original foreign-key cascade still works');
 verify($db->query('SELECT parent_id FROM tblcascade_nullable')->fetch_assoc()['parent_id']===null,'original SET NULL behavior retained');
 $db->query('ALTER TABLE tblcascade_child ADD description TEXT');$archive->install('tblcascade_child');verify($archive->health()['changed_tables']===[],'child schema change refreshes dependent parent guards');
 echo "PASS $checks database recovery checks.\n";
}finally{$db->query("DROP DATABASE `$name`");}

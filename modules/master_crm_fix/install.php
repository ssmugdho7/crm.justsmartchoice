<?php
defined('BASEPATH') or exit('No direct script access allowed');
add_option('master_crm_fix_pack_size_mb','480');
add_option('master_crm_fix_include_database','1');
add_option('master_crm_fix_exclude_archives','1');
add_option('master_crm_fix_include_uploads','1');
$root=dirname(FCPATH).DIRECTORY_SEPARATOR.'CRM MASTER BACKUP';
if(!is_dir($root)){@mkdir($root,0755,true);}
if(!file_exists($root.'/.htaccess')){@file_put_contents($root.'/.htaccess',"Options -Indexes\n");}

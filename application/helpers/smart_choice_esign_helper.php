<?php
defined('BASEPATH') or exit('No direct script access allowed');
function sces_process_initial_image($base64,$folder){
 if(!$base64){return null;} if(!is_dir($folder)){@mkdir($folder,0755,true);} $data=base64_decode($base64,true); if($data===false){return null;}
 $file='initials_'.time().'_'.bin2hex(random_bytes(3)).'.png'; if(file_put_contents(rtrim($folder,'/').'/'.$file,$data)===false){return null;} return $file;
}
function sces_save_initials($type,$id,$base64,$folder){
 $CI=&get_instance(); $file=sces_process_initial_image($base64,$folder); if(!$file){return false;} $table=db_prefix().$type.'s'; if($type==='proposal'){$table=db_prefix().'proposals';}
 if($CI->db->table_exists($table) && $CI->db->field_exists('initials',$table)){ $CI->db->where('id',(int)$id)->update($table,['initials'=>$file]); return $file; } return false;
}
function sces_initial_url($type,$id,$file){
 if(!$file)return ''; $folder=$type==='contract'?CONTRACTS_UPLOADS_FOLDER:($type==='estimate'?ESTIMATE_ATTACHMENTS_FOLDER:PROPOSAL_ATTACHMENTS_FOLDER); $path=$folder.$id.'/'.$file; return site_url('download/preview_image?path='.protected_file_url_by_path($path));
}
function sces_replace_initial_merge_fields($content,$type,$record){
 $file=isset($record->initials)?$record->initials:''; $url=sces_initial_url($type,$record->id,$file); $html=$url?'<img class="sces-inline-initial" src="'.html_escape($url).'" alt="Customer Initials" />':'________________';
 return str_replace(['{Customer_Initial}','{Costumer_Initial}','{Customer_Initials}'],$html,(string)$content);
}

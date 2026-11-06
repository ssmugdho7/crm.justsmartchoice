<?php defined('BASEPATH') or exit('No direct script access allowed');
ob_start();include dirname(__DIR__,3).'/sales_center/views/'.$rep_native.'.php';$native=ob_get_clean();
if(str_ends_with($rep_native,'/form')){
 // One optional editor, created only on explicit request; no eager TinyMCE iframe initialization.
 $native=preg_replace('~(<textarea\b[^>]*id="content"[^>]*class=")tinymce(")~','$1scaf-agreement-editor$2',$native,1);
 $native=str_replace('<textarea name="content"','<button type="button" class="btn btn-default" data-rep-editor>'.e(scaf_rep_label('Open rich-text agreement editor','Abrir editor de texto del acuerdo')).'</button><textarea name="content"',$native);
}
if(str_ends_with($rep_native,'/view')&&isset($contract)){
 $proof=scaf_rep_signature($contract);$label=$proof==='both'?scaf_rep_label('Signed & countersigned','Firmado y contrafirmado'):($proof==='partial'?scaf_rep_label('Awaiting countersignature','Pendiente de contrafirma'):scaf_rep_label('No verified signature record','Sin registro de firma verificada'));
 $audit='<section class="scaf-rep-audit"><strong>'.e($label).'</strong>';
 foreach(['company','salesperson'] as $role){$key=$role.'_signed_at';$ip=$role.'_signed_ip';if(!empty($contract->{$role.'_signature'})&&!empty($contract->$key))$audit.='<p>'.e($role.' · '.$contract->$key.' · '.($contract->$ip??'')).'</p>';}
 $audit.='</section>';$native=preg_replace_callback('~(<div class="content">)~',fn($m)=>$m[1].$audit,$native,1);
}
echo $native;

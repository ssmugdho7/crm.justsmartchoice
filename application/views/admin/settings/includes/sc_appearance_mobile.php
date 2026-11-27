<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$companyPath = get_upload_path_by_type('company');
function sc_bg_meta_403($filename, $basePath) {
    $out = ['dimensions'=>'', 'size'=>''];
    if (!$filename) return $out;
    $file = rtrim($basePath, '/\\') . DIRECTORY_SEPARATOR . basename($filename);
    if (!is_file($file)) return $out;
    $out['size'] = round(filesize($file)/1024, 1) . ' KB';
    $info = @getimagesize($file);
    if ($info) $out['dimensions'] = $info[0] . ' × ' . $info[1] . ' px';
    return $out;
}
$adminBg = get_option('sc_admin_login_background');
$clientBg = get_option('sc_client_login_background');
$adminMeta = sc_bg_meta_403($adminBg, $companyPath);
$clientMeta = sc_bg_meta_403($clientBg, $companyPath);
?>
<div class="row">
  <div class="col-md-12"><h4 class="tw-font-bold"><i class="fa-solid fa-palette"></i> <?= _l('sc_brand_appearance'); ?></h4><p class="text-muted"><?= _l('sc_brand_appearance_help'); ?></p></div>
  <?php $colors = [
    ['sc_menu_icon_color','sc_main_icon_color','#169179'],
    ['sc_submenu_icon_color','sc_submenu_icon_color','#374151'],
    ['sc_menu_text_color','sc_menu_text_color','#334155'],
    ['sc_submenu_text_color','sc_submenu_text_color','#4b5563'],
    ['sc_table_header_color','sc_table_header_color','#3598DB'],
    ['sc_title_color','sc_title_color','#1f2937'],
    ['sc_primary_button_color','sc_primary_button_color','#169179'],
  ]; foreach($colors as $c){ ?>
  <div class="col-md-4"><div class="form-group"><label><?= _l($c[1]); ?></label><div class="input-group"><span class="input-group-addon"><i class="fa fa-droplet"></i></span><input type="color" class="form-control" style="height:38px;padding:4px" name="settings[<?= $c[0]; ?>]" value="<?= e(get_option($c[0]) ?: $c[2]); ?>"></div></div></div>
  <?php } ?>
</div>
<hr>
<h4 class="tw-font-bold"><i class="fa-regular fa-image"></i> <?= _l('sc_login_backgrounds'); ?></h4>
<p class="text-muted"><?= _l('sc_login_background_help'); ?></p>
<div class="row">
 <?php $scBgVer = time(); foreach ([['admin','sc_admin_login_background',$adminBg,$adminMeta],['client','sc_client_login_background',$clientBg,$clientMeta]] as $bg) { $bgUrl = $bg[2] ? base_url('uploads/company/'.basename($bg[2])).'?v='.$scBgVer : ''; ?>
 <div class="col-md-6"><div class="panel panel-default sc-login-bg-card" data-type="<?= $bg[0]; ?>" data-saved-url="<?= e($bgUrl); ?>"><div class="panel-heading"><strong><?= _l($bg[0]==='admin'?'sc_admin_login_background':'sc_client_login_background'); ?></strong></div><div class="panel-body">
   <div class="sc-bg-preview-box" style="height:170px;border:1px dashed #cbd5e1;border-radius:8px;background:#f8fafc;display:flex;align-items:center;justify-content:center;overflow:hidden;margin-bottom:10px"><img class="sc-bg-preview-img" src="<?= e($bgUrl); ?>" style="width:100%;height:100%;object-fit:cover;<?= $bgUrl ? '' : 'display:none'; ?>" alt="background"><span class="text-muted sc-bg-empty" style="<?= $bgUrl ? 'display:none' : ''; ?>"><?= _l('sc_no_background_uploaded'); ?></span></div>
   <div class="tw-flex tw-gap-2 tw-mb-2">
     <button type="button" class="btn btn-default btn-sm sc-login-bg-preview" <?= $bgUrl ? '' : 'disabled'; ?>><i class="fa-regular fa-eye"></i> <?= _l('sc_login_bg_preview'); ?></button>
     <?php if ($bg[2]) { ?><button type="button" class="btn btn-danger btn-sm sc-login-bg-remove" data-url="<?= e(admin_url('settings/remove_login_background/'.$bg[0])); ?>"><i class="fa-regular fa-trash-can"></i> <?= _l('sc_login_bg_remove'); ?></button><?php } ?>
   </div>
   <input type="file" name="<?= $bg[1]; ?>" class="form-control sc-login-bg-input" accept=".jpg,.jpeg,.png,.webp">
   <p class="text-muted mtop10"><strong><?= _l('sc_recommended_image'); ?>:</strong> 1920 × 1080 px, JPG/WebP, 80–90% quality, under 2 MB. Maximum accepted: 5 MB.</p>
   <div class="sc-bg-file-info text-info"><?php if($bg[2]) echo e(trim($bg[3]['dimensions'].' · '.$bg[3]['size'])); ?></div>
 </div></div></div>
 <?php } ?>
</div>
<div class="modal fade" id="sc-login-bg-modal" tabindex="-1" role="dialog" aria-labelledby="sc-login-bg-modal-title">
 <div class="modal-dialog modal-lg" role="document" style="width:min(1100px,96vw)"><div class="modal-content">
  <div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="<?= e(_l('close')); ?>"><span aria-hidden="true">&times;</span></button><h4 class="modal-title" id="sc-login-bg-modal-title"><?= _l('sc_login_bg_preview_title'); ?></h4></div>
  <div class="modal-body" style="padding:0">
   <div class="sc-login-bg-stage" style="position:relative;height:min(62vh,600px);background:#f1f5f9 center/cover no-repeat;display:flex;align-items:center;justify-content:center">
    <div class="sc-login-bg-overlay" style="position:absolute;inset:0"></div>
    <div style="position:relative;width:300px;max-width:86%;background:#fff;border-radius:10px;box-shadow:0 10px 30px rgba(15,23,42,.25);padding:22px 24px">
     <div class="sc-login-bg-heading tw-font-bold tw-text-lg tw-text-center tw-mb-4"></div>
     <div style="height:10px;width:40%;background:#e2e8f0;border-radius:4px;margin-bottom:8px"></div><div style="height:32px;border:1px solid #cbd5e1;border-radius:6px;margin-bottom:14px"></div>
     <div style="height:10px;width:35%;background:#e2e8f0;border-radius:4px;margin-bottom:8px"></div><div style="height:32px;border:1px solid #cbd5e1;border-radius:6px;margin-bottom:18px"></div>
     <div class="btn btn-primary btn-block" style="pointer-events:none"><?= _l('sc_login_bg_mock_button'); ?></div>
    </div>
   </div>
  </div>
  <div class="modal-footer"><span class="text-muted pull-left sc-login-bg-unsaved" style="display:none;margin-top:7px"><i class="fa-solid fa-circle-info"></i> <?= _l('sc_login_bg_unsaved'); ?></span><button type="button" class="btn btn-default" data-dismiss="modal"><?= _l('close'); ?></button></div>
 </div></div>
</div>
<script>
(function(){
 var L={tooLarge:<?= json_encode(_l('sc_image_too_large')); ?>,confirm:<?= json_encode(_l('sc_login_bg_remove_confirm')); ?>,remove:<?= json_encode(_l('sc_login_bg_remove')); ?>,admin:<?= json_encode(_l('admin_auth_login_heading')); ?>,client:<?= json_encode(_l('clients_login_heading_no_register')); ?>};
 var objectUrls={};
 function card(el){return el.closest('.sc-login-bg-card');}
 function currentUrl(c){return objectUrls[c.getAttribute('data-type')]||c.getAttribute('data-saved-url')||'';}
 function setPreview(c,url){var img=c.querySelector('.sc-bg-preview-img'),empty=c.querySelector('.sc-bg-empty'),btn=c.querySelector('.sc-login-bg-preview');if(url){img.src=url;img.style.display='';empty.style.display='none';btn.disabled=false;}else{img.removeAttribute('src');img.style.display='none';empty.style.display='';btn.disabled=true;}}
 document.querySelectorAll('.sc-login-bg-input').forEach(function(el){el.addEventListener('change',function(){
  var c=card(el),type=c.getAttribute('data-type'),f=el.files&&el.files[0],box=c.querySelector('.sc-bg-file-info');
  if(objectUrls[type]){URL.revokeObjectURL(objectUrls[type]);delete objectUrls[type];}
  if(!f){setPreview(c,c.getAttribute('data-saved-url'));return;}
  if(f.size>5242880){box.textContent=L.tooLarge;el.value='';setPreview(c,c.getAttribute('data-saved-url'));return;}
  objectUrls[type]=URL.createObjectURL(f);setPreview(c,objectUrls[type]);
  var im=new Image();im.onload=function(){box.textContent=im.width+' × '+im.height+' px · '+Math.round(f.size/1024)+' KB';};im.src=objectUrls[type];
 });});
 document.querySelectorAll('.sc-login-bg-preview').forEach(function(btn){btn.addEventListener('click',function(){
  var c=card(btn),type=c.getAttribute('data-type'),url=currentUrl(c);if(!url)return;
  var m=document.getElementById('sc-login-bg-modal'),stage=m.querySelector('.sc-login-bg-stage');
  stage.style.backgroundImage='url("'+url.replace(/"/g,'%22')+'")';
  m.querySelector('.sc-login-bg-overlay').style.background=type==='admin'?'rgba(15,23,42,.32)':'rgba(248,250,252,.80)';
  m.querySelector('.sc-login-bg-heading').textContent=type==='admin'?L.admin:L.client;
  m.querySelector('.sc-login-bg-unsaved').style.display=objectUrls[type]?'':'none';
  $(m).modal('show');
 });});
 document.querySelectorAll('.sc-login-bg-remove').forEach(function(btn){btn.addEventListener('click',function(){
  var submit=function(){var f=document.createElement('form');f.method='post';f.action=btn.getAttribute('data-url');var add=function(n,v){var i=document.createElement('input');i.type='hidden';i.name=n;i.value=v;f.appendChild(i);};add('delete_confirmation','DELETE');if(typeof csrfData!=='undefined'&&csrfData&&csrfData.token_name){add(csrfData.token_name,csrfData.hash);}document.body.appendChild(f);f.submit();};
  // Same typed-DELETE confirmation the CRM uses for every remove/delete action (sc_delete_confirmation_helper).
  if(window.SCDelete&&typeof window.SCDelete.confirm==='function'){window.SCDelete.confirm(L.confirm).then(function(ok){if(ok)submit();});}
  else if(window.SCAF&&typeof window.SCAF.confirm==='function'){Promise.resolve(window.SCAF.confirm({title:L.remove,message:L.confirm,typed:'DELETE',actionLabel:L.remove})).then(function(ok){if(ok)submit();});}
  else if(window.prompt(L.confirm+' (DELETE)','')==='DELETE'){submit();}
 });});
})();
</script>

<hr>
<h4 class="tw-font-bold"><i class="fa-solid fa-table-columns"></i> <?= _l('sc_sales_table_settings'); ?></h4>
<p class="text-muted"><?= _l('sc_sales_table_settings_help'); ?></p>
<?php
$scSalesTables = [
 'proposals' => [
  ['number','Proposal #',12],['subject','Subject',18],['to','To',15],['total','Total',10],['date','Date',10],['open_till','Open Till',10],['project','Project',14],['tags','Tags',10],['date_created','Date Created',10],['status','Status',10],
 ],
 'estimates' => [
  ['number','Estimate #',10],['amount','Amount',10],['tax','Tax',8],['year','Year',7],['client','Client',15],['project','Project',14],['tags','Tags',10],['date','Date',10],['expiry','Expiry Date',10],['reference','Reference #',10],['status','Status',10],
 ],
 'invoices' => [
  ['number','Invoice #',10],['amount','Amount',10],['tax','Tax',8],['year','Year',7],['date','Date',10],['client','Client',15],['project','Project',14],['tags','Tags',10],['due','Due Date',10],['status','Status',10],
 ],
];
foreach ($scSalesTables as $scTableKey => $scColumns) { ?>
<div class="panel panel-default">
 <div class="panel-heading"><strong><?= e(ucfirst($scTableKey)); ?></strong></div>
 <div class="panel-body">
  <div class="sc-sales-column-row tw-font-semibold"><span><?= _l('sc_table_column'); ?></span><span><?= _l('sc_table_show'); ?></span><span><?= _l('sc_table_width'); ?></span></div>
  <?php foreach ($scColumns as $scColumn) { $visibleKey='sc_'.$scTableKey.'_col_'.$scColumn[0].'_visible'; $widthKey='sc_'.$scTableKey.'_col_'.$scColumn[0].'_width'; $visible=get_option($visibleKey); if($visible==='')$visible='1'; $width=(int)(get_option($widthKey) ?: $scColumn[2]); ?>
  <div class="sc-sales-column-row">
    <label for="<?= e($visibleKey); ?>"><?= e($scColumn[1]); ?></label>
    <div><input type="hidden" name="settings[<?= e($visibleKey); ?>]" value="0"><input id="<?= e($visibleKey); ?>" type="checkbox" name="settings[<?= e($visibleKey); ?>]" value="1" <?= $visible==='1'?'checked':''; ?>></div>
    <div class="input-group"><input type="number" min="4" max="40" step="1" class="form-control" name="settings[<?= e($widthKey); ?>]" value="<?= e($width); ?>"><span class="input-group-addon">%</span></div>
  </div>
  <?php } ?>
  <p class="sc-sales-table-config-note"><?= _l('sc_sales_table_width_help'); ?></p>
 </div>
</div>
<?php } ?>

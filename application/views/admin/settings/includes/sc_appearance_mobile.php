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
 <?php foreach ([['admin','sc_admin_login_background',$adminBg,$adminMeta],['client','sc_client_login_background',$clientBg,$clientMeta]] as $bg) { ?>
 <div class="col-md-6"><div class="panel panel-default"><div class="panel-heading"><strong><?= _l($bg[0]==='admin'?'sc_admin_login_background':'sc_client_login_background'); ?></strong></div><div class="panel-body">
   <div style="height:170px;border:1px dashed #cbd5e1;border-radius:8px;background:#f8fafc;display:flex;align-items:center;justify-content:center;overflow:hidden;margin-bottom:10px"><?php if($bg[2]){ ?><img src="<?= base_url('uploads/company/'.basename($bg[2])); ?>?v=<?= time(); ?>" style="width:100%;height:100%;object-fit:cover" alt="background"><?php } else { ?><span class="text-muted"><?= _l('sc_no_background_uploaded'); ?></span><?php } ?></div>
   <input type="file" name="<?= $bg[1]; ?>" class="form-control sc-login-bg-input" accept=".jpg,.jpeg,.png,.webp">
   <p class="text-muted mtop10"><strong><?= _l('sc_recommended_image'); ?>:</strong> 1920 × 1080 px, JPG/WebP, 80–90% quality, under 2 MB. Maximum accepted: 5 MB.</p>
   <div class="sc-bg-file-info text-info"><?php if($bg[2]) echo e(trim($bg[3]['dimensions'].' · '.$bg[3]['size'])); ?></div>
 </div></div></div>
 <?php } ?>
</div>
<script>
(function(){document.querySelectorAll('.sc-login-bg-input').forEach(function(el){el.addEventListener('change',function(){var f=el.files&&el.files[0],box=el.parentNode.querySelector('.sc-bg-file-info');if(!f)return;if(f.size>5242880){box.textContent='<?= e(_l('sc_image_too_large')); ?>';el.value='';return;}var r=new FileReader();r.onload=function(ev){var im=new Image();im.onload=function(){box.textContent=im.width+' × '+im.height+' px · '+Math.round(f.size/1024)+' KB';};im.src=ev.target.result;};r.readAsDataURL(f);});});})();
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

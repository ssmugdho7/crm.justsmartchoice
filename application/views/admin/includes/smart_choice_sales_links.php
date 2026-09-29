<?php defined('BASEPATH') or exit('No direct script access allowed');
$scRelType = isset($sc_rel_type) ? (string)$sc_rel_type : '';
$scRelId   = isset($sc_rel_id) ? (int)$sc_rel_id : 0;
$scLinks   = ($scRelType && $scRelId) ? sc_sales_links_get($scRelType,$scRelId) : [];
?>
<div class="panel_s mtop20" id="sc-inline-links-<?= e($scRelType); ?>-<?= e($scRelId); ?>">
  <div class="panel-body">
    <h4 class="tw-font-semibold tw-mt-0"><i class="fa fa-link"></i> <?= _l('sc_manage_links'); ?></h4>
    <?php for($i=0;$i<4;$i++){ $link=$scLinks[$i]??['title'=>'','url'=>'']; ?>
    <div class="row sc-inline-link-row mbot10">
      <div class="col-md-4"><label class="control-label"><?= _l('sc_link_title'); ?></label><input type="text" class="form-control sc-inline-title" value="<?= html_escape($link['title']); ?>" autocomplete="off"></div>
      <div class="col-md-8"><label class="control-label"><?= _l('sc_link_url'); ?></label><input type="url" class="form-control sc-inline-url" value="<?= html_escape($link['url']); ?>" placeholder="https://" autocomplete="off"></div>
    </div>
    <?php } ?>
    <button type="button" class="btn btn-primary sc-inline-save-links" data-rel-type="<?= e($scRelType); ?>" data-rel-id="<?= (int)$scRelId; ?>"><i class="fa fa-save"></i> <?= _l('save'); ?></button>
    <?php if(!$scRelId){ ?><p class="text-muted mtop10 mbot0"><?= _l('sc_links_save_after_document'); ?></p><?php } ?>
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function(){
  if(!window.jQuery){ return; }
  (function($){
    var panel=$('#sc-inline-links-<?= e($scRelType); ?>-<?= (int)$scRelId; ?>');
    if(!panel.length){ return; }
    var storageKey='scSalesLinks:<?= e($scRelType); ?>';
    function collect(){
      var data={rel_type:'<?= e($scRelType); ?>',rel_id:<?= (int)$scRelId; ?>,link_title:[],link_url:[]};
      panel.find('.sc-inline-link-row').each(function(){data.link_title.push($(this).find('.sc-inline-title').val()||'');data.link_url.push($(this).find('.sc-inline-url').val()||'');});
      return data;
    }
    function postLinks(data,onDone){
      $.ajax({url:admin_url+'smart_choice_sales_links/save',method:'POST',data:data,dataType:'json'})
       .done(function(resp){if(resp&&resp.message){alert_float(resp.success?'success':'danger',resp.message);} if(resp&&resp.success&&onDone){onDone();}})
       .fail(function(){alert_float('danger','<?= e(_l('sc_links_save_failed')); ?>');});
    }
    panel.on('click','.sc-inline-save-links',function(){var data=collect();if(!data.rel_id){try{sessionStorage.setItem(storageKey,JSON.stringify(data));}catch(e){}alert_float('info','<?= e(_l('sc_links_save_after_document')); ?>');return;}postLinks(data);});
    if(<?= (int)$scRelId; ?> > 0){try{var pending=JSON.parse(sessionStorage.getItem(storageKey)||'null');if(pending){pending.rel_id=<?= (int)$scRelId; ?>;postLinks(pending,function(){sessionStorage.removeItem(storageKey);});}}catch(e){}}
    var form=panel.closest('form');
    if(form.length&&<?= (int)$scRelId; ?>===0){form.on('submit.scSalesLinks',function(){try{sessionStorage.setItem(storageKey,JSON.stringify(collect()));}catch(e){}});}
  })(window.jQuery);
});
</script>

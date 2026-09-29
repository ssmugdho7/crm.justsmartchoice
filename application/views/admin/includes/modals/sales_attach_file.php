<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal fade" tabindex="-1" id="sales_attach_file" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">
                    <?= _l('invoice_attach_file'); ?>
                </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <?= form_open_multipart('admin/misc/upload_sales_file', ['id' => 'sales-upload', 'class' => 'dropzone']); ?>
                        <input type="file" name="file" multiple />
                        <?= form_close(); ?>
                        <div class="row mtop15" id="sales_uploaded_files_preview">
                        </div>
                        <div class="panel panel-default mtop20" id="sc-sales-links-editor">
                            <div class="panel-heading"><i class="fa fa-link"></i> <?= _l('sc_manage_links'); ?></div>
                            <div class="panel-body">
                                <?php for ($scLinkIndex = 0; $scLinkIndex < 4; $scLinkIndex++) { ?>
                                <div class="row mbot10 sc-sales-link-row">
                                    <div class="col-md-4"><?= render_input('sc_link_title_' . $scLinkIndex, 'sc_link_title', '', 'text', ['class'=>'sc-link-title']); ?></div>
                                    <div class="col-md-8"><?= render_input('sc_link_url_' . $scLinkIndex, 'sc_link_url', '', 'url', ['class'=>'sc-link-url','placeholder'=>'https://']); ?></div>
                                </div>
                                <?php } ?>
                                <button type="button" class="btn btn-primary" id="sc-save-sales-links"><i class="fa fa-save"></i> <?= _l('save'); ?></button>
                            </div>
                        </div>
                        <div class="tw-flex tw-justify-end tw-items-center tw-space-x-2">
                            <button class="gpicker" data-on-pick="salesGoogleDriveSave">
                                <i class="fa-brands fa-google" aria-hidden="true"></i>
                                <?= _l('choose_from_google_drive'); ?>
                            </button>
                            <div id="dropbox-chooser-sales"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->
<script>
(function(){
  function scSalesRel(){return {type:$('input[name="_attachment_sale_type"]').val()||'',id:parseInt($('input[name="_attachment_sale_id"]').val()||0,10)};}
  function scLoadLinks(){var r=scSalesRel(); if(!r.type||!r.id)return; $('#sc-sales-links-editor .sc-link-title,#sc-sales-links-editor .sc-link-url').val(''); requestGetJSON('smart_choice_sales_links/get/'+encodeURIComponent(r.type)+'/'+r.id).done(function(x){if(!x||!x.links)return; $.each(x.links.slice(0,4),function(i,l){$('#sc-sales-links-editor .sc-link-title').eq(i).val(l.title||''); $('#sc-sales-links-editor .sc-link-url').eq(i).val(l.url||'');});});}
  $('#sales_attach_file').on('shown.bs.modal',scLoadLinks);
  $(document).off('click.scSalesLinks','#sc-save-sales-links').on('click.scSalesLinks','#sc-save-sales-links',function(){var r=scSalesRel();if(!r.type||!r.id)return;var d={rel_type:r.type,rel_id:r.id,link_title:[],link_url:[]}; $('#sc-sales-links-editor .sc-sales-link-row').each(function(){d.link_title.push($(this).find('.sc-link-title').val());d.link_url.push($(this).find('.sc-link-url').val());}); requestPostJSON('smart_choice_sales_links/save',d).done(function(x){alert_float(x&&x.success?'success':'danger',x&&x.message?x.message:'');});});
})();
</script>
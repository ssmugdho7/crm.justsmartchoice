<div class="modal fade" id="modalInstallPopup" tabindex="-1" role="dialog" aria-labelledby="createModalLabel" aria-hidden="true">
<div class="modal-dialog modal-lg" role="document">
  <div class="modal-content">
    <div class="modal-header">
      <h3 class="modal-title" id="exampleModalLabel"><?php echo _l('install_popup'); ?></h3>
    </div>
    <div class="modal-body">
        <div class="tab-content mbottom20">
            <p class="text-muted"><?php echo _l('Copy and paste the following JS Code Snippet before the end of the </head> of your website.'); ?></p>
            <pre id="popup_key_html" class="pre-custom rounded"></pre>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
    </div>
  </div>
</div>
<?php defined('BASEPATH') or exit('No direct script access allowed');
$scRelType = isset($sc_rel_type) ? (string) $sc_rel_type : '';
$scRelId   = isset($sc_rel_id) ? (int) $sc_rel_id : 0;
$rows      = [];
if ($scRelType && $scRelId) {
    $rows = $this->db->where('rel_type', $scRelType)
        ->where('rel_id', $scRelId)
        ->order_by('dateadded', 'desc')
        ->get(db_prefix() . 'files')->result_array();
}
?>
<div class="panel_s mtop20 sc-sales-attachments-panel">
    <div class="panel-body">
        <h4 class="tw-font-semibold tw-mt-0"><i class="fa fa-paperclip"></i> <?= _l('attachments'); ?></h4>
        <?php if (empty($sc_hide_upload)) { ?>
            <input type="file" name="sales_attachments[]" class="form-control" multiple accept="image/*,.pdf">
            <p class="text-muted mtop5"><?= _l('sc_upload_files_help'); ?></p>
        <?php } ?>
        <?php if ($rows) { ?>
        <div class="row mtop15">
            <?php foreach ($rows as $a) {
                $url = sc_sales_attachment_download_url($a);
                $preview = sc_sales_attachment_preview_url($a);
                $ext  = strtolower(pathinfo((string) $a['file_name'], PATHINFO_EXTENSION));
                $mime = strtolower((string) $a['filetype']);
                $img  = strpos($mime, 'image/') === 0 || in_array($ext, ['jpg','jpeg','png','gif','webp'], true);
                $pdf  = $ext === 'pdf' || strpos($mime, 'pdf') !== false;
            ?>
            <div class="col-md-3 col-sm-4 col-xs-6 mbot15">
                <div class="sc-admin-attachment-card">
                    <div class="sc-admin-attachment-title">
                        <i class="<?= e(get_mime_class($a['filetype'])); ?>"></i>
                        <span><?= e($a['file_name']); ?></span>
                    </div>
                    <?php if ($img) { ?>
                        <a href="<?= e($preview); ?>" target="_blank" rel="noopener" class="sc-admin-attachment-image-link">
                            <img src="<?= e($preview); ?>" alt="<?= e($a['file_name']); ?>" loading="lazy">
                        </a>
                    <?php } elseif ($pdf) { ?>
                        <iframe class="sc-admin-pdf-preview" src="<?= e($preview); ?>#toolbar=1&navpanes=0" title="<?= e($a['file_name']); ?>" loading="lazy"></iframe>
                    <?php } else { ?>
                        <div class="sc-admin-attachment-generic"><i class="<?= e(get_mime_class($a['filetype'])); ?> fa-3x"></i></div>
                    <?php } ?>
                    <div class="mtop8">
                        <a class="btn btn-default btn-xs" href="<?= e($preview); ?>" target="_blank" rel="noopener">
                            <i class="fa fa-external-link"></i> <?= _l('sc_open_attachment_new_tab'); ?>
                        </a>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
        <?php } ?>
    </div>
</div>

<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="panel_s">
    <div class="panel-body">
        <h4><?php echo _l('field_signer_project_records'); ?> - <?php echo $project_id; ?></h4>
        <?php foreach($signs as $s){ ?>
            <div class="row">
                <div class="col-md-4">
                    <?php if($s['photo']){ ?>
                        <img src="<?php echo site_url(FIELD_SIGNER_MODULE_UPLOAD_PATH.'/'.$s['photo']); ?>" class="img img-responsive" />
                    <?php } ?>
                </div>
                <div class="col-md-4">
                    <?php if($s['signature']){ ?>
                        <img src="<?php echo site_url(FIELD_SIGNER_MODULE_UPLOAD_PATH.'/'.$s['signature']); ?>" class="img img-responsive" />
                    <?php } ?>
                </div>
                <div class="col-md-4">
                    <strong><?php echo $s['title']; ?></strong>
                    <p><?php echo $s['note']; ?></p>
                    <small><?php echo $s['date_created']; ?></small>
                </div>
            </div>
            <hr/>
        <?php } ?>
    </div>
</div>

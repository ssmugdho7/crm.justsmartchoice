<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<details class="sc-prefix-editor mbot15" data-url="<?= e(admin_url('smart_choice_sales/update_number_settings/' . $prefix_type . '/' . $prefix_id)); ?>">
    <summary class="text-info" style="cursor:pointer">
        <i class="fa fa-cog" aria-hidden="true"></i> <?= e(_l('sc_edit_document_prefix')); ?>
    </summary>
    <div class="mtop10">
        <label for="sc-prefix-<?= e($prefix_type); ?>"><?= e(_l('sc_document_prefix')); ?></label>
        <div class="input-group">
            <input id="sc-prefix-<?= e($prefix_type); ?>" class="form-control sc-prefix-value" type="text" maxlength="50" value="<?= e($document_prefix); ?>">
            <span class="input-group-btn"><button type="button" class="btn btn-default sc-save-prefix"><?= e(_l('save')); ?></button></span>
        </div>
        <p class="text-muted mtop5"><?= e(_l('sc_document_prefix_help')); ?></p>
        <p class="sc-prefix-feedback" role="status" aria-live="polite"></p>
    </div>
</details>

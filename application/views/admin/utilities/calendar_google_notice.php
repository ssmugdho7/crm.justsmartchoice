<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php if (!empty($google_calendar_unavailable)): ?>
<div class="alert alert-warning" role="status">
    <?= e(_l('sc_google_calendar_unavailable', (int) $google_calendar_unavailable)); ?>
    <?php if (is_admin()): ?>
        <a class="alert-link" href="<?= admin_url('departments'); ?>"><?= _l('setup_calendar_by_departments'); ?></a>
        &middot; <a class="alert-link" href="<?= admin_url('settings?group=google'); ?>"><?= _l('settings'); ?></a>
    <?php endif; ?>
</div>
<?php endif; ?>

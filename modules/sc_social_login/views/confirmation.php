<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="alert alert-info scsl-confirmation" role="status">
    <strong><?= html_escape(_l('scsl_confirm_title')); ?></strong>
    <?php if (!empty($pending['email'])): ?><p style="margin:8px 0;overflow-wrap:anywhere"><?= html_escape(_l('scsl_provider_'.$pending['provider'])); ?>: <strong><?= html_escape($pending['email']); ?></strong></p><?php endif; ?>
    <p><?= html_escape(_l('scsl_confirm_hint')); ?></p>
    <p><?= html_escape(_l('scsl_confirm_other_email')); ?></p>
    <?php if ($can_register): ?><p class="mtop10"><a href="<?= site_url('authentication/register'); ?>"><?= html_escape(_l('scsl_confirm_register')); ?></a></p><?php endif; ?>
</div>

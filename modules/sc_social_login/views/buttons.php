<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="mtop20 text-center" aria-label="<?= html_escape(_l($registration ? 'scsl_signup' : 'scsl_signin')); ?>">
    <p class="text-muted"><?= _l($registration ? 'scsl_signup' : 'scsl_signin'); ?></p>
    <?php foreach ($providers as $provider): ?>
    <a class="btn btn-default mtop5" href="<?= site_url('sc_social_login/oauth/start/'.$provider.'/'.$audience); ?>"><?= _l('scsl_continue_'.$provider); ?></a>
    <?php endforeach; ?>
</div>

<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="mtop20 text-center" aria-label="<?= html_escape(_l('scsl_signin')); ?>">
    <p class="text-muted"><?= _l('scsl_signin'); ?></p>
    <?php foreach (['google','facebook','apple'] as $provider): if (!scsl_ready($provider,$audience)) continue; ?>
    <a class="btn btn-default mtop5" href="<?= site_url('sc_social_login/oauth/start/'.$provider.'/'.$audience); ?>"><?= _l('scsl_continue_'.$provider); ?></a>
    <?php endforeach; ?>
</div>

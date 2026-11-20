<?php defined('BASEPATH') or exit('No direct script access allowed');
$displayProviders = array_values(array_unique(array_merge(['facebook', 'google'], $providers)));
$buttonAsset = 'assets/css/sign-in-buttons.css';
?>
<link rel="stylesheet" href="<?= html_escape(module_dir_url('sc_social_login', $buttonAsset) . '?v=' . filemtime(dirname(__DIR__) . '/' . $buttonAsset)); ?>">
<div class="scsl-sign-in mtop20" aria-label="<?= html_escape(_l($registration ? 'scsl_signup' : 'scsl_signin')); ?>">
    <p class="scsl-sign-in-heading"><?= _l($registration ? 'scsl_signup' : 'scsl_signin'); ?></p>
    <?php foreach ($displayProviders as $provider): $available = in_array($provider, $providers, true); ?>
    <?php if ($available): ?>
    <a class="scsl-provider-button scsl-provider-<?= html_escape($provider); ?>" href="<?= site_url('sc_social_login/oauth/start/'.$provider.'/'.$audience); ?>">
    <?php else: ?>
    <button type="button" class="scsl-provider-button scsl-provider-<?= html_escape($provider); ?>" disabled aria-describedby="scsl-unavailable-<?= html_escape($provider); ?>">
    <?php endif; ?>
        <span class="scsl-provider-icon" aria-hidden="true"><?= $provider === 'facebook' ? 'f' : ($provider === 'google' ? 'G' : '&#63743;'); ?></span>
        <span><?= _l(($registration ? 'scsl_signup_' : 'scsl_signin_') . $provider); ?></span>
    <?= $available ? '</a>' : '</button>'; ?>
    <?php if (!$available): ?><small class="scsl-provider-unavailable" id="scsl-unavailable-<?= html_escape($provider); ?>"><?= _l('scsl_unavailable_'.$provider); ?></small><?php endif; ?>
    <?php endforeach; ?>
    <?php if ($audience==='staff' && in_array('google',$providers,true)): ?>
    <p class="mtop15"><a href="<?= site_url('sc_social_login/oauth/start/google/staff'); ?>"><?= _l('scsl_staff_signup_google'); ?></a></p>
    <?php endif; ?>
</div>

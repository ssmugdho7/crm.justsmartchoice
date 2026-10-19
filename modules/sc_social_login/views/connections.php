<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="panel_s"><div class="panel-body">
    <h2 class="h4"><?= _l('scsl_connections'); ?></h2><p><?= _l('scsl_connections_hint'); ?></p>
    <?php if (scsl_license() && ($audience==='client' || scsl_can('create'))): ?>
    <div class="row"><?php foreach (['google','facebook','apple'] as $provider): if(!scsl_ready($provider,$audience)) continue; ?>
        <div class="col-sm-4"><?= form_open(site_url('sc_social_login/oauth/start/'.$provider.'/'.$audience.'/link')); ?><button class="btn btn-default" type="submit"><?= _l('scsl_connect_'.$provider); ?></button><?= form_close(); ?></div>
    <?php endforeach; ?></div><hr>
    <?php endif; ?>
    <div class="table-responsive"><table class="table"><thead><tr><th><?= _l('scsl_provider'); ?></th><th><?= _l('scsl_account'); ?></th><th><?= _l('scsl_status'); ?></th><th><?= _l('scsl_actions'); ?></th></tr></thead><tbody>
    <?php foreach ($identities as $identity): ?><tr><td><?= _l('scsl_provider_'.$identity['provider']); ?></td><td><?= _l('scsl_audience_'.$identity['audience']); ?> #<?= (int)$identity['user_id']; ?></td><td><?= _l($identity['enabled']?'scsl_enabled':'scsl_disabled'); ?></td><td>
        <?php if ($audience==='client' || scsl_can('delete') || scsl_can('edit')): ?>
        <?= form_open($audience==='staff'?admin_url('sc_social_login/remove/'.(int)$identity['id']):site_url('sc_social_login/account/remove/'.(int)$identity['id'])); ?>
        <?php if ($audience==='client' || scsl_can('delete')): ?><button type="submit" name="action" value="delete" class="btn btn-danger btn-sm" onclick="return confirm(<?= html_escape(json_encode(_l('scsl_unlink_confirm'))); ?>)"><?= _l('scsl_unlink'); ?></button><?php endif; ?>
        <?php if ($audience==='staff' && scsl_can('edit') && $identity['enabled']): ?><button type="submit" name="action" value="disable" class="btn btn-default btn-sm"><?= _l('scsl_disable'); ?></button><?php endif; ?>
        <?= form_close(); ?><?php endif; ?>
    </td></tr><?php endforeach; ?></tbody></table></div>
    <?php if (!$identities): ?><p class="text-muted"><?= _l('scsl_none'); ?></p><?php endif; ?>
</div></div>

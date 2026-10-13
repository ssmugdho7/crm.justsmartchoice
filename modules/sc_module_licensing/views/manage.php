<?php defined('BASEPATH') or exit('No direct script access allowed'); init_head(); ?>
<div id="wrapper"><div class="content">
    <div class="panel_s"><div class="panel-body">
        <h1 class="h3"><?= _l('scl_title'); ?></h1><p class="text-muted"><?= _l('scl_subtitle'); ?></p>
        <ul class="nav nav-tabs" role="navigation" aria-label="<?= html_escape(_l('scl_title')); ?>">
            <?php foreach (['activation'=>scl_can('manage_settings'), 'licenses'=>scl_issuer() && (scl_can('view') || scl_can('view_own') || scl_can('create')), 'help'=>true] as $item=>$allowed): if (!$allowed) continue; ?>
            <li class="<?= $page === $item ? 'active' : ''; ?>"><a href="<?= admin_url('sc_module_licensing/' . $item); ?>" <?= $page === $item ? 'aria-current="page"' : ''; ?>><?= _l('scl_' . $item); ?></a></li>
            <?php endforeach; ?>
        </ul>
        <?php if ($page === 'activation'): ?>
            <p><?= _l('scl_activation_hint'); ?></p>
            <?= form_open(admin_url('sc_module_licensing/activation'), ['autocomplete'=>'off']); ?>
                <?= render_input('module', 'scl_module', 'sc_social_login'); ?>
                <?= render_input('activation_key', 'scl_key', '', 'password', ['autocomplete'=>'new-password']); ?>
                <button class="btn btn-primary" type="submit"><?= _l('scl_activate'); ?></button>
            <?= form_close(); ?>
            <hr><table class="table"><thead><tr><th><?= _l('scl_module'); ?></th><th><?= _l('scl_status'); ?></th></tr></thead><tbody>
                <?php foreach ($activations as $activation): ?><tr><td><?= html_escape($activation['module']); ?></td><td><?= _l(scl_licensed($activation['module']) ? 'scl_valid' : 'scl_inactive'); ?></td></tr><?php endforeach; ?>
            </tbody></table>
        <?php elseif ($page === 'licenses'): ?>
            <?php if ($issued_key !== ''): ?><div class="alert alert-success"><p><?= _l('scl_key_once'); ?></p><code><?= html_escape($issued_key); ?></code></div><?php endif; ?>
            <?php if (scl_can('create')): ?>
                <?= form_open(admin_url('sc_module_licensing/licenses'), ['autocomplete'=>'off']); ?>
                <div class="row"><div class="col-md-6"><?= render_input('company','scl_company'); ?><?= render_input('module','scl_module','sc_social_login'); ?></div>
                <div class="col-md-6"><?= render_input('site','scl_site','','url'); ?><label for="years"><?= _l('scl_years'); ?></label><select id="years" name="years" class="form-control"><?php foreach ([1,2,3,4,5] as $year): ?><option value="<?= $year; ?>"><?= $year; ?></option><?php endforeach; ?></select></div></div>
                <button type="submit" class="btn btn-primary mtop15"><?= _l('scl_issue'); ?></button>
                <?= form_close(); ?><hr>
            <?php endif; ?>
            <p class="text-muted"><?= _l('scl_revoke_hint'); ?></p>
            <div class="table-responsive"><table class="table"><thead><tr><?php foreach (['company','module','site','expires','status','actions'] as $label): ?><th><?= _l('scl_' . $label); ?></th><?php endforeach; ?></tr></thead><tbody>
            <?php foreach ($licenses as $license): ?><tr>
                <td><?= html_escape($license['company']); ?></td><td><?= html_escape($license['module']); ?></td><td><?= html_escape($license['site']); ?></td>
                <td><?= gmdate('Y-m-d', $license['expires']); ?></td><td><?= _l($license['active'] && $license['expires'] > time() ? 'scl_valid' : 'scl_inactive'); ?></td>
                <td><?php if (scl_can('edit') || scl_can('delete')): ?><?= form_open(admin_url('sc_module_licensing/change/' . (int) $license['id'])); ?>
                    <?php if (scl_can('edit')): ?>
                    <label class="sr-only" for="expires-<?= (int) $license['id']; ?>"><?= _l('scl_expires'); ?></label><input id="expires-<?= (int) $license['id']; ?>" type="date" name="expires" value="<?= gmdate('Y-m-d', $license['expires']); ?>">
                    <?php foreach (['extend','revoke','enable'] as $action): ?><button class="btn btn-default btn-sm" type="submit" name="action" value="<?= $action; ?>"><?= _l('scl_' . $action); ?></button><?php endforeach; ?>
                    <?php endif; ?>
                    <?php if (scl_can('delete')): ?><button class="btn btn-danger btn-sm" type="submit" name="action" value="delete" onclick="return confirm(<?= html_escape(json_encode(_l('scl_delete_confirm'))); ?>)"><?= _l('scl_delete'); ?></button><?php endif; ?>
                <?= form_close(); ?><?php endif; ?></td>
            </tr><?php endforeach; ?></tbody></table></div>
        <?php else: ?>
            <h2 class="h4"><?= _l('scl_help_title'); ?></h2><p><?= _l('scl_help_keys'); ?></p><p><?= _l('scl_help_trust'); ?></p><p><?= _l('scl_help_future'); ?></p>
        <?php endif; ?>
    </div></div>
</div></div>
<?php init_tail(); ?>

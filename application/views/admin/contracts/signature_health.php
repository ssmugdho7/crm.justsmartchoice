<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <h4 class="tw-mt-0 tw-mb-3 tw-font-bold tw-text-xl"><?= _l('signature_health'); ?></h4>
                <div class="tw-mb-3">
                    <a href="<?= admin_url('contracts'); ?>" class="btn btn-default"><?= _l('contracts'); ?></a>
                    <?= form_open(admin_url('contracts/signature_health'), ['class' => 'tw-inline']); ?>
                    <button type="submit" name="backup_signatures" value="1" class="btn btn-primary">
                        <i class="fa-solid fa-database tw-mr-1"></i><?= _l('signature_health_backup'); ?>
                    </button>
                    <?= form_close(); ?>
                </div>
                <div class="panel_s">
                    <div class="panel-body">
                        <?php if (empty($missing)) { ?>
                        <p class="text-success no-mbot"><?= _l('signature_health_none'); ?></p>
                        <?php } else { ?>
                        <p class="text-muted"><?= _l('signature_health_missing_intro'); ?></p>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th><?= _l('signature_health_document'); ?></th>
                                        <th><?= _l('client'); ?></th>
                                        <th><?= _l('contract_signed_by'); ?></th>
                                        <th><?= _l('contract_signed_date'); ?></th>
                                        <th><?= _l('contract_signed_ip'); ?></th>
                                        <th><?= _l('signature_health_file'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($missing as $item) { ?>
                                    <tr>
                                        <td><a href="<?= e($item['link']); ?>"><?= e($item['id']); ?></a></td>
                                        <td><?= e(_l($item['type'])); ?></td>
                                        <td><?= e($item['client']); ?></td>
                                        <td><?= e($item['signer']); ?></td>
                                        <td><?= e($item['date'] ? _dt($item['date']) : ''); ?></td>
                                        <td><?= e($item['ip']); ?></td>
                                        <td><?= e($item['file']); ?></td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
</body>
</html>

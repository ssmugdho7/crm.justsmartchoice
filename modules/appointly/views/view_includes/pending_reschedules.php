<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php if (!empty($pending_reschedules) && staff_can('edit', 'appointments')) : ?>
    <div class="col-md-12 tw-px-0">
        <div class="panel_s">
            <div class="panel-body">
                <h4 class="pull-left"><?= _l('appointment_pending_reschedules'); ?></h4>
                <div class="clearfix"></div>
                <hr class="mbot0">

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th><?= _l('appointment_subject'); ?></th>
                                <th><?= _l('appointment_current_date'); ?></th>
                                <th><?= _l('appointment_requested_date'); ?></th>
                                <th><?= _l('appointment_requested_by'); ?></th>
                                <th><?= _l('appointment_reschedule_reason'); ?></th>
                                <th><?= _l('options'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pending_reschedules as $reschedule) : ?>
                                <tr>
                                    <td><?= $reschedule['subject']; ?></td>
                                    <td><?= _d($reschedule['date']); ?> <?= date("H:i A", strtotime($reschedule['start_hour'])); ?></td>
                                    <td><?= _d($reschedule['requested_date']); ?> <?= date("H:i A", strtotime($reschedule['requested_time'])); ?></td>
                                    <td>
                                        <?php
                                        if (isset($reschedule['contact_name'])) {
                                            echo $reschedule['contact_name'];
                                        } else {
                                            echo $reschedule['name'] ?: _l('appointment_no_name_provided');
                                        }
                                        ?>
                                    </td>
                                    <td><?= $reschedule['reason']; ?></td>
                                    <td>
                                        <a href="<?= admin_url('appointly/appointments/view?appointment_id=' . $reschedule['appointment_id']); ?>" class="btn btn-default btn-icon">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        <button class="btn btn-success btn-icon" onclick="approveReschedule(<?= $reschedule['id']; ?>)" title="<?= _l('appointment_approve_reschedule'); ?>">
                                            <i class="fa fa-check"></i>
                                        </button>
                                        <button class="btn btn-danger btn-icon" onclick="denyReschedule(<?= $reschedule['id']; ?>)" title="<?= _l('appointment_deny_reschedule'); ?>">
                                            <i class="fa fa-times"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

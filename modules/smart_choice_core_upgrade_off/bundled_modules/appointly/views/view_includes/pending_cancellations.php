<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php if (!empty($pending_cancellations) && staff_can('edit', 'appointments')) : ?>
    <div class="col-md-12 tw-px-0">
        <div class="panel_s">
            <div class="panel-body">
                <h4 class="pull-left"><?= _l('appointment_pending_cancellations'); ?></h4>
                <div class="clearfix"></div>
                <hr class="mbot0">

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th><?= _l('appointment_subject'); ?></th>
                                <th><?= _l('appointment_meeting_date'); ?></th>
                                <th><?= _l('appointment_requested_by'); ?></th>
                                <th><?= _l('appointment_cancellation_description_label'); ?></th>
                                <th><?= _l('options'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pending_cancellations as $cancellation) : ?>
                                <tr>
                                    <td><?= $cancellation['subject']; ?></td>
                                    <td><?= _d($cancellation['date']); ?> <?= date("H:i A", strtotime($cancellation['start_hour'])); ?></td>
                                    <td>
                                        <?php
                                        if (isset($cancellation['contact_name'])) {
                                            echo $cancellation['contact_name'];
                                        } else {
                                            echo $cancellation['name'] ?: _l('appointment_no_name_provided');
                                        }
                                        ?>
                                    </td>
                                    <td><?= $cancellation['cancel_notes']; ?></td>
                                    <td>
                                        <a href="<?= admin_url('appointly/appointments/view?appointment_id=' . $cancellation['id']); ?>" class="btn btn-default btn-icon">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        <button class="btn btn-danger btn-icon" onclick="approveCancellation(<?= $cancellation['id']; ?>)" title="<?= _l('appointment_approve_cancellation'); ?>">
                                            <i class="fa fa-check"></i>
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

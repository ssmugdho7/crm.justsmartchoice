<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<link href="<?php echo module_dir_url('ticket_feedback', 'assets/css/client_feedback.css'); ?>" rel="stylesheet"
    type="text/css">

<div class="wrap-client-feedback">
    <h1><?php echo _l('My Ticket Feedback'); ?></h1>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th><?php echo _l('Ticket Number'); ?></th>
                <th><?php echo _l('Subject'); ?></th>
                <th><?php echo _l('Status'); ?></th>
                <th><?php echo _l('Last Reply'); ?></th>
                <th><?php echo _l('Feedback Status'); ?></th>
                <th><?php echo _l('Action'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($tickets)) { ?>
                <?php foreach ($tickets as $ticket) { ?>
                    <tr>
                        <td><?php echo $ticket['ticketid']; ?></td>
                        <td>
                            <a href="<?php echo site_url('clients/ticket/' . $ticket['ticketid']); ?>">
                                #<?php echo $ticket['ticketid']; ?>
                            </a>
                        </td>
                        <td><?php echo $ticket['subject']; ?></td>
                        <td>
                            <span class="badge <?php echo ticket_status_class($ticket['status']); ?>">
                                <?php echo ticket_status_translate($ticket['status']); ?>
                            </span>
                        </td>
                        <td><?php echo _dt($ticket['lastreply']); ?></td>
                        <td>
                            <?php if (isset($ticket['feedback_received']) && $ticket['feedback_received'] == 1): ?>
                                <span class="badge badge-success">Feedback Submitted</span>
                            <?php else: ?>
                                <span class="badge badge-warning">Pending</span>
                            <?php endif; ?>

                        </td>
                        <td>
                        <?php if (isset($ticket['feedback_received']) && $ticket['feedback_received'] != 1){ ?>
                                <a href="<?php echo site_url('ticket_feedback/Client_ticket_feedback/submit_feedback_form/' . $ticket['ticketid']); ?>"
                                    class="btn btn-sm btn-primary">
                                    <i class="fa fa-comment"></i> <?php echo _l('Submit Feedback'); ?>
                                </a>
                            <?php } else { ?>
                                <button class="btn btn-sm btn-success" disabled>
                                    <i class="fa fa-check"></i> <?php echo _l('Completed'); ?>
                                </button>
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
            <?php } else { ?>
                <tr>
                    <td colspan="7" class="text-center"><?php echo _l('No feedback requests found.'); ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>
<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php init_head(); ?> <!-- ✅ Adds Perfex header -->

<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-heading">
                        <h2 class="panel-title"><?= _l('Ticket Feedback Management'); ?></h2>
                    </div>
                    <div class="panel-body">

                        <!-- Ticket Selection Form -->
                        <form action="<?= admin_url('ticket_feedback/ticket_feedback_controller/send_feedback_email'); ?>"
                            method="POST">
                            <!-- CSRF Token -->
                            <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>"
                                value="<?= $this->security->get_csrf_hash(); ?>">

                            <div class="form-group">
                                <label>Select a Ticket:</label>
                                <select name="ticket_id" class="form-control selectpicker" data-live-search="true">
                                    <?php foreach ($tickets as $ticket): ?>
                                        <option value="<?= $ticket['ticketid']; ?>">
                                            #<?= $ticket['ticketid']; ?> - <?= $ticket['subject']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-envelope"></i> Send Feedback Request
                            </button>
                        </form>


                        <hr>

                        <!-- Feedback Requests Table -->
                        <h3>Feedback Requests</h3>
                        <div class="table-responsive">
                            <table class="table table-striped dt-table" id="feedbackTable">
                                <thead>
                                    <tr>
                                        <th><?= _l('Ticket ID'); ?></th>
                                        <th><?= _l('Email Sent'); ?></th>
                                        <th><?= _l('Feedback Received'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($feedback_requests as $request): ?>
                                        <tr>
                                            <td>#<?= $request['ticket_id']; ?></td>
                                            <td>
                                                <span
                                                    class="label <?= $request['email_sent'] ? 'label-success' : 'label-danger'; ?>">
                                                    <?= $request['email_sent'] ? 'Yes' : 'No'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span
                                                    class="label <?= $request['feedback_received'] ? 'label-success' : 'label-warning'; ?>">
                                                    <?= $request['feedback_received'] ? 'Yes' : 'Pending'; ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?> <!-- ✅ Adds Perfex footer -->

<!-- DataTable Script -->
<script>
    $(document).ready(function () {
        $('#feedbackTable').DataTable({
            "paging": true,
            "searching": true,
            "ordering": true,
            "responsive": true,
            "language": {
                "search": "<?= _l('Search:'); ?>"
            }
        });
    });
</script>

</body>

</html>
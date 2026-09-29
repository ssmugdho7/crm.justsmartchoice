<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper" class="feedback_tracking">
    <div class="content">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel_s">
                    <div class="panel-heading text-center">
                        <h3 class="panel-title"><?= _l('track_feedback_by_customer'); ?></h3>
                    </div>
                    <div class="panel-body">
                        <!-- Filter Section -->
                        <div class="form-section">
                            <h5 class="form-section-title"><?= _l('filter_feedback'); ?></h5>
                            <?php echo form_open(admin_url('ticket_feedback/ticket_feedback_controller/feedback_by_customer')); ?>

                                <div class="form-group">
                                    <label for="customer_id"><?= _l('select_customer'); ?></label>
                                    <select name="customer_id" id="customer_id" class="form-control selectpicker" data-live-search="true">
                                        <option value=""><?= _l('select_a_customer'); ?></option>
                                        <?php foreach ($customers as $customer) { ?>
                                            <option value="<?= $customer['userid']; ?>" 
                                                <?= isset($selected_customer) && $selected_customer == $customer['userid'] ? 'selected' : ''; ?>>
                                                <?= $customer['company']; ?> (<?= $customer['userid']; ?>)
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fa fa-filter"></i> <?= _l('filter_feedback'); ?>
                                    </button>
                                </div>

                            </form>
                        </div>

                        <!-- Customer Feedback Table -->
                        <h5 class="mtop20 form-section-title"><?= _l('customer_feedback'); ?></h5>
                        <table class="table dt-table">
                            <thead>
                                <tr>
                                    <th><?= _l('ticket_id'); ?></th>
                                    <th><?= _l('subject'); ?></th>
                                    <th><?= _l('rating'); ?></th>
                                    <th><?= _l('resolved'); ?></th>
                                    <th><?= _l('response_time'); ?></th>
                                    <th><?= _l('comments'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($feedbacks as $feedback) { ?>
                                    <tr>
                                        <td>#<?= $feedback['ticket_id']; ?></td>
                                        <td><?= $feedback['subject']; ?></td>
                                        <td><?= $feedback['rating']; ?> ⭐</td>
                                        <td><?= $feedback['resolved'] ? '<span class="badge badge-success">' . _l('yes') . '</span>' : '<span class="badge badge-danger">' . _l('no') . '</span>'; ?></td>
                                        <td><?= ($feedback['response_time'] < 1) ? 'Less than an hour' : $feedback['response_time'] . ' hours'; ?></td>

                                        <td><?= $feedback['comments']; ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>

</body>
</html>

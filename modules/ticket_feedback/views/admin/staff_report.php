<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel_s">
                    <div class="panel-heading text-center">
                        <h3 class="panel-title"><?= _l('Staff Performance Report'); ?></h3>
                    </div>
                    <div class="panel-body">
                        <table class="table dt-table">
                            <thead>
                                <tr>
                                    <th><?= _l('Staff Name'); ?></th>
                                    <th><?= _l('Total Feedbacks'); ?></th>
                                    <th><?= _l('Average Rating'); ?></th>
                                    <th><?= _l('Resolved Tickets'); ?></th>
                                    <th><?= _l('Performance'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($staff_feedback as $feedback) { ?>
                                    <tr>
                                        <td><?= $feedback['staff_name']; ?></td>
                                        <td><?= $feedback['total_feedbacks']; ?></td>
                                        <td>
                                            <strong><?= number_format($feedback['avg_rating'], 1); ?> ⭐</strong>
                                        </td>
                                        <td><?= $feedback['resolved_tickets']; ?></td>
                                        <td>
                                            <?php
                                            if ($feedback['avg_rating'] >= 4.5) {
                                                echo '<span class="badge badge-success">Excellent</span>';
                                            } elseif ($feedback['avg_rating'] >= 3) {
                                                echo '<span class="badge badge-warning">Good</span>';
                                            } else {
                                                echo '<span class="badge badge-danger">Needs Improvement</span>';
                                            }
                                            ?>
                                        </td>
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

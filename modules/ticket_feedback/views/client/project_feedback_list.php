<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<style>
/* Standardized button styles */
.btn-primary,
.btn-success {
    font-weight: bold;
    border-radius: 5px;
    padding: 10px 20px;
    border: 2px solid white;
}

/* Primary button (Give Feedback) */
.btn-primary {
    background-color: #28a745 !important;  /* Green */
    color: #ffffff !important;
}

.btn-primary:hover {
    background-color: #218838 !important; /* Darker Green */
}

/* Success button (Feedback Submitted) */
.btn-success {
    background-color: #17a2b8 !important; /* Teal */
    color: #ffffff !important;
}

.btn-success:hover {
    background-color: #138496 !important; /* Darker Teal */
}

/* Table Styling */
.badge {
    font-size: 14px;
    padding: 6px 12px;
}
</style>

<div id="wrapper">
    <div class="content">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="panel_s">
                    <div class="panel-heading">
                        <h3 class="panel-title text-center"><?= _l('My Project Feedback'); ?></h3>
                    </div>
                    <div class="panel-body">
                        <table class="table dt-table table-hover">
                            <thead>
                                <tr>
                                    <th><?= _l('Project Name'); ?></th>
                                    <th><?= _l('Status'); ?></th>
                                    <th><?= _l('Feedback'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($projects as $project): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($project['name']); ?></td>
                                        <td>
                                            <?php
                                            // ✅ Project Status Mapping with better labels
                                            $status_labels = [
                                                1 => '<span class="badge badge-warning">Not Started</span>',
                                                2 => '<span class="badge badge-primary">In Progress</span>',
                                                3 => '<span class="badge badge-info">On Hold</span>',
                                                4 => '<span class="badge badge-success">Finished</span>',
                                                5 => '<span class="badge badge-danger">Cancelled</span>',
                                            ];
                                            echo isset($status_labels[$project['status']]) ? $status_labels[$project['status']] : '<span class="badge badge-secondary">Unknown</span>';
                                            ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if (isset($project['feedback_given']) && $project['feedback_given'] == 0) { ?>
                                                <a href="<?= site_url('ticket_feedback/Client_project_feedback/submit_feedback_form/' . $project['id']); ?>"
                                                    class="btn btn-primary btn-sm">
                                                    <i class="fa fa-comment"></i> <?= _l('Give Feedback'); ?>
                                                </a>
                                            <?php } else { ?>
                                                <button class="btn btn-success btn-sm" disabled>
                                                    <i class="fa fa-check"></i> <?= _l('Feedback Submitted'); ?>
                                                </button>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <?php if (empty($projects)) { ?>
                            <p class="text-center mt-3 text-muted"><?= _l('No projects available for feedback.'); ?></p>
                        <?php } ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>

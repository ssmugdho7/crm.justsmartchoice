<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="container mt-5">
    <h2 class="text-center">Feedback Options</h2>
    <div class="row text-center">
        <div class="col-md-6">
            <a href="<?= site_url('ticket_feedback/client_ticket_feedback/feedback_tickets'); ?>" class="btn btn-primary btn-lg">
                <i class="fa fa-life-ring"></i> Support Feedback
            </a>
        </div>
        <div class="col-md-6">
            <a href="<?= site_url('ticket_feedback/Client_project_feedback/list_projects'); ?>" class="btn btn-success btn-lg">
                <i class="fa fa-tasks"></i> Project Feedback
            </a>
        </div>
    </div>
</div>

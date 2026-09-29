<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>
    /* Social Share Buttons */
    .share-buttons {
        display: flex;
        gap: 10px;
    }

    .share-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 8px 12px;
        font-size: 14px;
        font-weight: bold;
        color: white;
        border-radius: 5px;
        text-decoration: none;
        transition: 0.3s;
    }

    .share-button i {
        margin-right: 5px;
    }

    /* Individual Social Button Styles */
    .facebook {
        background: #3b5998;
    }

    .facebook:hover {
        background: #2d4373;
    }

    .twitter {
        background: #1DA1F2;
    }

    .twitter:hover {
        background: #0d8ae6;
    }

    .linkedin {
        background: #0077b5;
    }

    .linkedin:hover {
        background: #005582;
    }

    .whatsapp {
        background: #25D366;
    }

    .whatsapp:hover {
        background: #1da851;
    }

    /* Feedback Request Section */
    .feedback-request {
        margin-bottom: 20px;
    }
</style>




<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel_s">
                    <div class="panel-heading">
                        <h3 class="panel-title"><?= _l('Project Feedback'); ?></h3>
                    </div>
                    <div class="panel-body">

                        <div class="feedback-request">
                            <form
                                action="<?= admin_url('ticket_feedback/project_feedback/send_project_feedback_email'); ?>"
                                method="POST">
                                <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>"
                                    value="<?= $this->security->get_csrf_hash(); ?>">


                                <?php if (is_admin()) { ?>
                                    <div class="form-group">
                                        <label for="customer_id">Select Customer:</label>
                                        <select name="customer_id" id="customer_id" class="form-control selectpicker"
                                            data-live-search="true">
                                            <option value="">Select a Customer</option>
                                            <?php foreach ($customers as $customer) { ?>
                                                <option value="<?= $customer['id']; ?>"><?= $customer['company']; ?> (ID:
                                                    <?= $customer['id']; ?>)</option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                <?php } ?>

                                <div class="form-group">
                                    <label for="project_id">Select Completed Project:</label>
                                    <select name="project_id" id="project_id" class="form-control selectpicker"
                                        data-live-search="true" <?= is_admin() ? 'disabled' : '' ?>>
                                        <option value="">Select a Project</option>
                                        <?php if (!is_admin()) { ?>
                                            <?php foreach ($completed_projects as $project) { ?>
                                                <option value="<?= $project->id; ?>"><?= $project->name; ?></option>
                                            <?php } ?>
                                        <?php } ?>
                                    </select>
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-envelope"></i> <?= _l('Request Feedback'); ?>
                                </button>
                            </form>
                        </div>

                        <!-- Feedback Table -->
                        <table class="table dt-table">
                            <thead>
                                <tr>
                                    <th><?= _l('Project Name'); ?></th>
                                    <th><?= _l('Customer'); ?></th>
                                    <th><?= _l('Rating'); ?></th>
                                    <th><?= _l('Satisfaction Level'); ?></th>
                                    <th><?= _l('Communication'); ?></th>
                                    <th><?= _l('Comments'); ?></th>
                                    <th><?= _l('Share'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($feedbacks as $feedback) { ?>
                                    <tr>
                                        <td><?= htmlspecialchars($feedback['project_name']); ?></td>
                                        <td><?= htmlspecialchars($feedback['customer_name']); ?></td>
                                        <td><?= $feedback['rating']; ?> ⭐</td>
                                        <td><?= $feedback['satisfaction_level'] ? 'Satisfied' : 'Not Satisfied'; ?></td>
                                        <td><?= $feedback['communication_quality'] ? 'Good' : 'Poor'; ?></td>
                                        <td><?= htmlspecialchars($feedback['comments']); ?></td>
                                        <td>
                                            <div class="share-buttons">
                                                <?php
                                                // Prepare dynamic text for sharing
                                                $readMoreUrl = base_url('ticket_feedback/public_project_feedback/view/' . $feedback['project_id']);
                                                $shareText = "📢 Project Feedback!%0A%0A"
                                                    . "💼 Project: {$feedback['project_name']}%0A"
                                                    . "⭐ Rating: {$feedback['rating']} Stars%0A"
                                                    . "💬 Comments: {$feedback['comments']}%0A"
                                                    . "👉 Read more: " . $readMoreUrl;

                                                $encodedUrl = urlencode($readMoreUrl);
                                                ?>

                                                <!-- Facebook -->
                                                <a href="https://www.facebook.com/sharer/sharer.php?u=<?= $encodedUrl; ?>"
                                                    target="_blank" class="share-button facebook">
                                                    <i class="fab fa-facebook"></i> Share
                                                </a>

                                                <!-- Twitter -->
                                                <a href="https://twitter.com/intent/tweet?text=<?= $shareText; ?>"
                                                    target="_blank" class="share-button twitter">
                                                    <i class="fab fa-twitter"></i> Tweet
                                                </a>

                                                <!-- LinkedIn -->
                                                <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?= $encodedUrl; ?>&title=<?= urlencode('Project Feedback'); ?>&summary=<?= $shareText; ?>"
                                                    target="_blank" class="share-button linkedin">
                                                    <i class="fab fa-linkedin"></i> Share
                                                </a>

                                                <!-- WhatsApp -->
                                                <a href="https://api.whatsapp.com/send?text=<?= $shareText; ?>"
                                                    target="_blank" class="share-button whatsapp">
                                                    <i class="fab fa-whatsapp"></i> Share
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>

                        <?php if (empty($feedbacks)) { ?>
                            <p class="text-center mt-3 text-muted"><?= _l('No feedback available.'); ?></p>
                        <?php } ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>
<script>
    var baseUrl = "<?= admin_url('ticket_feedback/project_feedback/get_completed_projects'); ?>";
</script>
<script src="<?= module_dir_url('ticket_feedback', 'assets/js/feedback.js'); ?>"></script>
</body>

</html>
<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script src="<?= module_dir_url('ticket_feedback', 'assets/js/sweetalert2.js'); ?>"></script>

<div id="wrapper">
    <div class="content">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="panel_s">
                    <div class="card shadow-lg border-0 rounded-lg">
                        <div class="card-header bg-primary text-white text-center">
                            <h3 class="m-0"><i class="fa fa-comment-dots"></i> <?= _l('Submit Project Feedback'); ?>
                            </h3>
                        </div>
                        <div class="panel-body">
                            <div class="card-body p-4">
                                <form
                                    action="<?= site_url('ticket_feedback/Client_project_feedback/store_feedback'); ?>"
                                    method="POST">
                                    <input type="hidden" name="project_id" value="<?= $project->id; ?>">
                                    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>"
                                        value="<?= $this->security->get_csrf_hash(); ?>">

                                    <!-- Star Rating -->
                                    <div class="form-group text-center">
                                        <label class="font-weight-bold"><?= _l('Rate your experience:'); ?></label>
                                        <div class="star-rating d-flex justify-content-center mt-2">
                                            <input type="hidden" name="rating" id="rating" value="5">
                                            <i class="fa fa-star star" data-value="1"></i>
                                            <i class="fa fa-star star" data-value="2"></i>
                                            <i class="fa fa-star star" data-value="3"></i>
                                            <i class="fa fa-star star" data-value="4"></i>
                                            <i class="fa fa-star star" data-value="5"></i>
                                        </div>
                                    </div>

                                    <!-- Satisfaction Level -->
                                    <div class="form-group">
                                        <label
                                            class="font-weight-bold"><?= _l('Overall Satisfaction Level:'); ?></label>
                                        <div class="btn-group btn-group-toggle d-flex" data-toggle="buttons">
                                            <label class="btn btn-outline-success flex-fill option-btn">
                                                <input type="radio" name="satisfaction_level" value="1" required> ✅
                                                <?= _l('Satisfied'); ?>
                                            </label>
                                            <label class="btn btn-outline-danger flex-fill option-btn">
                                                <input type="radio" name="satisfaction_level" value="0" required> ❌
                                                <?= _l('Not Satisfied'); ?>
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Communication Quality -->
                                    <div class="form-group">
                                        <label class="font-weight-bold"><?= _l('Quality of Communication:'); ?></label>
                                        <div class="btn-group btn-group-toggle d-flex" data-toggle="buttons">
                                            <label class="btn btn-outline-success flex-fill option-btn">
                                                <input type="radio" name="communication_quality" value="1" required> ✅
                                                <?= _l('Good'); ?>
                                            </label>
                                            <label class="btn btn-outline-danger flex-fill option-btn">
                                                <input type="radio" name="communication_quality" value="0" required> ❌
                                                <?= _l('Poor'); ?>
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Additional Comments -->
                                    <div class="form-group">
                                        <label class="font-weight-bold"><?= _l('Additional Comments:'); ?></label>
                                        <textarea name="comments" class="form-control rounded-lg shadow-sm" rows="4"
                                            placeholder="<?= _l('Share your thoughts...'); ?>"></textarea>
                                    </div>

                                    <!-- Submit Button -->
                                    <div class="text-center">
                                        <button type="submit" class="btn btn-success btn-lg shadow-sm">
                                            <i class="fa fa-paper-plane"></i> <?= _l('Submit Feedback'); ?>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for Star Rating & UI Enhancements -->
<script>
    document.addEventListener("DOMContentLoaded", function () {
        let stars = document.querySelectorAll(".star-rating .star");
        let ratingInput = document.getElementById("rating");

        stars.forEach(star => {
            star.addEventListener("click", function () {
                let ratingValue = this.getAttribute("data-value");
                ratingInput.value = ratingValue;

                // Reset and highlight selected stars
                stars.forEach(s => s.classList.remove("text-warning"));
                for (let i = 0; i < ratingValue; i++) {
                    stars[i].classList.add("text-warning");
                }
            });

            // Hover effect
            star.addEventListener("mouseenter", function () {
                let hoverValue = this.getAttribute("data-value");
                stars.forEach(s => s.classList.remove("text-warning"));
                for (let i = 0; i < hoverValue; i++) {
                    stars[i].classList.add("text-warning");
                }
            });

            // Reset stars when mouse leaves
            star.addEventListener("mouseleave", function () {
                let ratingValue = ratingInput.value;
                stars.forEach(s => s.classList.remove("text-warning"));
                for (let i = 0; i < ratingValue; i++) {
                    stars[i].classList.add("text-warning");
                }
            });
        });

        // UI Enhancement - Auto Select Buttons
        let optionButtons = document.querySelectorAll(".option-btn input");
        optionButtons.forEach(btn => {
            btn.addEventListener("change", function () {
                optionButtons.forEach(b => b.parentElement.classList.remove("active"));
                if (this.checked) {
                    this.parentElement.classList.add("active");
                }
            });
        });
    });
</script>

<!-- Custom Styling -->
<style>
    
    .star {
        font-size: 24px;
        color: #ddd;
        cursor: pointer;
        transition: 0.3s;
    }

    .star.text-warning {
        color: #FFD700;
    }

    .option-btn {
        transition: all 0.2s ease-in-out;
    }

    .option-btn.active {
        background-color: #28a745 !important;
        color: white !important;
    }
</style>

</body>

</html>
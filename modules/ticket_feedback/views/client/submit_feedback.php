<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script src="<?= module_dir_url('ticket_feedback', 'assets/js/sweetalert2.js'); ?>"></script>
<style>

.option-btn {
    transition: all 0.2s ease-in-out;
}

.option-btn.active {
    background-color: #28a745 !important;
    color: white !important;
}

.option-btn input[type="radio"] {
    display: none; /* Hide default radio */
}


</style>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel_s">
                    <div class="panel-heading">
                        <h3 class="panel-title"><?= _l('Submit Feedback'); ?></h3>
                    </div>
                    <div class="panel-body">



                        <div class="col-md-8 offset-md-2">
                            <div class="card shadow-lg">
                                <div class="card-header bg-primary text-white text-center">
                                    <h3><i class="fa fa-comment-dots"></i> Submit Your Ticket Feedback</h3>
                                </div>

                                <div class="card-body">
                                    <form
                                        action="<?= site_url('ticket_feedback/Client_ticket_feedback/store_feedback'); ?>"
                                        method="POST">
                                        <input type="hidden" name="ticket_id" value="<?= $ticket_id; ?>">

                                        <div class="form-group">
                                            <label class="font-weight-bold">Was your issue resolved?</label>
                                            <div class="btn-group btn-group-toggle d-flex" data-toggle="buttons">
                                                <label class="btn btn-outline-success flex-fill option-btn">
                                                    <input type="radio" name="resolved" value="1" required> ✅ Yes
                                                </label>
                                                <label class="btn btn-outline-danger flex-fill option-btn">
                                                    <input type="radio" name="resolved" value="0" required> ❌ No
                                                </label>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="font-weight-bold">Was the response time satisfactory?</label>
                                            <div class="btn-group btn-group-toggle d-flex" data-toggle="buttons">
                                                <label class="btn btn-outline-success flex-fill option-btn">
                                                    <input type="radio" name="response_time_satisfactory" value="1"
                                                        required> ✅ Yes
                                                </label>
                                                <label class="btn btn-outline-danger flex-fill option-btn">
                                                    <input type="radio" name="response_time_satisfactory" value="0"
                                                        required> ❌ No
                                                </label>
                                            </div>
                                        </div>




                                        <!-- Star Rating System -->
                                        <div class="form-group">
                                            <label class="font-weight-bold">Rate your experience:</label>
                                            <div class="star-rating">
                                                <input type="hidden" name="rating" id="rating" value="1">
                                                <i class="fa fa-star" data-value="1"></i>
                                                <i class="fa fa-star" data-value="2"></i>
                                                <i class="fa fa-star" data-value="3"></i>
                                                <i class="fa fa-star" data-value="4"></i>
                                                <i class="fa fa-star" data-value="5"></i>
                                            </div>
                                        </div>

                                        <!-- Additional Comments -->
                                        <div class="form-group">
                                            <label class="font-weight-bold">Additional Comments:</label>
                                            <textarea name="comments" class="form-control" rows="4"
                                                placeholder="Write your feedback here..."></textarea>
                                        </div>

                                        <!-- Submit Button -->
                                        <div class="text-center">
                                            <button type="submit" class="btn btn-success btn-lg">
                                                <i class="fa fa-paper-plane"></i> Submit Feedback
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
</div>
<!-- Add JavaScript for Star Rating -->
<script>
    document.addEventListener("DOMContentLoaded", function () {
        let optionButtons = document.querySelectorAll(".option-btn input");

        optionButtons.forEach(btn => {
            btn.addEventListener("change", function () {
                let parentGroup = this.closest(".btn-group");
                parentGroup.querySelectorAll("label").forEach(label => label.classList.remove("active"));
                if (this.checked) {
                    this.parentElement.classList.add("active");
                }
            });
        });
    });

    document.addEventListener("DOMContentLoaded", function () {
        let stars = document.querySelectorAll(".star-rating i");
        let ratingInput = document.getElementById("rating");

        stars.forEach(star => {
            // Click to set rating
            star.addEventListener("click", function () {
                let ratingValue = this.getAttribute("data-value");
                ratingInput.value = ratingValue;

                // Reset stars
                stars.forEach(s => s.classList.remove("text-warning"));

                // Highlight selected stars
                for (let i = 0; i < ratingValue; i++) {
                    stars[i].classList.add("text-warning");
                }
            });

            // Hover effect to preview rating
            star.addEventListener("mouseenter", function () {
                let hoverValue = this.getAttribute("data-value");

                stars.forEach(s => s.classList.remove("text-warning"));
                for (let i = 0; i < hoverValue; i++) {
                    stars[i].classList.add("text-warning");
                }
            });

            // Reset stars if not clicked
            star.addEventListener("mouseleave", function () {
                let ratingValue = ratingInput.value;
                stars.forEach(s => s.classList.remove("text-warning"));
                for (let i = 0; i < ratingValue; i++) {
                    stars[i].classList.add("text-warning");
                }
            });
        });
    });

    document.addEventListener("DOMContentLoaded", function () {
        let feedbackForm = document.querySelector("form");

        feedbackForm.addEventListener("submit", function (event) {
            event.preventDefault();

            let formData = new FormData(feedbackForm);
            formData.append(csrfData['token_name'], csrfData['hash']); // ✅ Add CSRF token

            fetch(feedbackForm.action, {
                method: "POST",
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: "success",
                            title: "Thank you!",
                            text: "Your feedback has been submitted successfully!",
                            showConfirmButton: false,
                            timer: 3000
                        });

                        document.querySelector("button[type='submit']").disabled = true;
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Oops!",
                            text: "Something went wrong. Please try again later."
                        });
                    }
                })
                .catch(error => console.error("Error:", error));
        });
    });



</script>

</body>

</html>
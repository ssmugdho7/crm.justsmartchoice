<?php defined('BASEPATH') or exit('No direct script access allowed');

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="user-scalable=no, width=device-width, initial-scale=1, maximum-scale=1">
    <title><?php echo hooks()->apply_filters('appointments_form_title', _l('appointment_create_new_appointment')); ?></title>

    <!-- Add Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>


    <link href="<?= module_dir_url('appointly', 'assets/css/appointments_external_form.css'); ?>" rel="stylesheet" type="text/css">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css" rel="stylesheet">
</head>
<div class="flex min-h-screen items-center justify-center bg-gray-100">
    <div class="w-full max-w-2xl px-4">
        <h1 class="mb-8 text-center text-3xl font-bold text-gray-900"><?= _l('appointment_how_can_we_help_you_today'); ?></h1>

        <div class="flex flex-col gap-4">
            <!-- Appointment Card -->
            <a href="<?= site_url('appointly/appointments_public/book') ?>"
                class="group relative overflow-hidden rounded-xl bg-white p-8 shadow-lg transition-all duration-300 hover:shadow-2xl">
                <div class="flex flex-col items-center">
                    <div class="mb-4 rounded-full bg-blue-50 p-4">
                        <i class="fa fa-calendar text-4xl text-blue-600"></i>
                    </div>
                    <h2 class="mb-2 text-2xl font-semibold text-gray-900"><?= _l('appointment_schedule_appointment'); ?></h2>
                    <p class="text-center text-gray-600"><?= _l('appointment_book_now_description'); ?></p>
                </div>
                <div class="mt-6 text-center">
                    <span class="inline-flex items-center text-blue-600">
                        <?= _l('appointment_book_now'); ?>
                        <i class="fa fa-arrow-right ml-2 transition-transform group-hover:translate-x-1"></i>
                    </span>
                </div>
            </a>
        </div>
    </div>
</div>
</body>

</html>

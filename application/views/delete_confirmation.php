<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!doctype html>
<html lang="<?= e($GLOBALS['locale'] ?? 'en'); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e(_l('delete')); ?></title>
    <link rel="stylesheet" href="<?= e(base_url('assets/plugins/bootstrap/css/bootstrap.min.css')); ?>">
</head>
<body>
<main class="container">
    <h1><?= e(_l('delete')); ?></h1>
    <?= form_open(e($action)); ?>
        <div class="form-group">
            <label for="delete_confirmation"><?= e($instruction); ?></label>
            <input class="form-control" id="delete_confirmation" name="delete_confirmation" autocomplete="off"
                spellcheck="false" required pattern="DELETE">
        </div>
        <button class="btn btn-danger" type="submit"><?= e(_l('delete')); ?></button>
        <a class="btn btn-default" href="<?= e($cancelUrl); ?>"><?= e(_l('cancel')); ?></a>
    <?= form_close(); ?>
</main>
</body>
</html>

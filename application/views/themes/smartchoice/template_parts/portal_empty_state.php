<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="sc-portal-empty" role="status">
    <h2><?= e($portalEmptyTitle); ?></h2>
    <p><?= e($portalEmptyText); ?></p>
    <?php if (has_contact_permission('support')) { ?>
    <a class="btn btn-default" href="<?= site_url('clients/open_ticket'); ?>">Ask the team</a>
    <?php } else { ?>
    <a class="btn btn-default" href="<?= site_url('clients/profile'); ?>">Review your account</a>
    <?php } ?>
</div>

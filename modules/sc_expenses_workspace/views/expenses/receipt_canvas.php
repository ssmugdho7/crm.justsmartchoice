<?php defined('BASEPATH') or exit('No direct script access allowed');
$receipt = scct_expenses_receipt($expense);
$url = site_url('download/file/expense/' . (int) $expense->expenseid);
?>
<?php if ($receipt['name'] !== '') { ?>
<div class="exp-receipt-canvas">
    <?php if (!$receipt['exists']) { ?>
        <p class="text-warning" role="status"><i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i> <?= e(_l('scct_expenses_receipt_missing')); ?></p>
    <?php } else { ?>
        <?php if (in_array($receipt['mime'], ['image/jpeg', 'image/png', 'image/gif', 'image/webp'], true)) { ?>
        <a href="<?= e($url . '?preview=1'); ?>" target="_blank" rel="noopener"><img src="<?= e($url . '?preview=1'); ?>" alt="<?= e(_l('expense_receipt') . ': ' . $receipt['name']); ?>" loading="lazy"></a>
        <?php } elseif ($receipt['mime'] === 'application/pdf') { ?>
        <iframe src="<?= e($url . '?preview=1'); ?>" title="<?= e(_l('expense_receipt') . ': ' . $receipt['name']); ?>" loading="lazy"></iframe>
        <?php } else { ?>
        <p><?= e(_l('scct_expenses_receipt_download_only')); ?></p>
        <?php } ?>
        <a class="btn btn-default mtop10" href="<?= e($url . '?preview=1'); ?>" target="_blank" rel="noopener"><?= e(_l('scct_expenses_open_receipt')); ?></a>
        <a class="btn btn-default mtop10" href="<?= e($url); ?>"><?= e(_l('download')); ?></a>
    <?php } ?>
</div>
<?php } ?>

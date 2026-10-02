<?php defined('BASEPATH') or exit('No direct script access allowed');
$title = (string) ($guide['article']['title'] ?? '');
$description = mb_strimwidth($guide['description'], 0, 190, '…');
?>
<a class="sc-help-guide<?php echo $isFeatured ? ' sc-help-guide-featured' : ''; ?>" href="<?php echo site_url('training_manual/customer-books/article/' . (int) $guide['article']['id']); ?>"<?php if (!$isFeatured) { ?> data-guide data-category="<?php echo $guide['category']; ?>" data-search="<?php echo html_escape($title . ' ' . $guide['description'] . ' ' . $guide['book']); ?>"<?php } ?>>
    <span class="sc-help-guide-category"><i class="fa <?php echo $guide['category'] === 'training' ? 'fa-graduation-cap' : 'fa-book'; ?>" aria-hidden="true"></i><?php echo html_escape($categories[$guide['category']]); ?></span>
    <?php if ($isFeatured) { ?><h3><?php echo html_escape($title); ?></h3><?php } else { ?><h4><?php echo html_escape($title); ?></h4><?php } ?>
    <?php if ($description !== '') { ?><p><?php echo html_escape($description); ?></p><?php } ?>
    <span class="sc-help-guide-footer"><span><?php echo $guide['category'] === 'training' ? 'Training resource' : 'Read guide'; ?></span><i class="fa fa-arrow-right" aria-hidden="true"></i></span>
</a>

<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="scie-nav-tabs">
  <?php foreach($nav as $n): ?>
    <a href="<?php echo admin_url($n[1]); ?>" class="scie-nav-tab"><i class="<?php echo html_escape($n[2]); ?>"></i> <?php echo html_escape($n[0]); ?></a>
  <?php endforeach; ?>
</div>

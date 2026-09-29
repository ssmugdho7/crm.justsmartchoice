<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<h2><?php echo _l('project_signatures_pdf_title'); ?> #<?php echo $signature->id; ?></h2>
<p><strong><?php echo _l('project'); ?>:</strong> <?php echo $signature->project_id; ?></p>
<p><strong><?php echo _l('signed_at'); ?>:</strong> <?php echo _dt($signature->signed_at); ?></p>
<p><strong><?php echo _l('notes'); ?>:</strong> <?php echo nl2br(html_escape($signature->notes)); ?></p>
<p><strong><?php echo _l('disclaimer'); ?>:</strong> <?php echo nl2br(html_escape($signature->disclaimer)); ?></p>
<p><strong><?php echo _l('project_signatures_signature'); ?>:</strong></p>
<pre><?php echo html_escape($signature->signature); ?></pre>

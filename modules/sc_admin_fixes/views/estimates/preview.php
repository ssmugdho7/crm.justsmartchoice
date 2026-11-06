<?php defined('BASEPATH') or exit('No direct script access allowed');
include APPPATH.'views/admin/estimates/estimate_preview_template.php';
if(staff_can('create','invoices')&&staff_can('edit','estimates')){?>
<div class="scaf-estimate-conversion-action"><button type="button" class="btn btn-primary scaf-estimate-convert" data-estimate="<?= (int)$estimate->id; ?>"><?= e(_l('estimate_convert_to_invoice')); ?></button><small>Choose a full invoice or review a percentage draw draft.</small></div>
<?php } ?>

<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php $this->load->view('purchasing_hub/_header'); ?>
<?php
$defaultMessage = 'Please review the attached purchasing document.';
if($type === 'orders'){
  $defaultMessage .= "\n\nPlease confirm that this purchase order was received. If there is any issue with inventory, delivery, pricing, substitutions, backorders, or schedule, contact Smart Choice Contractors USA immediately before processing this order.";
}
?>
<div class="panel_s"><div class="panel-body">
  <?php echo form_open(admin_url('purchasing_hub/send_email/'.$type.'/'.$row['id'])); ?>
    <?php echo render_input('email', 'Recipient Email', $row['sent_to'] ?? '', 'email'); ?>
    <?php echo render_input('subject', 'Subject', ($title ?? 'Purchasing Document') . ' #' . $row['id']); ?>
    <?php echo render_textarea('message', 'Email Message', $defaultMessage, ['class'=>'tinymce','rows'=>12]); ?>
    <?php if($type === 'orders'){ ?><div class="checkbox checkbox-primary"><input type="checkbox" name="receipt_requested" id="receipt_requested_email" value="1" checked><label for="receipt_requested_email">Request vendor email receipt confirmation</label></div><?php } ?>
    <button type="submit" class="btn btn-primary btn-sm">Send Email</button>
    <a href="<?php echo admin_url('purchasing_hub/view/'.$type.'/'.$row['id']); ?>" class="btn btn-default btn-sm">Cancel</a>
  <?php echo form_close(); ?>
</div></div>
<?php $this->load->view('purchasing_hub/_footer'); ?>

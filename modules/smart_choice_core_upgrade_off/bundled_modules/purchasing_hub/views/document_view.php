<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php $this->load->view('purchasing_hub/_header'); ?>
<div class="panel_s"><div class="panel-body">
  <div class="m-b-md">
    <a href="<?php echo admin_url('purchasing_hub/simple/'.$type.'/'.$row['id']); ?>" class="btn btn-default btn-sm">Edit</a>
    <a href="<?php echo admin_url('purchasing_hub/copy/'.$type.'/'.$row['id']); ?>" class="btn btn-warning btn-sm">Copy</a>
    <a href="<?php echo admin_url('purchasing_hub/pdf/'.$type.'/'.$row['id']); ?>" target="_blank" class="btn btn-info btn-sm">View PDF</a>
    <a href="<?php echo admin_url('purchasing_hub/client_view/'.$type.'/'.$row['id']); ?>" target="_blank" class="btn btn-success btn-sm">View As Client</a>
    <a href="<?php echo admin_url('purchasing_hub/send_email/'.$type.'/'.$row['id']); ?>" class="btn btn-primary btn-sm">Send Email</a>
  </div>
  <?php $this->load->view('purchasing_hub/document_pdf', ['type'=>$type,'row'=>$row,'title'=>$title]); ?>
</div></div>
<div class="panel_s"><div class="panel-body">
  <h4>Attachments</h4>
  <?php echo form_open_multipart(admin_url('purchasing_hub/attachment/'.$type.'/'.$row['id'])); ?>
    <input type="file" name="attachment" class="form-control" />
    <br><button class="btn btn-primary btn-sm" type="submit">Upload Attachment</button>
  <?php echo form_close(); ?>
  <hr>
  <?php if(empty($attachments)){ echo '<p>No attachments uploaded.</p>'; } ?>
  <?php foreach($attachments as $attachment){ ?><p><a target="_blank" href="<?php echo $attachment['url']; ?>"><?php echo html_escape($attachment['file_name']); ?></a></p><?php } ?>
</div></div>
<?php $this->load->view('purchasing_hub/_footer'); ?>

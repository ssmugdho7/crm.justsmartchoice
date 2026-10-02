<?php defined('BASEPATH') or exit('No direct script access allowed');

function sc_sales_discussions_get($type,$id)
{
    $CI=&get_instance(); $table=db_prefix().'sc_sales_discussions';
    if(!$CI->db->table_exists($table)) return [];
    return $CI->db->where(['rel_type'=>$type,'rel_id'=>(int)$id])->order_by('id','asc')->get($table)->result_array();
}

function sc_sales_document_summary_values($type,$document)
{
    $values=['total'=>isset($document->total)?(float)$document->total:0.0,'contract_total'=>0.0,'due_now'=>0.0,'remaining_balance'=>0.0];
    if(function_exists('scps_get_meta') && !empty($document->id)){
        $meta=scps_get_meta($type,(int)$document->id);
        if($meta){$values['contract_total']=(float)$meta->contract_total;$values['due_now']=(float)$meta->due_now;$values['remaining_balance']=(float)$meta->remaining_balance;}
    }
    if($values['contract_total']<=0)$values['contract_total']=$values['total'];
    if($type==='invoice' && isset($document->total_left_to_pay) && $values['due_now']<=0)$values['due_now']=(float)$document->total_left_to_pay;
    elseif($values['due_now']<=0)$values['due_now']=$values['total'];
    return $values;
}

function sc_sales_public_sidebar($type,$document,$hash)
{
    if(!$document||empty($document->id))return '';
    $CI=&get_instance(); $comments=sc_sales_discussions_get($type,$document->id); $summary=sc_sales_document_summary_values($type,$document);
    $currency=isset($document->currency_name)?$document->currency_name:get_base_currency();
    $active=$CI->input->get('tab')==='discussion'?'discussion':'summary';
    $status = !empty($document->sc_custom_status_value) ? html_escape((string)$document->sc_custom_status_value) : ($type==='invoice'?format_invoice_status($document->status,'',false):format_estimate_status($document->status,'',false));
    $dateLabel=$type==='invoice'?_l('invoice_data_date'):_l('estimate_data_date'); $dateValue=_d($document->date);
    ob_start(); ?>
    <div class="inner mtop20 sc-sales-summary-discussion proposal-html-tabs">
      <ul class="nav nav-tabs nav-tabs-flat mbot15" role="tablist">
        <li class="<?= $active==='summary'?'active':''; ?>"><a href="#sc_summary" role="tab" data-toggle="tab"><i class="fa-regular fa-file-lines"></i> <?= _l('summary'); ?></a></li>
        <li class="<?= $active==='discussion'?'active':''; ?>"><a href="#discussion" role="tab" data-toggle="tab"><i class="fa-regular fa-comment"></i> <?= _l('discussion'); ?></a></li>
      </ul>
      <div class="tab-content">
        <div class="tab-pane<?= $active==='summary'?' active':''; ?>" id="sc_summary">
          <div class="panel_s"><div class="panel-body">
            <p class="tw-font-semibold tw-text-neutral-700"><?= _l('sc_document_summary'); ?></p>
            <div class="row mbot10"><div class="col-xs-5 text-muted"><?= _l('status'); ?></div><div class="col-xs-7 text-right"><?= $status; ?></div></div>
            <div class="row mbot10"><div class="col-xs-5 text-muted"><?= $dateLabel; ?></div><div class="col-xs-7 text-right"><?= html_escape($dateValue); ?></div></div><hr>
            <div class="row mbot10"><div class="col-xs-6 text-muted"><?= _l('sc_contract_total'); ?></div><div class="col-xs-6 text-right bold"><?= html_escape(app_format_money($summary['contract_total'],$currency)); ?></div></div>
            <div class="row mbot10"><div class="col-xs-6 text-muted"><?= _l('sc_amount_due_now'); ?></div><div class="col-xs-6 text-right bold text-success"><?= html_escape(app_format_money($summary['due_now'],$currency)); ?></div></div>
            <div class="row"><div class="col-xs-6 text-muted"><?= _l('sc_remaining_balance'); ?></div><div class="col-xs-6 text-right bold"><?= html_escape(app_format_money($summary['remaining_balance'],$currency)); ?></div></div>
          </div></div>
        </div>
        <div class="tab-pane<?= $active==='discussion'?' active':''; ?>" id="discussion">
          <?php foreach($comments as $comment){ ?><div class="panel panel-default"><div class="panel-body"><strong><?= html_escape($comment['author_name']); ?></strong><span class="text-muted pull-right"><?= html_escape(_dt($comment['created_at'])); ?></span><div class="clearfix"></div><div class="mtop10"><?= nl2br(html_escape($comment['message'])); ?></div></div></div><?php } ?>
          <?= form_open(site_url('smart_choice_sales_discussions/add')); ?>
          <input type="hidden" name="rel_type" value="<?= html_escape($type); ?>"><input type="hidden" name="rel_id" value="<?= (int)$document->id; ?>"><input type="hidden" name="hash" value="<?= html_escape($hash); ?>">
          <?= render_input('author_name','sc_discussion_name','', 'text',['required'=>true]); ?>
          <?= render_input('author_email','sc_discussion_email','', 'email',['required'=>true]); ?>
          <?= render_textarea('message','sc_discussion_comment','',['required'=>true,'rows'=>4]); ?>
          <button type="submit" class="btn btn-primary"><i class="fa fa-paper-plane"></i> <?= _l('sc_add_comment'); ?></button><?= form_close(); ?>
        </div>
      </div>
    </div>
    <?php return ob_get_clean();
}

function sc_sales_admin_summary_discussion($type,$document)
{
    if(!$document||empty($document->id))return '';
    $comments=sc_sales_discussions_get($type,$document->id); $summary=sc_sales_document_summary_values($type,$document);
    $currency=isset($document->currency_name)?$document->currency_name:get_base_currency(); ob_start(); ?>
    <div class="row"><div class="col-md-4"><div class="panel_s"><div class="panel-body"><h4 class="tw-font-semibold tw-mt-0"><?= _l('summary'); ?></h4>
      <p><span class="text-muted"><?= _l('sc_contract_total'); ?>:</span><strong class="pull-right"><?= html_escape(app_format_money($summary['contract_total'],$currency)); ?></strong></p><div class="clearfix"></div>
      <p><span class="text-muted"><?= _l('sc_amount_due_now'); ?>:</span><strong class="pull-right text-success"><?= html_escape(app_format_money($summary['due_now'],$currency)); ?></strong></p><div class="clearfix"></div>
      <p><span class="text-muted"><?= _l('sc_remaining_balance'); ?>:</span><strong class="pull-right"><?= html_escape(app_format_money($summary['remaining_balance'],$currency)); ?></strong></p><div class="clearfix"></div>
    </div></div></div>
    <div class="col-md-8"><div class="panel_s"><div class="panel-body"><h4 class="tw-font-semibold tw-mt-0"><?= _l('discussion'); ?></h4>
      <?php foreach($comments as $comment){ ?><div class="panel panel-default mbot10"><div class="panel-body"><strong><?= html_escape($comment['author_name']); ?></strong><span class="text-muted pull-right"><?= html_escape(_dt($comment['created_at'])); ?></span><div class="clearfix"></div><div class="mtop10"><?= nl2br(html_escape($comment['message'])); ?></div></div></div><?php } ?>
      <?= form_open(site_url('smart_choice_sales_discussions/admin_add')); ?><input type="hidden" name="rel_type" value="<?= html_escape($type); ?>"><input type="hidden" name="rel_id" value="<?= (int)$document->id; ?>">
      <?= render_textarea('message','sc_discussion_comment','',['required'=>true,'rows'=>4]); ?><button type="submit" class="btn btn-primary"><i class="fa fa-paper-plane"></i> <?= _l('sc_add_comment'); ?></button><?= form_close(); ?>
    </div></div></div></div>
    <?php return ob_get_clean();
}

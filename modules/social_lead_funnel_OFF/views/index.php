<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content"><div class="row"><div class="col-md-12">
  <div class="slf-hero">
    <div><h3><i class="fa fa-bullhorn"></i> <?php echo _l('social_lead_funnel'); ?></h3><p><?php echo _l('social_lead_funnel_subtitle'); ?></p></div>
    <a href="<?php echo admin_url('social_lead_funnel/create'); ?>" class="btn slf-btn-orange btn-sm"><i class="fa fa-plus"></i> <?php echo _l('social_lead_funnel_new_opportunity'); ?></a>
  </div>
  <?php $this->load->view('social_lead_funnel/_nav'); ?>
  <div class="panel_s"><div class="panel-body">
    <form method="get" class="slf-filter-row">
      <input type="text" name="search" class="form-control input-sm" placeholder="<?php echo _l('search'); ?>" value="<?php echo html_escape($filters['search'] ?? ''); ?>">
      <select name="source" class="form-control input-sm"><option value=""><?php echo _l('social_lead_funnel_all_sources'); ?></option><?php foreach(['manual','facebook','nextdoor','messenger','lead_ad','comment'] as $s){ ?><option value="<?php echo $s; ?>" <?php echo (($filters['source'] ?? '')===$s?'selected':''); ?>><?php echo ucfirst(str_replace('_',' ',$s)); ?></option><?php } ?></select>
      <select name="status" class="form-control input-sm"><option value=""><?php echo _l('social_lead_funnel_all_statuses'); ?></option><?php foreach(['new','reviewing','converted','ignored'] as $s){ ?><option value="<?php echo $s; ?>" <?php echo (($filters['status'] ?? '')===$s?'selected':''); ?>><?php echo ucfirst($s); ?></option><?php } ?></select>
      <button class="btn btn-default btn-sm"><i class="fa fa-search"></i> <?php echo _l('search'); ?></button>
    </form>
    <div class="table-responsive">
      <table class="table dt-table slf-table">
        <thead><tr><th><?php echo _l('source'); ?></th><th><?php echo _l('title'); ?></th><th><?php echo _l('name'); ?></th><th><?php echo _l('social_lead_funnel_service'); ?></th><th><?php echo _l('social_lead_funnel_score'); ?></th><th><?php echo _l('status'); ?></th><th><?php echo _l('options'); ?></th></tr></thead>
        <tbody><?php foreach($opportunities as $row){ ?><tr>
          <td><span class="slf-pill"><?php echo html_escape(ucfirst($row['source'])); ?></span></td>
          <td><?php echo html_escape($row['title']); ?><br><small><?php echo html_escape(mb_strimwidth((string)$row['message'],0,90,'...')); ?></small></td>
          <td><?php echo html_escape($row['author_name']); ?></td>
          <td><?php echo html_escape($row['service_type']); ?></td>
          <td><strong class="slf-score"><?php echo (int)$row['ai_score']; ?>%</strong></td>
          <td><?php echo html_escape(ucfirst($row['status'])); ?></td>
          <td><a class="btn btn-default btn-xs" href="<?php echo admin_url('social_lead_funnel/view/'.$row['id']); ?>"><i class="fa fa-eye"></i> <?php echo _l('view'); ?></a></td>
        </tr><?php } ?></tbody>
      </table>
    </div>
  </div></div>
</div></div></div></div>
<?php init_tail(); ?>

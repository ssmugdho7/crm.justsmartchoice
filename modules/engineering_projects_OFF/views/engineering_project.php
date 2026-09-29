<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php
$ep = $engineering_project ?? null;
$selectedProject = $ep->project_id ?? '';
$selectedCustomer = $ep->customer_id ?? '';
?>
<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-10 col-md-offset-1">
        <div class="panel_s eng-card">
          <div class="panel-body">
            <h4 class="no-margin"><i class="fa fa-building-o"></i> <?php echo $title; ?></h4>
            <p class="text-muted mtop10">Link engineering drawings, permit documents, and existing CRM project files in one place. Files uploaded here are stored under uploads/Projects/Customer/Project.</p>
            <hr class="hr-panel-heading" />
            <?php echo form_open($this->uri->uri_string()); ?>
            <div class="row">
              <div class="col-md-6"><?php echo render_select('project_id', $projects ?? [], ['id','name'], _l('crm_project'), $selectedProject, ['data-live-search'=>true]); ?></div>
              <div class="col-md-6"><?php echo render_select('customer_id', $customers ?? [], ['userid','company'], _l('customer'), $selectedCustomer, ['data-live-search'=>true]); ?></div>
            </div>
            <?php echo render_input('name', _l('engineering_job_name'), $ep->name ?? ''); ?>
            <div class="row">
              <div class="col-md-6"><?php echo render_date_input('start_date', _l('project_start_date'), isset($ep->start_date) ? _d($ep->start_date) : ''); ?></div>
              <div class="col-md-6"><?php echo render_date_input('end_date', _l('project_end_date'), isset($ep->end_date) ? _d($ep->end_date) : ''); ?></div>
            </div>
            <div class="row">
              <div class="col-md-4"><?php echo render_select('site_survey_schedule', get_engeniering_status(), ['id','name'], 'engineering_project_site_survey_schedule', $ep->site_survey_schedule ?? ''); echo render_date_input('site_survey_schedule_date', 'engg_project_date', isset($ep->site_survey_schedule_date) ? _d($ep->site_survey_schedule_date) : ''); ?></div>
              <div class="col-md-4"><?php echo render_select('install_schedule', get_engeniering_status(), ['id','name'], 'engg_install_schedule', $ep->install_schedule ?? ''); echo render_date_input('install_schedule_date', 'engg_project_date', isset($ep->install_schedule_date) ? _d($ep->install_schedule_date) : ''); ?></div>
              <div class="col-md-4"><?php echo render_select('final_inspection', get_engeniering_status(), ['id','name'], 'engg_final_inspection', $ep->final_inspection ?? ''); echo render_date_input('final_inspection_date', 'engg_project_date', isset($ep->final_inspection_date) ? _d($ep->final_inspection_date) : ''); ?></div>
            </div>
            <?php echo render_select('contractor_id', $contractors ?? [], ['id','contractor'], 'engg_contractor_select', $ep->contractor_id ?? '', ['data-live-search'=>true]); ?>
            <?php $drawings_ids = isset($ep->drawings_ids) && $ep->drawings_ids !== '' ? explode(',', $ep->drawings_ids) : []; echo render_multi_select('drawings_ids[]','drawings_ids',$drawings ?? [], ['id','name'], 'engg_drawings', $drawings_ids, ['multiple'=>'multiple']); ?>
            <?php $document_ids = isset($ep->document_ids) && $ep->document_ids !== '' ? explode(',', $ep->document_ids) : []; echo render_multi_select('document_ids[]','document_ids', $documents ?? [], ['id','name'], 'engg_project_documents', $document_ids, ['multiple'=>'multiple']); ?>
            <?php $shared_ids = isset($ep->shared_file_ids) && $ep->shared_file_ids !== '' ? explode(',', $ep->shared_file_ids) : []; echo render_multi_select('shared_file_ids[]','shared_file_ids', $project_files ?? [], ['id','file_name'], _l('shared_crm_files'), $shared_ids, ['multiple'=>'multiple','data-live-search'=>true]); ?>
            <?php echo render_select('tpo_status', get_engeniering_tpo_status(), ['id','name'], 'engg_tpo_status', $ep->tpo_status ?? ''); ?>
            <?php
              $__extra_meta = [];
              if ($ep && !empty($ep->extra_meta)) { $__extra_meta = json_decode($ep->extra_meta, true) ?: []; }
              if (!empty($option_fields) && is_array($option_fields)) {
                echo '<hr><h4>'._l('engineering_custom_tracking').'</h4>';
                foreach ($option_fields as $of_key) { echo render_input('options['.$of_key.']', _l($of_key), $__extra_meta[$of_key] ?? ''); }
              }
            ?>
            <div class="text-right mtop20"><a href="<?php echo admin_url('engineering_projects'); ?>" class="btn btn-default"><?php echo _l('close'); ?></a> <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button></div>
            <?php echo form_close(); ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php init_tail(); ?>
<style>.eng-card{border-radius:12px;box-shadow:0 8px 24px rgba(0,0,0,.08)}.eng-card .form-control{border-radius:8px}.eng-card .btn{border-radius:8px}</style>
<script>$(function(){ appValidateForm($('form'), {project_id:'required',customer_id:'required',contractor_id:'required'}); });</script>
</body></html>

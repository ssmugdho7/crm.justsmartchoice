<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$project_id = isset($project->id) ? (int)$project->id : (isset($project_id) ? (int)$project_id : 0);
$CI =& get_instance();
$CI->load->model('engineering_projects/engineering_projects_model');
$CI->load->model('engineering_projects/drawings_model');
$CI->load->model('engineering_projects/documents_model');
$engineering = [];
if ($CI->db->table_exists(db_prefix().'eng_engineering_projects')) {
    $CI->db->where('project_id', $project_id);
    $engineering = $CI->db->get(db_prefix().'eng_engineering_projects')->result_array();
}
?>
<div class="panel_s engineering-hub-project-tab">
  <div class="panel-body">
    <h4><i class="fa fa-cubes"></i> <?php echo _l('engineering_hub'); ?></h4>
    <p class="text-muted"><?php echo _l('engineering_hub_description'); ?></p>
    <a class="btn btn-info" href="<?php echo admin_url('engineering_projects/engineering_project'); ?>?project_id=<?php echo $project_id; ?>"><i class="fa fa-plus"></i> <?php echo _l('new_engineering_project'); ?></a>
    <hr>
    <?php if (empty($engineering)) { ?>
      <p class="text-muted"><?php echo _l('no_engineering_records'); ?></p>
    <?php } else { ?>
      <div class="table-responsive"><table class="table dt-table"><thead><tr><th><?php echo _l('engineering_project'); ?></th><th><?php echo _l('customer'); ?></th><th><?php echo _l('engg_install_schedule'); ?></th><th><?php echo _l('engg_final_inspection'); ?></th></tr></thead><tbody>
      <?php foreach ($engineering as $row) { ?>
        <tr><td><a href="<?php echo admin_url('engineering_projects/engineering_project/'.$row['engg_proj_id']); ?>"><?php echo html_escape($row['name']); ?></a></td><td><?php echo (int)$row['customer_id']; ?></td><td><?php echo html_escape($row['install_schedule']); ?></td><td><?php echo html_escape($row['final_inspection']); ?></td></tr>
      <?php } ?>
      </tbody></table></div>
    <?php } ?>
  </div>
</div>
<style>.engineering-hub-project-tab .panel-body{border-radius:14px}.engineering-hub-project-tab h4{font-weight:700}</style>

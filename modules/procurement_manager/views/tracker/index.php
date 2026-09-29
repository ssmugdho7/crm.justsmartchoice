<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-12">

        <div class="panel_s">
          <div class="panel-body">
            <h4 class="no-margin"><?php echo _l('procurement_manager'); ?></h4>
            <hr />

            <form method="get" action="<?php echo admin_url('procurement_manager'); ?>" class="form-inline mbot15">
              <div class="form-group">
                <label for="project_id" class="control-label"><?php echo _l('project'); ?></label>
                <select name="project_id" id="project_id" class="selectpicker" data-width="300px" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                  <option value=""></option>
                  <?php foreach ($projects as $project) { ?>
                    <option value="<?php echo $project['id']; ?>" <?php echo ($project_id == $project['id'] ? 'selected' : ''); ?>>
                      <?php echo $project['id'] . ' - ' . $project['name']; ?>
                    </option>
                  <?php } ?>
                </select>
              </div>
              <button type="submit" class="btn btn-info"><?php echo _l('filter'); ?></button>
              <a href="<?php echo admin_url('procurement_manager'); ?>" class="btn btn-default"><?php echo _l('clear'); ?></a>
            </form>

            <div class="row mbot20">
              <div class="col-md-6">
                <a href="<?php echo admin_url('procurement_manager/item'); ?>" class="btn btn-success">
                  <i class="fa fa-plus"></i> <?php echo _l('add_new', _l('procurement_item')); ?>
                </a>
                <a href="<?php echo admin_url('procurement_manager/export_csv?project_id=' . $project_id); ?>" class="btn btn-default">
                  <i class="fa fa-file-excel-o"></i> <?php echo _l('export'); ?> CSV
                </a>
              </div>
              <div class="col-md-6 text-right">
                <form method="post" enctype="multipart/form-data" action="<?php echo admin_url('procurement_manager/import_csv'); ?>" class="form-inline">
                  <div class="form-group">
                    <input type="file" name="csv_file" class="form-control" required>
                  </div>
                  <button type="submit" class="btn btn-default">
                    <i class="fa fa-upload"></i> <?php echo _l('import'); ?> CSV
                  </button>
                </form>
              </div>
            </div>

            <div class="row mbot20">
              <div class="col-md-6">
                <h4><?php echo _l('summary_by_supplier'); ?></h4>
                <canvas id="supplierChart"></canvas>
              </div>
              <div class="col-md-6">
                <h4><?php echo _l('summary_by_category'); ?></h4>
                <canvas id="categoryChart"></canvas>
              </div>
            </div>

            <div class="table-responsive">
              <table class="table table-striped">
                <thead>
                  <tr>
                    <th><?php echo _l('project'); ?></th>
                    <th><?php echo _l('category'); ?></th>
                    <th><?php echo _l('item'); ?></th>
                    <th><?php echo _l('qty'); ?></th>
                    <th><?php echo _l('quoted_price'); ?></th>
                    <th><?php echo _l('supplier'); ?></th>
                    <th><?php echo _l('status'); ?></th>
                    <th><?php echo _l('options'); ?></th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($items as $item) { ?>
                    <tr>
                      <td><?php echo $item['project_id']; ?></td>
                      <td><?php echo html_escape($item['category']); ?></td>
                      <td><?php echo html_escape($item['item_name']); ?></td>
                      <td><?php echo html_escape($item['qty']); ?></td>
                      <td><?php echo app_format_money($item['quoted_price'], get_base_currency()); ?></td>
                      <td><?php echo html_escape($item['supplier_name']); ?><br/><small><?php echo html_escape($item['supplier_email']); ?></small></td>
                      <td><?php echo html_escape($item['status']); ?></td>
                      <td>
                        <a href="<?php echo admin_url('procurement_manager/item/' . $item['id']); ?>" class="btn btn-default btn-icon"><i class="fa fa-pencil-square-o"></i></a>
                        <a href="<?php echo admin_url('procurement_manager/send_request/' . $item['id']); ?>" class="btn btn-info btn-icon" title="Request Quote"><i class="fa fa-envelope"></i></a>
                        <a href="<?php echo admin_url('procurement_manager/delete/' . $item['id']); ?>" class="btn btn-danger btn-icon _delete"><i class="fa fa-remove"></i></a>
                      </td>
                    </tr>
                  <?php } ?>
                </tbody>
              </table>
            </div>

          </div>
        </div>

      </div>
    </div>
  </div>
</div>

<?php
// Prepare data for charts
$suppliers = [];
$supplier_totals = [];
foreach ($summary_by_supplier as $row) {
    $suppliers[] = $row['supplier_name'] ?: 'N/A';
    $supplier_totals[] = (float)$row['total_quoted'];
}

$categories = [];
$category_totals = [];
foreach ($summary_by_category as $row) {
    $categories[] = $row['category'] ?: 'N/A';
    $category_totals[] = (float)$row['total_quoted'];
}
?>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    if (typeof Chart !== 'undefined') {
      var supplierCtx = document.getElementById('supplierChart').getContext('2d');
      new Chart(supplierCtx, {
        type: 'bar',
        data: {
          labels: <?php echo json_encode($suppliers); ?>,
          datasets: [{
            label: '<?php echo _l('total_by_supplier'); ?>',
            data: <?php echo json_encode($supplier_totals); ?>,
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
        }
      });

      var categoryCtx = document.getElementById('categoryChart').getContext('2d');
      new Chart(categoryCtx, {
        type: 'bar',
        data: {
          labels: <?php echo json_encode($categories); ?>,
          datasets: [{
            label: '<?php echo _l('total_by_category'); ?>',
            data: <?php echo json_encode($category_totals); ?>,
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
        }
      });
    }
  });
</script>

<?php init_tail(); ?>

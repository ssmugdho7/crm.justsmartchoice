<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
	<div class="content">
		<div id="vueApp">
			<div class="row">
				<div class="col-md-12 tw-mb-3 md:tw-mb-6">
					<div class="md:tw-flex md:tw-items-center">
						<div class="tw-grow">
							<h4 class="tw-my-0 tw-font-bold tw-text-xl">
								<?= _l('invoices'); ?>
							</h4>
							<?php if (! isset($project)) { ?>
							<a href="<?= admin_url('invoices/recurring'); ?>"
								class="tw-mr-4">
								<?= _l('invoices_list_recurring'); ?>
								&rarr;
							</a>
							<?php } ?>
						</div>

						<div id="invoices_total" data-type="badge"
							class="tw-self-start tw-mt-2 md:tw-mt-0 empty:tw-min-h-[60px]"></div>
					</div>

				</div>
				<div class="col-md-12">
					<?php $this->load->view('admin/invoices/quick_stats'); ?>
				</div>
				<?php include_once APPPATH . 'views/admin/invoices/filter_params.php'; ?>
				<?php $this->load->view('admin/invoices/list_template'); ?>
			</div>
		</div>
	</div>
</div>
<?php $this->load->view('admin/includes/modals/sales_attach_file'); ?>
<div id="modal-wrapper"></div>
<script>
	var hidden_columns = [2, 6, 7, 8];
</script>
<?php init_tail(); ?>
<script>
	$(function() {
		init_invoice();
	});
</script>
<?php
$scTableConfig = [
    'number' => ['index' => 0, 'visible' => get_option('sc_invoices_col_number_visible') !== '0', 'width' => (int)(get_option('sc_invoices_col_number_width') ?: 10)],
    'amount' => ['index' => 1, 'visible' => get_option('sc_invoices_col_amount_visible') !== '0', 'width' => (int)(get_option('sc_invoices_col_amount_width') ?: 10)],
    'tax' => ['index' => 2, 'visible' => get_option('sc_invoices_col_tax_visible') !== '0', 'width' => (int)(get_option('sc_invoices_col_tax_width') ?: 8)],
    'year' => ['index' => 3, 'visible' => get_option('sc_invoices_col_year_visible') !== '0', 'width' => (int)(get_option('sc_invoices_col_year_width') ?: 7)],
    'date' => ['index' => 4, 'visible' => get_option('sc_invoices_col_date_visible') !== '0', 'width' => (int)(get_option('sc_invoices_col_date_width') ?: 10)],
    'client' => ['index' => 5, 'visible' => get_option('sc_invoices_col_client_visible') !== '0', 'width' => (int)(get_option('sc_invoices_col_client_width') ?: 15)],
    'project' => ['index' => 6, 'visible' => get_option('sc_invoices_col_project_visible') !== '0', 'width' => (int)(get_option('sc_invoices_col_project_width') ?: 14)],
    'tags' => ['index' => 7, 'visible' => get_option('sc_invoices_col_tags_visible') !== '0', 'width' => (int)(get_option('sc_invoices_col_tags_width') ?: 10)],
    'due' => ['index' => 8, 'visible' => get_option('sc_invoices_col_due_visible') !== '0', 'width' => (int)(get_option('sc_invoices_col_due_width') ?: 10)],
    'status' => ['index' => 9, 'visible' => get_option('sc_invoices_col_status_visible') !== '0', 'width' => (int)(get_option('sc_invoices_col_status_width') ?: 10)],
];
?>
<script>
(function(){
  var config = <?= json_encode($scTableConfig); ?>;
  function applyScTableConfig(){
    var selector = '.table-invoices';
    if (!$.fn.DataTable || !$.fn.DataTable.isDataTable(selector)) return;
    var dt=$(selector).DataTable();
    Object.keys(config).forEach(function(k){
      var c=config[k];
      if(c.index<dt.columns().count()){
        dt.column(c.index).visible(!!c.visible,false);
        $(dt.column(c.index).header()).css({width:c.width+'%',minWidth:c.width+'%'});
      }
    });
    dt.columns.adjust();
  }
  $(document).on('init.dt', function(e,settings){if($(settings.nTable).hasClass('table-invoices')){setTimeout(applyScTableConfig,0);}});
  $(function(){setTimeout(applyScTableConfig,350);});
})();
</script>
</body>

</html>
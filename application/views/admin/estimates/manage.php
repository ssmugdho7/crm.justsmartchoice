<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="panel-table-full">
                <div id="vueApp">
                    <div class="col-md-12 tw-mb-3">
                        <h4 class="tw-my-0 tw-font-bold tw-text-xl"><?= _l('estimates'); ?></h4>
                        <a href="#" 
							class="estimates-total tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700"
							onclick="slideToggle('#stats-top'); init_estimates_total(true); return false;">
								<?= _l('view_financial_stats'); ?>
						</a>
                    </div>                  
                    <div class="col-md-12">
                        <?php $this->load->view('admin/estimates/quick_stats'); ?>
                    </div>
                    <?php $this->load->view('admin/estimates/list_template'); ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $this->load->view('admin/includes/modals/sales_attach_file'); ?>
<script>
var hidden_columns = [2, 5, 6, 8, 9];
</script>
<?php init_tail(); ?>
<script>
$(function() {
    init_estimate();
});
</script>
<?php
$scTableConfig = [
    'number' => ['index' => 0, 'visible' => get_option('sc_estimates_col_number_visible') !== '0', 'width' => (int)(get_option('sc_estimates_col_number_width') ?: 10)],
    'amount' => ['index' => 1, 'visible' => get_option('sc_estimates_col_amount_visible') !== '0', 'width' => (int)(get_option('sc_estimates_col_amount_width') ?: 10)],
    'tax' => ['index' => 2, 'visible' => get_option('sc_estimates_col_tax_visible') !== '0', 'width' => (int)(get_option('sc_estimates_col_tax_width') ?: 8)],
    'year' => ['index' => 3, 'visible' => get_option('sc_estimates_col_year_visible') !== '0', 'width' => (int)(get_option('sc_estimates_col_year_width') ?: 7)],
    'client' => ['index' => 4, 'visible' => get_option('sc_estimates_col_client_visible') !== '0', 'width' => (int)(get_option('sc_estimates_col_client_width') ?: 15)],
    'project' => ['index' => 5, 'visible' => get_option('sc_estimates_col_project_visible') !== '0', 'width' => (int)(get_option('sc_estimates_col_project_width') ?: 14)],
    'tags' => ['index' => 6, 'visible' => get_option('sc_estimates_col_tags_visible') !== '0', 'width' => (int)(get_option('sc_estimates_col_tags_width') ?: 10)],
    'date' => ['index' => 7, 'visible' => get_option('sc_estimates_col_date_visible') !== '0', 'width' => (int)(get_option('sc_estimates_col_date_width') ?: 10)],
    'expiry' => ['index' => 8, 'visible' => get_option('sc_estimates_col_expiry_visible') !== '0', 'width' => (int)(get_option('sc_estimates_col_expiry_width') ?: 10)],
    'reference' => ['index' => 9, 'visible' => get_option('sc_estimates_col_reference_visible') !== '0', 'width' => (int)(get_option('sc_estimates_col_reference_width') ?: 10)],
    'status' => ['index' => 10, 'visible' => get_option('sc_estimates_col_status_visible') !== '0', 'width' => (int)(get_option('sc_estimates_col_status_width') ?: 10)],
];
?>
<script>
(function(){
  var config = <?= json_encode($scTableConfig); ?>;
  function applyScTableConfig(){
    var selector = '.table-estimates';
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
  $(document).on('init.dt', function(e,settings){if($(settings.nTable).hasClass('table-estimates')){setTimeout(applyScTableConfig,0);}});
  $(function(){setTimeout(applyScTableConfig,350);});
})();
</script>
</body>

</html>
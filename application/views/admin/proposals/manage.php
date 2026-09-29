<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="panel-table-full">
                <?php $this->load->view('admin/proposals/list_template'); ?>
            </div>
        </div>
    </div>
</div>
<?php $this->load->view('admin/includes/modals/sales_attach_file'); ?>
<script>
    var hidden_columns = [4, 5, 6, 7, 8];
</script>
<?php init_tail(); ?>
<div id="convert_helper"></div>
<script>
    var proposal_id;
    $(function() {
        var Proposals_ServerParams = {};
        $.each($('._hidden_inputs._filters input'), function() {
            Proposals_ServerParams[$(this).attr('name')] = '[name="' + $(this).attr('name') + '"]';
        });
        var proposalsTable = initDataTable('.table-proposals', admin_url + 'proposals/table', ['undefined'], ['undefined'],
            Proposals_ServerParams, [8, 'desc']);
        init_proposal();
    });
</script>
<?php
$scTableConfig = [
    'number' => ['index' => 0, 'visible' => get_option('sc_proposals_col_number_visible') !== '0', 'width' => (int)(get_option('sc_proposals_col_number_width') ?: 12)],
    'subject' => ['index' => 1, 'visible' => get_option('sc_proposals_col_subject_visible') !== '0', 'width' => (int)(get_option('sc_proposals_col_subject_width') ?: 18)],
    'to' => ['index' => 2, 'visible' => get_option('sc_proposals_col_to_visible') !== '0', 'width' => (int)(get_option('sc_proposals_col_to_width') ?: 15)],
    'total' => ['index' => 3, 'visible' => get_option('sc_proposals_col_total_visible') !== '0', 'width' => (int)(get_option('sc_proposals_col_total_width') ?: 10)],
    'date' => ['index' => 4, 'visible' => get_option('sc_proposals_col_date_visible') !== '0', 'width' => (int)(get_option('sc_proposals_col_date_width') ?: 10)],
    'open_till' => ['index' => 5, 'visible' => get_option('sc_proposals_col_open_till_visible') !== '0', 'width' => (int)(get_option('sc_proposals_col_open_till_width') ?: 10)],
    'project' => ['index' => 6, 'visible' => get_option('sc_proposals_col_project_visible') !== '0', 'width' => (int)(get_option('sc_proposals_col_project_width') ?: 14)],
    'tags' => ['index' => 7, 'visible' => get_option('sc_proposals_col_tags_visible') !== '0', 'width' => (int)(get_option('sc_proposals_col_tags_width') ?: 10)],
    'date_created' => ['index' => 8, 'visible' => get_option('sc_proposals_col_date_created_visible') !== '0', 'width' => (int)(get_option('sc_proposals_col_date_created_width') ?: 10)],
    'status' => ['index' => 9, 'visible' => get_option('sc_proposals_col_status_visible') !== '0', 'width' => (int)(get_option('sc_proposals_col_status_width') ?: 10)],
];
?>
<script>
(function(){
  var config = <?= json_encode($scTableConfig); ?>;
  function applyScTableConfig(){
    var selector = '.table-proposals';
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
  $(document).on('init.dt', function(e,settings){if($(settings.nTable).hasClass('table-proposals')){setTimeout(applyScTableConfig,0);}});
  $(function(){setTimeout(applyScTableConfig,350);});
})();
</script>
</body>

</html>
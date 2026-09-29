<?php
defined('BASEPATH') or exit('No direct script access allowed');
$isGridView = 0;
?>
<div class="row" id="dmn-table">
  <div class="col-md-12">
      <?php render_datatable(array(
        _l('dmn_title'),
        _l('dmn_desc'),
        _l('dmn_staff'),
        _l('project_group'),
        _l('dmn_created_at')
      ),'dmn', array('customizable-table'),
      array(
          'id'=>'table-dmn',
          'data-last-order-identifier'=>'dmn',
          'data-default-order'=>get_table_last_order('dmn'),
      )); ?>
  </div>
</div>
<?php init_tail(); ?>
<script>
    var _lnth = 12;
    $(function(){
        var id = $('input[name=project_id]').val();
        var TblServerParams = {
            "assigned": "[name='view_assigned']",
            "group": "[name='view_group']",
            "project_id": id,
        };
        var tAPI = initDataTable('.table-dmn', admin_url+'dmn/project_table?project_id='+id, [2, 3], [2, 3], TblServerParams,[4, 'desc']);
        $.each(TblServerParams, function(i, obj) {
            $('select' + obj).on('change', function() {
                $('table.table-dmn').DataTable().ajax.reload()
                .columns.adjust()
                .responsive.recalc();
           });
        });
    });
</script>
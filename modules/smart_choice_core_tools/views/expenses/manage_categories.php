<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content scct-expenses-workspace">
        <?php $this->load->view('../../modules/smart_choice_core_tools/views/expenses/navigation'); ?>
        <div class="row">
            <div class="col-md-12">
                <h4><?= _l('expense_categories'); ?></h4>
                <div class="tw-mb-2">
                    <a href="#" onclick="new_category(); return false;" class="btn btn-primary">
                        <i class="fa-regular fa-plus tw-mr-1"></i>
                        <?= _l('new_expense_category'); ?>
                    </a>
                </div>
                <?php $this->load->view('../../modules/smart_choice_core_tools/views/expenses/category_spending'); ?>
                <div class="panel_s">
                    <div class="panel-body panel-table-full">
                        <?php render_datatable([_l('id'), _l('name'), _l('dt_expense_description'), _l('options')], 'expenses-categories'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $this->load->view('admin/expenses/expense_category'); ?>
<?php init_tail(); ?>
<script>
    $(function() {
        initDataTable('.table-expenses-categories', window.location.href, [3], [3], 'undefined', [1, 'asc']);
    });
</script>
</body>

</html>
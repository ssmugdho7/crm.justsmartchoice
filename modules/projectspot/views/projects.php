<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <?php if (has_permission('projectspot', '', 'create')) { ?>
                    <div class="tw-mb-2 sm:tw-mb-4">
                        <a href="<?php echo admin_url('projectspot/create_project'); ?>" class="btn btn-primary">
                            <i class="fa-regular fa-plus tw-mr-1"></i>
                            <?php echo _l('projectspot_add_project'); ?>
                        </a>
                    </div>
                <?php } ?>
                <div class="panel_s">
                    <div class="panel-body panel-table-full">
                        <?php render_datatable([
                            _l('projectspot_project_name'),
                            _l('projectspot_category_name'),
                            _l('projectspot_is_enabled'),
                            _l('projectspot_created_at'),
                            _l('options'),
                        ], 'projectspot-projects'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    $(function() {
        initDataTable('.table-projectspot-projects', window.location.href, [3], [3], [], [4, 'desc']);
    });
</script>
</body>

</html>
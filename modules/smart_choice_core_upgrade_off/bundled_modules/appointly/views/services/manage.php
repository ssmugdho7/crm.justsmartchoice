<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <?php if (staff_can('create', 'appointments')) { ?>
                    <div class="tw-mb-2">
                        <a href="<?php echo admin_url('appointly/services/service'); ?>" class="btn btn-primary">
                            <i class="fa-regular fa-plus tw-mr-1"></i>
                            <?php echo _l('new_service'); ?>
                        </a>
                    </div>
                <?php } ?>

                <div class="panel_s">
                    <div class="panel-body panel-table-full">
                        <?php
                        $table_data = [
                            _l('appointly_service_name'),
                            _l('appointly_service_duration') . ' (' . _l('minutes') . ')',
                            _l('appointly_service_price'),
                            _l('appointly_service_staff'),
                            [
                                'name' => _l('appointly_service_active'),
                                'th_attrs' => ['class' => 'toggleActive']
                            ],
                            _l('options')
                        ];

                        render_datatable($table_data, 'services', [], [
                            'id' => 'table-services',
                            'data-last-order-col' => '0',
                            'data-last-order-type' => 'asc'
                        ]);
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>
<script>
    $(function() {
        var ServiceServerParams = {};
        initDataTable('.table-services', admin_url + 'appointly/services/table', [5], [5], ServiceServerParams, [0, 'asc']);
    });

    // Define appointlyLang for use in services.js
    var appointlyLang = {
        durationMinutes: "<?php echo addslashes(_l('minutes')); ?>",
        selectProvider: "<?php echo addslashes(_l('appointly_select_provider')); ?>",
        appointly_service_in_use_warning: "<?php echo addslashes(_l('appointly_service_in_use_warning')); ?>"
    };
</script>
<script src="<?= module_dir_url('appointly', 'assets/js/services.js'); ?>?v=<?= time(); ?>"></script>

<style>
    .toggleActive {
        width: 100px;
        text-align: center;
    }

    .toggle.btn {
        min-width: 60px;
    }
</style>
</body>

</html>

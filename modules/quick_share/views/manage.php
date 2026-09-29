<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <?php if (has_permission('quick_share', '', 'create')) { ?>
                    <div class="tw-mb-2 sm:tw-mb-4">
                        <a href="<?php echo admin_url('quick_share/upload'); ?>" class="btn btn-primary">
                            <i class="fa-regular fa-plus tw-mr-1"></i>
                            <?php echo _l('upload_file'); ?>
                        </a>
                    </div>
                <?php } ?>
                <div class="panel_s">
                    <div class="panel-body panel-table-full">
                        <?php render_datatable([
                            _l('qs_table_file_path'),
                            _l('qs_table_file_size'),
                            _l('qs_table_file_status'),
                            _l('qs_table_file_auto_destroy'),
                            _l('qs_table_file_share_type'),
                            _l('qs_table_file_has_password'),
                            _l('qs_table_file_total_downloads'),
                            _l('qs_table_file_created_at'),
                            _l('options'),
                        ], 'quick-share'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    $(function() {
        initDataTable('.table-quick-share', window.location.href, [7], [7], [], [7, 'desc']);
    });

    function copyFileLink(element, id) {
        var textToCopy = id;

        var tempInput = document.createElement("input");
        tempInput.value = textToCopy;
        document.body.appendChild(tempInput);

        tempInput.select();
        document.execCommand("copy");

        document.body.removeChild(tempInput);

        alert_float('success', 'Link copied successfully');
    }
</script>
</body>

</html>
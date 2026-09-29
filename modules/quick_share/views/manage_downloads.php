<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-text-neutral-700">
                    <?php echo $title; ?>
                </h4>
                <div class="panel_s">
                    <div class="panel-body panel-table-full">
                        <?php render_datatable([
                            _l('qs_downloads_table_downloaded_file'),
                            _l('qs_downloads_table_download_ip'),
                            _l('qs_table_file_created_at'),
                        ], 'quick-share-downloads'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    $(function() {
        initDataTable('.table-quick-share-downloads', window.location.href, [2], [2], [], [2, 'desc']);
    });
</script>
</body>

</html>
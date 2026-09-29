<div class="modal fade" id="_custom_tables_popup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <div class="row">
                    <?php echo form_open_multipart('', ['id' => 'table_customizer_form']); ?>
                    <div class="col-md-12">
                        <h4 class="tw-font-semibold tw-mt-0 tw-text-neutral-800">
                            <?php echo $tab['name']; ?> <small class="text-danger pull-right" style="font-style: italic;"><?= _l('required_note'); ?></small>
                        </h4>
                        <?php $this->config->load(CUSTOMTABLES_MODULE . '/config'); ?>
                        <?php $this->load->view($tab['view']); ?>
                    </div>
                    <?php echo form_close(); ?>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-success" onclick="location.reload()">Close & Refresh</button>
            </div>
	</div>
</div>
<?php defined('BASEPATH') or exit('No direct script access allowed');
/**
 * @var string $title
 * @var array[] $features
 * @var array[] $invoiceStatuses
 */
init_head();
?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-6 col-md-offset-3">
                <h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-text-neutral-700"><?= $title ?></h4>
                <?= form_open($this->uri->uri_string(), ['id' => 'xml-export-form']); ?>
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-12">

                                <div class="form-group select-placeholder">
                                    <label for="export_type"><?= _l('csv_export_select_type') ?></label>
                                    <select readonly="1" name="export_type" id="export_type" class="selectpicker"
                                            data-width="100%"
                                            data-none-selected-text="<?= _l('dropdown_non_selected_tex') ?>" disabled>
                                        <?php foreach ($features as $feature => $name) { ?>
                                            <option selected value="<?= $feature ?>"><?= $name ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <?php echo render_date_input('date-from', 'zip_from_date'); ?>
                                <?php echo render_date_input('date-to', 'zip_to_date'); ?>
                                <div class="form-group  shifter invoices_shifter">
                                    <label for="status"><?php echo _l('bulk_export_status'); ?></label>
                                    <div class="radio radio-primary">
                                        <input type="radio" id="all" value="all" checked name="status">
                                        <label for="all"><?php echo _l('bulk_export_status_all'); ?></label>
                                    </div>
                                    <?php foreach ($invoiceStatuses as $status) { ?>
                                        <div class="radio radio-primary">
                                            <input type="radio"
                                                   id="invoice_<?php echo format_invoice_status($status, '', false); ?>"
                                                   value="<?php echo $status; ?>" name="invoices_export_status">
                                            <label
                                                    for="invoice_<?php echo format_invoice_status($status, '', false); ?>"><?php echo format_invoice_status($status, '', false); ?></label>
                                        </div>
                                    <?php } ?>
                                    <hr/>
                                    <div class="radio radio-primary">
                                        <input type="radio" id="invoice_not_send" value="not_send"
                                               name="invoices_export_status">
                                        <label for="invoice_not_send"><?php echo _l('not_sent_indicator'); ?></label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="panel-footer text-right">
                        <button class="btn btn-primary" type="submit">
                            <?= _l('csv_export_button') ?>
                        </button>
                    </div>
                </div>
                <?= form_close() ?>
            </div>
        </div>
    </div>
</div>
</div>
<?php init_tail(); ?>
<script>
    appValidateForm('#xml-export-form', {
        'export_type': 'required',
        'date-from': 'required',
        'date-to': 'required',
    });
</script>
</body>
</html>

<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$this->load->helper('smart_choice_sales_links');
$this->app_css->add('sc-sales-form-layout', 'assets/css/sales-form-layout.css?v=1');
init_head();
?>
<div id="wrapper">
    <div class="content">
        <?php $this->load->view('admin/includes/sales_workspace'); ?>
        <div class="row">
            <?php
            echo form_open($this->uri->uri_string(), ['id' => 'estimate-form', 'class' => '_transaction_form estimate-form', 'enctype' => 'multipart/form-data']);
            if (isset($estimate)) {
                echo form_hidden('isedit');
            }
            ?>
            <div class="col-md-12">
                <h4
                    class="tw-mt-0 tw-font-bold tw-text-lg tw-text-neutral-700 tw-flex tw-items-center tw-space-x-2">
                    <span data-sc-document-number class="hide"><?= isset($estimate) ? e(format_estimate_number($estimate->id)) : ''; ?></span><span data-sc-document-heading>
                        <?php echo isset($estimate) ? e(sc_sales_document_heading('estimate', format_estimate_number($estimate))) : _l('create_new_estimate'); ?>
                    </span>
                    <?php echo isset($estimate) ? format_estimate_status($estimate->status) : ''; ?>
                </h4>
                <?php $this->load->view('admin/estimates/estimate_template'); ?>
            <?php $this->load->view('admin/includes/smart_choice_sales_attachments', ['sc_rel_type'=>'estimate','sc_rel_id'=>isset($estimate)?(int)$estimate->id:0]); ?>
            <?php $this->load->view('admin/includes/smart_choice_sales_links', ['sc_rel_type'=>'estimate','sc_rel_id'=>isset($estimate)?$estimate->id:0]); ?>
            </div>
            <?php echo form_close(); ?>
            <?php $this->load->view('admin/invoice_items/item'); ?>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
$(function() {
    init_editor('#clientnote,#terms', { height: 240 });
    validate_estimate_form();
    // Init accountacy currency symbol
    init_currency();
    // Project ajax search
    init_ajax_project_search_by_customer_id();
    if ($('#item_select.ajax-search').length) {
        init_ajax_search('items', '#item_select.ajax-search', undefined, admin_url + 'items/search');
    }
});
</script>
</body>

</html>

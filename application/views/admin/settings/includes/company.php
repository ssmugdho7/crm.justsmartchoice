<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div role="tabpanel" class="tab-pane" id="company_info">
    <div class="alert alert-info">
        <?php echo _l('settings_sales_company_info_note'); ?>
    </div>
    <?php echo render_input('settings[invoice_company_name]', 'settings_sales_company_name', get_option('invoice_company_name')); ?>
    <?php echo render_input('settings[invoice_company_address]', 'settings_sales_address', get_option('invoice_company_address')); ?>
    <?php echo render_input('settings[invoice_company_city]', 'settings_sales_city', get_option('invoice_company_city')); ?>
    <?php echo render_input('settings[company_state]', 'billing_state', get_option('company_state')); ?>
    <?php echo render_input('settings[invoice_company_country_code]', 'settings_sales_country_code', get_option('invoice_company_country_code')); ?>
    <?php echo render_input('settings[invoice_company_postal_code]', 'settings_sales_postal_code', get_option('invoice_company_postal_code')); ?>
    <?php echo render_input('settings[invoice_company_phonenumber]', 'settings_sales_phonenumber', get_option('invoice_company_phonenumber')); ?>
    <?php echo render_input('settings[company_vat]', 'EIN #', get_option('company_vat'), 'text', [
        'maxlength'    => 11,
        'placeholder'  => '###-##-####',
        'pattern'      => '[0-9]{3}-[0-9]{2}-[0-9]{4}',
        'inputmode'    => 'numeric',
        'autocomplete' => 'off',
        'class'        => 'form-control smart-choice-ein-mask',
    ]); ?>
    <?php echo render_input('settings[company_crm_domain]', 'Company CRM Domain', get_option('company_crm_domain') ?: 'https://crm.justsmartchoice.com', 'url', [
        'placeholder' => 'https://crm.justsmartchoice.com',
    ]); ?>
    <?php echo render_custom_fields('company', 0); ?>
    <hr />
    <?php echo render_textarea('settings[company_info_format]', 'company_info_format', clear_textarea_breaks(get_option('company_info_format')), ['rows' => 8, 'style' => 'line-height:20px;']); ?>
    <p>
        <a href="#" class="settings-textarea-merge-field" data-to="company_info_format">{company_name}</a>
        <a href="#" class="settings-textarea-merge-field" data-to="company_info_format">{address}</a>,
        <a href="#" class="settings-textarea-merge-field" data-to="company_info_format">{city}</a>,
        <a href="#" class="settings-textarea-merge-field" data-to="company_info_format">{state}</a>,
        <a href="#" class="settings-textarea-merge-field" data-to="company_info_format">{zip_code}</a>,
        <a href="#" class="settings-textarea-merge-field" data-to="company_info_format">{country_code}</a>,
        <a href="#" class="settings-textarea-merge-field" data-to="company_info_format">{phone}</a>,
        <a href="#" class="settings-textarea-merge-field" data-to="company_info_format">{ein_number}</a>,
        <a href="#" class="settings-textarea-merge-field" data-to="company_info_format">{ein_number_with_label}</a>,
        <a href="#" class="settings-textarea-merge-field" data-to="company_info_format">{crm_portal_url}</a>
    </p>
    <?php $custom_company_fields = get_company_custom_fields();
    if (count($custom_company_fields) > 0) {
        echo '<hr />';
        echo '<p class="font-medium"><b>' . _l('custom_fields') . '</b></p>';
        echo '<ul class="list-group">';
        foreach ($custom_company_fields as $field) {
            echo '<li class="list-group-item"><b>' . $field['name'] . '</b>: ' . '<a href="#" class="settings-textarea-merge-field" data-to="company_info_format">{cf_' . $field['id'] . '}</a></li>';
        }
        echo '</ul>';
        echo '<hr />';
    }
    ?>
</div>
<script>
(function () {
    function smartChoiceFormatEin(value) {
        var numbers = String(value || '').replace(/\D/g, '').substring(0, 9);
        if (numbers.length > 5) {
            return numbers.substring(0, 3) + '-' + numbers.substring(3, 5) + '-' + numbers.substring(5);
        }
        if (numbers.length > 3) {
            return numbers.substring(0, 3) + '-' + numbers.substring(3);
        }
        return numbers;
    }

    function smartChoiceBindEinMask() {
        var input = document.querySelector('input[name="settings[company_vat]"]');
        if (!input || input.dataset.smartChoiceEinBound === '1') {
            return;
        }
        input.dataset.smartChoiceEinBound = '1';
        input.value = smartChoiceFormatEin(input.value);
        input.addEventListener('input', function () {
            input.value = smartChoiceFormatEin(input.value);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', smartChoiceBindEinMask);
    } else {
        smartChoiceBindEinMask();
    }
})();
</script>

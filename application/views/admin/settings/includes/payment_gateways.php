<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="horizontal-scrollable-tabs panel-full-width-tabs">
    <div class="scroller arrow-left tw-rounded-tl-md"><i class="fa fa-angle-left"></i></div>
    <div class="scroller arrow-right tw-rounded-tr-md"><i class="fa fa-angle-right"></i></div>
    <div class="horizontal-tabs">
        <ul class="nav nav-tabs nav-tabs-horizontal" role="tablist">
            <li role="presentation" class="active">
                <a href="#payment_modes_general" aria-controls="payment_modes_general" role="tab"
                    data-toggle="tab"><?= _l('settings_group_general'); ?></a>
            </li>
            <?php foreach ($payment_gateways as $gateway) {    ?>
            <li role="presentation">
                <a href="#online_payments_<?= e($gateway['id']); ?>_tab"
                    aria-controls="online_payments_paypal_tab" role="tab" data-toggle="tab">
                    <?= e($gateway['instance']->getName()) ?>
                </a>
            </li>
            <?php } ?>
        </ul>
    </div>
</div>

<div class="tab-content mtop30">
    <div role="tabpanel" class="tab-pane active" id="payment_modes_general">
        <?php render_yes_no_option('notification_when_customer_pay_invoice', 'notification_when_customer_pay_invoice'); ?>
        <hr />
        <?php render_yes_no_option('allow_payment_amount_to_be_modified', 'settings_allow_payment_amount_to_be_modified'); ?>
    </div>
    <?php foreach ($payment_gateways as $gateway) { ?>
    <div role="tabpanel" class="tab-pane"
        id="online_payments_<?= e($gateway['id']); ?>_tab">
        <h4><?= e($gateway['instance']->getName()); ?>
        </h4>
        <?php hooks()->do_action('before_render_payment_gateway_settings', $gateway); ?>
        <hr />
        <?php
     $settings = $gateway['instance']->getSettings();

        foreach ($settings as $option) {
            $value = get_option($option['name']);
            $fieldAttributes = ($option['field_attributes'] ?? []);

            if (isset($option['encrypted']) && $option['encrypted'] == true) {
                // Never render stored secrets back into HTML. A blank field preserves
                // the existing encrypted value; entering a value replaces it.
                $fieldAttributes['data-has-saved-secret'] = $value !== '' ? '1' : '0';
                $value = '';
            }

            if (! isset($option['type'])) {
                $option['type'] = 'input';
            }

            $optionName      = 'settings[' . $option['name'] . ']';
            $optionLabel     = $option['label'];

            if ($option['type'] == 'yes_no') {
                render_yes_no_option($option['name'], $option['label']);
            } elseif ($option['type'] == 'input') {
                echo render_input($optionName, $optionLabel, $value, ($option['input_type'] ?? 'text'), $fieldAttributes);
            } elseif ($option['type'] == 'textarea') {
                echo render_textarea($optionName, $optionLabel, $value, $fieldAttributes);
            }

            if (isset($option['after'])) {
                echo $option['after'];
            }
        } ?>
    </div>
    <?php } ?>
</div>
<script>
(function(){
    function initGatewaySecretToggle(inputName, showText, hideText){
        var input=document.querySelector('input[name="settings['+inputName+']"]');
        if(!input || input.dataset.scSecretToggleReady==='1') return;
        input.dataset.scSecretToggleReady='1';
        input.setAttribute('data-sc-money-ignore','1');
        var wrap=document.createElement('div');
        wrap.className='tw-mt-2';
        var btn=document.createElement('button');
        btn.type='button';
        btn.className='btn btn-default btn-xs';
        btn.textContent=showText;
        btn.addEventListener('click',function(){
            var show=input.type==='password';
            input.type=show?'text':'password';
            btn.textContent=show?hideText:showText;
        });
        wrap.appendChild(btn);
        input.parentNode.appendChild(wrap);
    }
    function protectGatewayCredentialInputs(){
        document.querySelectorAll('input[name^="settings[paymentmethod_"]').forEach(function(input){
            var n=(input.getAttribute('name')||'').toLowerCase();
            if(/(client_id|secret|api|key|token|merchant_id|webhook_id|username|password|signature)/.test(n)){
                input.setAttribute('data-sc-money-ignore','1');
                input.setAttribute('autocomplete','off');
            }
        });
    }
    function openRequestedGatewayTab(){
        var params=new URLSearchParams(window.location.search);
        var tab=params.get('tab')||window.location.hash.replace(/^#/,'');
        if(!tab) return;
        var selector='a[href="#'+tab.replace(/"/g,'')+'"]';
        var link=document.querySelector(selector);
        if(link){
            if(window.jQuery && jQuery.fn.tab){ jQuery(link).tab('show'); }
            else { link.click(); }
        }
    }
    function init(){
        protectGatewayCredentialInputs();
        initGatewaySecretToggle('paymentmethod_paypal_checkout_secret','<?= e(_l('sc_show_secret_key')); ?>','<?= e(_l('sc_hide_secret_key')); ?>');
        initGatewaySecretToggle('paymentmethod_stripe_api_secret_key','<?= e(_l('sc_show_secret_key')); ?>','<?= e(_l('sc_hide_secret_key')); ?>');
        openRequestedGatewayTab();
    }
    if(document.readyState==='loading') document.addEventListener('DOMContentLoaded',init); else init();
})();
</script>

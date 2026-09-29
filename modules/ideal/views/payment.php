<?php defined('BASEPATH') or exit('No direct script access allowed'); echo payment_gateway_head(); ?>
<script src="https://js.stripe.com/v3/"></script>
<body class="gateway-stripe-ideal">
<div class="container"><div class="col-md-8 col-md-offset-2 mtop30">
<div class="mbot30 text-center"><?php echo payment_gateway_logo(); ?></div>
<div class="panel_s"><div class="panel-heading"><h4><?php echo _l('payment_for_invoice'); ?> <a href="<?php echo site_url('invoice/'.$invoice->id.'/'.$invoice->hash); ?>"><?php echo html_escape(format_invoice_number($invoice->id)); ?></a></h4></div>
<div class="panel-body">
<p><strong><?php echo _l('ideal_invoice_payment_amount'); ?>:</strong> <?php echo app_format_money($base_amount, $invoice->currency_name); ?></p>
<?php if ((float)$fee > 0): ?><p><strong><?php echo _l('ideal_processing_fee'); ?>:</strong> <?php echo app_format_money($fee, $invoice->currency_name); ?></p><?php endif; ?>
<p><strong><?php echo _l('total'); ?>:</strong> <?php echo app_format_money($total, $invoice->currency_name); ?></p>
<div id="checkout"></div></div></div></div></div>
<script>
(async function(){try{const stripe=Stripe(<?php echo json_encode($this->ideal_gateway->getPublishableKey()); ?>);const checkout=await stripe.initEmbeddedCheckout({fetchClientSecret:async()=><?php echo json_encode($client_secret); ?>});checkout.mount('#checkout');}catch(e){window.location.href=<?php echo json_encode(site_url('invoice/'.$invoice->id.'/'.$invoice->hash)); ?>;}})();
</script>
</body><?php echo payment_gateway_footer(); ?>

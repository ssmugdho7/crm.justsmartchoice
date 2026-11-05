<?php defined('BASEPATH') or exit('No direct script access allowed');
if (!scaf_customer_allowed($client->userid)) return;
$CI = &get_instance();
$contact = $CI->db->where('userid',(int)$client->userid)->where('is_primary',1)->get(db_prefix().'contacts')->row_array();
$name = trim((string)$client->company) ?: trim(($contact['firstname'] ?? '').' '.($contact['lastname'] ?? ''));
$name = $name ?: _l('client').' #'.(int)$client->userid;
$address = trim(implode(', ', array_filter([$client->address ?? '',$client->city ?? '',$client->state ?? '',$client->zip ?? ''])));
$address = trim(preg_replace('/\s+/', ' ', strip_tags(preg_replace('~<br\s*/?>~i', ', ', $address))));
$balances = scaf_customer_finances($client->userid); ?>
<header class="scaf-customer-hero"><span class="scaf-contact-avatar"><?= e(scaf_customer_initials($name)); ?></span><div><h2><?= e($name); ?></h2><p><?= e($address); ?></p><?php if (!empty($contact['email'])) { ?><a href="mailto:<?= e($contact['email']); ?>"><?= e($contact['email']); ?></a><?php } ?> <?php if (!empty($contact['phonenumber'])) { ?><a href="tel:<?= e(preg_replace('/[^+0-9]/','',$contact['phonenumber'])); ?>"><?= e($contact['phonenumber']); ?></a><?php } ?></div></header>
<section class="scaf-customer-kpis" aria-label="<?= e(_l('scaf_lifetime_financials')); ?>">
<?php foreach (['invoiced'=>'invoiced_amount','paid'=>'amount_paid','due'=>'balance_due'] as $key=>$label) { ?><div class="scaf-customer-kpi"><span><?= e(_l($label)); ?></span><strong><?= scaf_customer_money_lines($balances,$key); ?></strong></div><?php } ?><div class="scaf-customer-kpi"><span><?= e(_l('scaf_active_sites')); ?></span><strong><?= scaf_customer_project_count($client->userid,true) ?? '—'; ?></strong></div>
</section>
<div class="scaf-customer-quick">
<?php if (staff_can('create','invoices')) { ?><a class="btn btn-primary" href="<?= admin_url('invoices/invoice?customer_id='.(int)$client->userid); ?>"><?= e(_l('create_new_invoice')); ?></a><?php } ?>
<?php if (staff_can('create','estimates')) { ?><a class="btn btn-default" href="<?= admin_url('estimates/estimate?customer_id='.(int)$client->userid); ?>"><?= e(_l('create_new_estimate')); ?></a><?php } ?>
<?php if (staff_can('view','invoices') || staff_can('view','payments')) { ?><a class="btn btn-default" href="<?= admin_url('clients/client/'.(int)$client->userid.'?group=statement'); ?>"><?= e(_l('statement')); ?></a><?php } ?>
</div>

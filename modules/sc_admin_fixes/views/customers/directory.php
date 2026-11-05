<?php defined('BASEPATH') or exit('No direct script access allowed');
$CI = &get_instance();
if (!staff_can('view','customers')) $CI->db->where('userid IN (SELECT customer_id FROM '.db_prefix().'customer_admins WHERE staff_id='.(int)get_staff_user_id().')',null,false);
$customers = $CI->db->select('userid,active')->get(db_prefix().'clients')->result_array();
$balances = scaf_customer_finances();
$cards = [
 ['scaf_managed_accounts', (string)count($customers), 'fa-users'],
 ['scaf_billed_ltv', scaf_customer_money_lines($balances,'invoiced'), 'fa-chart-line'],
 ['balance_due', scaf_customer_money_lines($balances,'due'), 'fa-file-invoice-dollar'],
 ['scaf_active_sites', scaf_customer_project_count(null,true) ?? '—', 'fa-diagram-project'],
]; ?>
<header class="scaf-customer-header"><div><h2><?= e(_l('clients')); ?></h2><p><?= e(_l('scaf_customer_scope')); ?></p></div><a class="btn btn-default" href="<?= admin_url('clients/all_contacts'); ?>"><?= e(_l('customer_contacts')); ?></a></header>
<section class="scaf-customer-kpis" aria-label="<?= e(_l('scaf_customer_summary')); ?>">
<?php foreach ($cards as [$label,$value,$icon]) { ?><div class="scaf-customer-kpi"><svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="4"/><path d="M7 16v-4m5 4V8m5 8v-6"/></svg><span><?= e(_l($label)); ?></span><strong><?= $value; ?></strong><?php if ($label === 'scaf_managed_accounts') { $active=count(array_filter($customers,static function($c){return (int)$c['active']===1;})); ?><small><?= e($active.' / '.count($customers).' '. _l('active_customers')); ?></small><?php } ?></div><?php } ?>
</section>
<div class="scaf-customer-segments" role="group" aria-label="<?= e(_l('scaf_segments')); ?>"><?php foreach (['all'=>'clients','residential'=>'scaf_active_residential','commercial'=>'scaf_commercial_accounts','pastdue'=>'scaf_past_due_accounts','vip'=>'scaf_vip_base_currency'] as $key=>$label) { ?><button type="button" class="btn btn-default" data-scaf-segment="<?= e($key); ?>" aria-pressed="<?= $key === 'all' ? 'true' : 'false'; ?>"><?= e(_l($label)); ?><?php if ($key === 'vip') echo ' ('.e(get_base_currency()->name).')'; ?></button><?php } ?></div>
<div class="scaf-customer-batch" hidden role="region" aria-label="<?= e(_l('bulk_actions')); ?>"><strong data-scaf-selected></strong><button type="button" class="btn btn-default" data-scaf-export>CSV</button><button type="button" class="btn btn-default" data-scaf-statements><?= e(_l('statement')); ?></button><button type="button" class="btn btn-default" data-toggle="modal" data-target="#customers_bulk_actions"><?= e(_l('bulk_actions')); ?></button><button type="button" class="btn btn-default" data-scaf-clear><?= e(_l('clear')); ?></button></div>

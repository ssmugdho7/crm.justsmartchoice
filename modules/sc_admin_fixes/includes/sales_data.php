<?php
defined('BASEPATH') or exit('No direct script access allowed');

/** Exact sums stay separated by currency. Presentation never authorizes writes. */
function scaf_sales_invoice_metrics(array $rows, $today)
{
    $groups = [];
    foreach ($rows as $row) {
        $currency = $row['currency_name'];
        if (!isset($groups[$currency])) { $groups[$currency] = ['paid'=>'0','due'=>'0','past_due'=>'0','issued'=>'0','count'=>0,'paid_count'=>0,'due_count'=>0,'past_count'=>0,'aging'=>['0','0','0','0']]; }
        $g = &$groups[$currency];
        $paid = (string) ($row['paid'] ?? '0');
        $due = scaf_decimal_sub(scaf_decimal_sub($row['total'], $paid), $row['credited'] ?? '0');
        if (scaf_decimal_cmp($due, '0') < 0) { $due = '0'; }
        $g['paid'] = scaf_decimal_add($g['paid'], $paid);
        $g['issued'] = scaf_decimal_add($g['issued'], $row['total']);
        $g['due'] = scaf_decimal_add($g['due'], $due); $g['count']++;
        if (scaf_decimal_cmp($due, '0') === 0) { $g['paid_count']++; } else {
            $g['due_count']++;
            $days = !empty($row['duedate']) && $row['duedate'] !== '0000-00-00' ? max(0, (int) (new DateTimeImmutable($row['duedate']))->diff(new DateTimeImmutable($today))->format('%r%a')) : 0;
            $bucket = $days <= 30 ? 0 : ($days <= 60 ? 1 : ($days <= 90 ? 2 : 3));
            $g['aging'][$bucket] = scaf_decimal_add($g['aging'][$bucket], $due);
            if ($days > 30) { $g['past_due'] = scaf_decimal_add($g['past_due'], $due); $g['past_count']++; }
        }
        unset($g);
    }
    return $groups;
}
function scaf_sales_invoice_data()
{
    $CI = &get_instance();
    $CI->load->model('invoices_model');
    $year = $CI->input->post('years');
    $years = is_array($year) ? array_values(array_filter($year, function ($v) { return ctype_digit((string) $v) && (int) $v >= 1900 && (int) $v <= 2200; })) : [];
    if (!$years) { $years = [(string) date('Y')]; }
    $p = db_prefix();
    $CI->db->select("i.id,i.total,i.duedate,c.name as currency_name,(SELECT COALESCE(SUM(amount),0) FROM {$p}invoicepaymentrecords p WHERE p.invoiceid=i.id) as paid,(SELECT COALESCE(SUM(amount),0) FROM {$p}credits a WHERE a.invoice_id=i.id) as credited", false)->from($p . 'invoices i')->join($p . 'currencies c', 'c.id=i.currency', 'left')->where_not_in('i.status', [5,6])->where_in('YEAR(i.date)', $years);
    if (!staff_can('view', 'invoices')) {
        // Native helper references unaliased invoices; remove only that exact prefix for this query alias.
        $scope = str_replace($p . 'invoices.', 'i.', get_invoices_where_sql_for_staff(get_staff_user_id()));
        $CI->db->where('(' . $scope . ')', null, false);
    }
    foreach (['customer_id'=>'i.clientid','project_id'=>'i.project_id'] as $key=>$column) {
        $value=$CI->input->post($key);
        if(ctype_digit((string)$value)&&(int)$value>0) {$CI->db->where($column,(int)$value);}
    }
    $currency = $CI->input->post('currency');
    if (ctype_digit((string) $currency) && (int) $currency > 0) { $CI->db->where('i.currency', (int) $currency); }
    return ['years'=>$years, 'groups'=>scaf_sales_invoice_metrics($CI->db->get()->result_array(), date('Y-m-d'))];
}
function scaf_sales_payment_data()
{
    $CI = &get_instance(); $p = db_prefix();
    $CI->db->select('p.amount,p.paymentmode,p.paymentmethod,c.name as currency_name,m.name as mode_name')->from($p . 'invoicepaymentrecords p')->join($p . 'invoices i','i.id=p.invoiceid')->join($p . 'currencies c','c.id=i.currency','left')->join($p . 'payment_modes m','m.id=p.paymentmode','left');
    if (!staff_can('view', 'payments')) {
        $id = (int) get_staff_user_id();
        $scope = '(i.addedfrom=' . $id . ' AND i.addedfrom IN (SELECT staff_id FROM ' . $p . 'staff_permissions WHERE feature="invoices" AND capability="view_own"))';
        if (get_option('allow_staff_view_invoices_assigned') == 1) { $scope .= ' OR i.sale_agent=' . $id; }
        $CI->db->where('(' . $scope . ')', null, false);
    }
    $records=$CI->db->get()->result_array();
    $CI->load->model('payment_modes_model');
    $gateways=[];foreach($CI->payment_modes_model->get_payment_gateways(true) as $gateway){$gateways[$gateway['id']]=$gateway['name'];}
    $groups = [];
    foreach ($records as $row) {
        $currency = $row['currency_name'];
        if (!isset($groups[$currency])) { $groups[$currency] = ['total'=>'0','count'=>0,'modes'=>[]]; }
        $mode = $row['mode_name'] ?: ($gateways[$row['paymentmode']] ?? $row['paymentmode']);
        if (!empty($row['paymentmethod'])) { $mode .= ' · ' . $row['paymentmethod']; }
        $g = &$groups[$currency]; $g['total'] = scaf_decimal_add($g['total'], $row['amount']); $g['count']++;
        $g['modes'][$mode] = scaf_decimal_add($g['modes'][$mode] ?? '0', $row['amount']); unset($g);
    }
    return $groups;
}
function scaf_sales_credit_data()
{
    $CI = &get_instance(); $p = db_prefix();
    $CI->db->select("n.total,n.status,c.name as currency_name,(SELECT COALESCE(SUM(amount),0) FROM {$p}credits a WHERE a.credit_id=n.id) as applied,(SELECT COALESCE(SUM(amount),0) FROM {$p}creditnote_refunds r WHERE r.credit_note_id=n.id) as refunded", false)->from($p . 'creditnotes n')->join($p . 'currencies c','c.id=n.currency','left')->where('n.status !=',3);
    if (!staff_can('view','credit_notes')) { $CI->db->where('n.addedfrom',get_staff_user_id()); }
    $groups=[];
    foreach ($CI->db->get()->result_array() as $row) {
        $currency=$row['currency_name'];
        if (!isset($groups[$currency])) { $groups[$currency]=['issued'=>'0','applied'=>'0','refunded'=>'0','available'=>'0']; }
        $g=&$groups[$currency];
        foreach (['issued'=>'total','applied'=>'applied','refunded'=>'refunded'] as $key=>$field) { $g[$key]=scaf_decimal_add($g[$key],$row[$field]); }
        // Closed notes have no usable credit even if legacy rounding leaves a residue.
        if ((int)$row['status']===1) { $remaining=scaf_decimal_sub(scaf_decimal_sub($row['total'],$row['applied']),$row['refunded']); if(scaf_decimal_cmp($remaining,'0')>0) {$g['available']=scaf_decimal_add($g['available'],$remaining);} }
        unset($g);
    }
    return $groups;
}

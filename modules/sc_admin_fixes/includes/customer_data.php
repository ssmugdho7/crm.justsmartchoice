<?php
defined('BASEPATH') or exit('No direct script access allowed');
function scaf_customer_allowed($id)
{
    return staff_can('view', 'customers') || is_customer_admin((int)$id);
}
function scaf_customer_initials($name)
{
    $parts = preg_split('/\s+/u', trim((string)$name), -1, PREG_SPLIT_NO_EMPTY);
    if (!$parts) return '?';
    return mb_strtoupper(mb_substr($parts[0], 0, 1) . (count($parts) > 1 ? mb_substr(end($parts), 0, 1) : ''));
}
/** Authorized invoice sums, grouped by original currency. Never combine unlike currencies. */
function scaf_customer_finances($customerId = null)
{
    $CI = &get_instance(); $prefix = db_prefix(); $result = [];
    if ($customerId !== null && !scaf_customer_allowed($customerId)) return [];
    if (!staff_can('view', 'invoices') && !staff_can('view_own', 'invoices') && !get_option('allow_staff_view_invoices_assigned')) return [];
    $CI->db->select('id,clientid,currency,total')->from($prefix.'invoices')->where_not_in('status', [5,6]);
    if ($customerId !== null) $CI->db->where('clientid', (int)$customerId);
    if (!staff_can('view', 'customers')) $CI->db->where('clientid IN (SELECT customer_id FROM '.$prefix.'customer_admins WHERE staff_id='.(int)get_staff_user_id().')', null, false);
    if (!staff_can('view', 'invoices')) {
        $parts = [];
        if (staff_can('view_own', 'invoices')) $parts[] = 'addedfrom='.(int)get_staff_user_id();
        if (get_option('allow_staff_view_invoices_assigned')) $parts[] = 'sale_agent='.(int)get_staff_user_id();
        $CI->db->where('('.implode(' OR ', $parts).')', null, false);
    }
    foreach ($CI->db->get()->result_array() as $invoice) {
        $key = (int)$invoice['currency'];
        if (!isset($result[$key])) $result[$key] = ['invoiced'=>'0','paid'=>'0','due'=>'0'];
        $due = get_invoice_total_left_to_pay($invoice['id'], $invoice['total']);
        $result[$key]['invoiced'] = scaf_decimal_add($result[$key]['invoiced'], $invoice['total']);
        $result[$key]['due'] = scaf_decimal_add($result[$key]['due'], $due);
        foreach ($CI->db->select('amount')->where('invoiceid', (int)$invoice['id'])->get($prefix.'invoicepaymentrecords')->result_array() as $payment) { $result[$key]['paid'] = scaf_decimal_add($result[$key]['paid'], $payment['amount']); }
    }
    return $result;
}
function scaf_customer_money_lines($balances, $key)
{
    $lines = [];
    foreach ($balances as $currency => $values) $lines[] = e(app_format_money($values[$key], get_currency($currency)));
    return $lines ? implode('<br>', $lines) : e(_l('scaf_no_visible_financial_data'));
}
function scaf_customer_project_count($id = null, $activeOnly = false)
{
    $CI = &get_instance();
    if ($id !== null && !scaf_customer_allowed($id)) return null;
    if (!staff_can('view', 'projects') && !staff_can('view_own', 'projects')) return null;
    if ($id !== null) $CI->db->where('clientid', (int)$id);
    if ($activeOnly) $CI->db->where('status', 2);
    if (!staff_can('view', 'customers')) $CI->db->where('clientid IN (SELECT customer_id FROM '.db_prefix().'customer_admins WHERE staff_id='.(int)get_staff_user_id().')',null,false);
    if (!staff_can('view', 'projects')) $CI->db->where('id IN (SELECT project_id FROM '.db_prefix().'project_members WHERE staff_id='.(int)get_staff_user_id().')',null,false);
    return (int)$CI->db->count_all_results(db_prefix().'projects');
}

function scaf_customer_current_aging($customerId, $currencyId, $today = null)
{
    if (!scaf_customer_allowed($customerId)) return null;
    if (staff_cant('view', 'invoices') && staff_cant('view', 'payments')) { return null; }
    $CI = &get_instance();
    $CI->load->helper('invoices');
    $today = $today ?: date('Y-m-d');
    $asOf = new DateTimeImmutable($today);
    $rows = $CI->db->select('id,total,duedate')->where('clientid', (int) $customerId)
        ->where('currency', (int) $currencyId)->where_in('status', [1, 3, 4])
        ->where('date <=', $today)->get(db_prefix() . 'invoices')->result_array();
    $buckets = ['current' => '0', '1_30' => '0', '31_60' => '0', '61_plus' => '0'];
    $places = get_decimal_places();
    foreach ($rows as $invoice) {
        $balance = get_invoice_total_left_to_pay($invoice['id'], $invoice['total']);
        if (scaf_decimal_cmp($balance, '0') <= 0) { continue; }
        $days = 0;
        if (!empty($invoice['duedate']) && $invoice['duedate'] !== '0000-00-00') {
            $due = new DateTimeImmutable($invoice['duedate']);
            $days = (int) $due->diff($asOf)->format('%r%a');
        }
        $key = $days <= 0 ? 'current' : ($days <= 30 ? '1_30' : ($days <= 60 ? '31_60' : '61_plus'));
        $buckets[$key] = scaf_decimal_add($buckets[$key], $balance, $places);
    }
    return ['as_of' => $today, 'buckets' => $buckets];
}

function scaf_statement_request_valid($ids, $from, $to)
{
    if (!staff_can('view','invoices') && !staff_can('view','payments')) return false;
    if (!is_array($ids) || !$ids || count($ids)>100) return false;
    foreach ($ids as $id) if (!is_scalar($id) || !ctype_digit((string)$id) || (int)$id<=0 || !scaf_customer_allowed((int)$id)) return false;
    foreach ([$from,$to] as $date) {
        if (!is_string($date)) return false;
        $parsed=DateTimeImmutable::createFromFormat('!Y-m-d',$date);
        if (!$parsed || $parsed->format('Y-m-d')!==$date) return false;
    }
    return $from <= $to;
}

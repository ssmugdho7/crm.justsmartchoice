<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Smart_choice_payment_schedule_model extends App_Model
{
    private $plans;
    private $installments;

    public function __construct()
    {
        parent::__construct();
        $this->plans = db_prefix() . 'sc_payment_plans';
        $this->installments = db_prefix() . 'sc_payment_installments';
    }

    public function get_plan($type, $id)
    {
        return $this->db->where('rel_type', $type)->where('rel_id', (int) $id)->get($this->plans)->row();
    }

    public function save_plan($type, $id, array $input)
    {
        $data = [
            'rel_type'             => $type,
            'rel_id'               => (int) $id,
            'enabled'              => !empty($input['scps_enabled']) ? 1 : 0,
            'mode'                 => in_array(($input['scps_mode'] ?? ''), ['percentage', 'fixed'], true) ? $input['scps_mode'] : 'percentage',
            'installments'         => max(1, min(24, (int) ($input['scps_installments'] ?? 1))),
            'down_payment_percent' => max(0, min(100, (float) ($input['scps_down_payment_percent'] ?? 0))),
            'down_payment_amount'  => max(0, (float) ($input['scps_down_payment_amount'] ?? 0)),
            'discount_reason'      => mb_substr(trim((string) ($input['scps_discount_reason'] ?? '')), 0, 120),
            'discount_note'        => mb_substr(trim((string) ($input['scps_discount_note'] ?? '')), 0, 500),
            'next_invoice_mode'    => in_array(($input['scps_next_invoice_mode'] ?? 'manual'), ['manual','automatic'], true) ? $input['scps_next_invoice_mode'] : 'manual',
            'updated_at'           => date('Y-m-d H:i:s'),
        ];

        $existing = $this->get_plan($type, $id);
        if ($existing) {
            $this->db->where('id', $existing->id)->update($this->plans, $data);
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert($this->plans, $data);
        }

        if ($type === 'invoice') {
            $this->rebuild_installments((int) $id);
        }
        return true;
    }

    public function copy_plan($fromType, $fromId, $toType, $toId)
    {
        $plan = $this->get_plan($fromType, $fromId);
        if (!$plan) return false;
        $this->save_plan($toType, $toId, [
            'scps_enabled'              => $plan->enabled,
            'scps_mode'                 => $plan->mode,
            'scps_installments'         => $plan->installments,
            'scps_down_payment_percent' => $plan->down_payment_percent,
            'scps_down_payment_amount'  => $plan->down_payment_amount,
            'scps_discount_reason'      => $plan->discount_reason,
            'scps_discount_note'        => $plan->discount_note,
            'scps_next_invoice_mode'   => $plan->next_invoice_mode ?? 'manual',
        ]);
        return true;
    }

    public function rebuild_installments($invoiceId)
    {
        $plan = $this->get_plan('invoice', $invoiceId);
        if (!$plan || !$plan->enabled) return;
        $invoice = $this->db->where('id', $invoiceId)->get(db_prefix() . 'invoices')->row();
        if (!$invoice) return;

        $count = max(1, (int) $plan->installments);
        $total = (float) $invoice->total;
        $first = scps_calculate_current_due($plan, $total);
        $remaining = max(0, $total - $first);
        $other = $count > 1 ? round($remaining / ($count - 1), 2) : 0;

        for ($i = 1; $i <= $count; $i++) {
            $amount = $i === 1 ? $first : $other;
            if ($i === $count) {
                $previous = $first + ($other * max(0, $count - 2));
                $amount = round($total - $previous, 2);
            }
            $row = $this->db->where('invoice_id', $invoiceId)->where('sequence_no', $i)->get($this->installments)->row();
            $data = [
                'invoice_id'  => $invoiceId,
                'sequence_no' => $i,
                'amount'      => max(0, $amount),
                'due_date'    => date('Y-m-d', strtotime('+' . (($i - 1) * 30) . ' days')),
                'updated_at'  => date('Y-m-d H:i:s'),
            ];
            if ($row) {
                $this->db->where('id', $row->id)->update($this->installments, $data);
            } else {
                $data['created_at'] = date('Y-m-d H:i:s');
                $this->db->insert($this->installments, $data);
            }
        }
        $this->db->where('invoice_id', $invoiceId)->where('sequence_no >', $count)->delete($this->installments);
    }

    public function sync_payment($paymentId)
    {
        $payment = $this->db->where('id', $paymentId)->get(db_prefix() . 'invoicepaymentrecords')->row();
        if (!$payment) return;
        $remaining = (float) $payment->amount;
        $rows = $this->db->where('invoice_id', $payment->invoiceid)->order_by('sequence_no', 'ASC')->get($this->installments)->result();
        foreach ($rows as $row) {
            if ($remaining <= 0) break;
            $open = max(0, (float) $row->amount - (float) $row->paid_amount);
            $applied = min($remaining, $open);
            if ($applied > 0) {
                $newPaid = (float) $row->paid_amount + $applied;
                $this->db->where('id', $row->id)->update($this->installments, [
                    'paid_amount' => $newPaid,
                    'status'      => $newPaid >= (float) $row->amount ? 'paid' : 'partial',
                    'payment_id'  => $paymentId,
                    'updated_at'  => date('Y-m-d H:i:s'),
                ]);
                $remaining -= $applied;
            }
        }
        $this->maybe_create_next_invoice((int) $payment->invoiceid);
    }

    private function maybe_create_next_invoice($invoiceId)
    {
        $plan = $this->get_plan('invoice', $invoiceId);
        if (!$plan || (int) $plan->enabled !== 1 || ($plan->next_invoice_mode ?? 'manual') !== 'automatic') return;
        $first = $this->db->where('invoice_id', $invoiceId)->where('sequence_no', 1)->get($this->installments)->row();
        if (!$first || $first->status !== 'paid' || !empty($first->next_invoice_id)) return;
        $invoice = $this->db->where('id', $invoiceId)->get(db_prefix() . 'invoices')->row();
        if (!$invoice) return;
        $remainingBalance = max(0, (float) $invoice->total - (float) $first->amount);
        if ($remainingBalance <= 0) return;

        $data = (array) $invoice;
        foreach (['id','hash','sent','viewed','datecreated','last_overdue_reminder','last_due_reminder','cancel_overdue_reminders','recurring','recurring_type','custom_recurring','cycles','total_cycles','is_recurring_from','last_recurring_date'] as $field) unset($data[$field]);
        $data['number'] = (int) get_option('next_invoice_number');
        $data['status'] = 1;
        $data['date'] = date('Y-m-d');
        $data['duedate'] = date('Y-m-d', strtotime('+30 days'));
        $data['subtotal'] = $remainingBalance;
        $data['total'] = $remainingBalance;
        $data['discount_percent'] = 0;
        $data['discount_total'] = 0;
        $data['adjustment'] = 0;
        $data['adminnote'] = trim(($data['adminnote'] ?? '') . "\nAutomatically created remaining balance invoice from " . format_invoice_number($invoiceId));
        $data['datecreated'] = date('Y-m-d H:i:s');
        $data['hash'] = app_generate_hash();
        $this->db->insert(db_prefix() . 'invoices', $data);
        $newId = (int) $this->db->insert_id();
        if (!$newId) return;

        add_new_sales_item_post([
            'description' => 'Remaining Contract Balance',
            'long_description' => 'Remaining balance after down payment on ' . format_invoice_number($invoiceId),
            'qty' => 1,
            'rate' => $remainingBalance,
            'unit' => '',
            'order' => 1,
        ], $newId, 'invoice');
        $this->load->model('invoices_model');
        $this->invoices_model->save_formatted_number($newId);
        $this->invoices_model->increment_next_number();
        update_invoice_status($newId);
        $this->db->where('id', $first->id)->update($this->installments, [
            'next_invoice_id' => $newId,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function dashboard_stats()
    {
        $pending = $this->db->where_in('status', ['pending', 'partial'])->count_all_results($this->installments);
        $due = $this->db->select_sum('amount')->select_sum('paid_amount')->where_in('status', ['pending', 'partial'])->get($this->installments)->row();
        return [
            'pending_count' => $pending,
            'pending_value' => max(0, (float) ($due->amount ?? 0) - (float) ($due->paid_amount ?? 0)),
        ];
    }
}

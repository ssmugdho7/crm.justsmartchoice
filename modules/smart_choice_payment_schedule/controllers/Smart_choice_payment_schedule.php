<?php

defined('BASEPATH') or exit('No direct script access allowed');
class Smart_choice_payment_schedule extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        if (!(is_admin() || staff_can('view_global', 'smart_choice_payment_schedule') || staff_can('view_own', 'smart_choice_payment_schedule'))) {
            access_denied('smart_choice_payment_schedule');
        }
    }

    public function index()
    {
        $table = db_prefix() . 'sc_payment_installments';
        $data['title'] = _l('scps_menu');
        $data['installments'] = $this->db->table_exists($table)
            ? $this->db->order_by('id', 'DESC')->get($table)->result()
            : [];
        $this->load->view('smart_choice_payment_schedule/admin/manage', $data);
    }

    public function create_balance($installmentId)
    {
        if (!(is_admin() || staff_can('create', 'smart_choice_payment_schedule'))) {
            access_denied('smart_choice_payment_schedule');
        }
        $row = $this->db->where('id', (int) $installmentId)->get(db_prefix() . 'sc_payment_installments')->row();
        if (!$row || (int) $row->balance_invoice_id > 0 || (float) $row->balance_amount <= 0) {
            set_alert('warning', _l('scps_balance_not_available'));
            redirect(admin_url('smart_choice_payment_schedule'));
        }
        $sourceTable = $row->source_type === 'estimate' ? 'estimates' : ($row->source_type === 'proposal' ? 'proposals' : 'invoices');
        $source = $this->db->where('id', $row->source_id)->get(db_prefix() . $sourceTable)->row();
        if ($source && $row->source_type === 'proposal') {
            $source = (object) ['id'=>$source->id,'clientid'=>$source->rel_id,'currency'=>$source->currency,'project_id'=>$source->project_id ?? 0];
        }
        $invoiceId = $source ? scps_create_simple_invoice((int)$source->clientid, $source, (float)$row->balance_amount, _l('scps_balance_invoice_item'), 1) : 0;
        if ($invoiceId) {
            $this->db->where('id', $row->id)->update(db_prefix() . 'sc_payment_installments', ['balance_invoice_id' => $invoiceId]);
            set_alert('success', _l('scps_balance_created'));
        } else {
            set_alert('danger', _l('scps_balance_create_failed'));
        }
        redirect(admin_url('smart_choice_payment_schedule'));
    }
}

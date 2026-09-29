<?php defined('BASEPATH') or exit('No direct script access allowed');

class Smart_choice_sales_discussions extends App_Controller
{
    private $allowedTypes = ['invoice', 'estimate'];

    public function add()
    {
        if (!$this->input->post()) { show_404(); }
        $type = strtolower(trim((string)$this->input->post('rel_type')));
        $id = (int)$this->input->post('rel_id');
        $hash = trim((string)$this->input->post('hash'));
        if (!in_array($type,$this->allowedTypes,true) || $id < 1 || $hash === '') { show_404(); }

        $model = $type === 'invoice' ? 'invoices_model' : 'estimates_model';
        $this->load->model($model);
        $doc = $this->{$model}->get($id);
        if (!$doc || !hash_equals((string)$doc->hash,$hash)) { show_404(); }

        $name = trim((string)$this->input->post('author_name'));
        $email = trim((string)$this->input->post('author_email'));
        $message = trim((string)$this->input->post('message'));
        $returnUrl = ($type === 'invoice' ? site_url('invoice/'.$id.'/'.$hash) : site_url('estimate/'.$id.'/'.$hash)) . '?tab=discussion#discussion';

        if ($name === '' || $message === '' || !filter_var($email,FILTER_VALIDATE_EMAIL)) {
            set_alert('danger', _l('sc_discussion_validation'));
            redirect($returnUrl,'refresh');
        }

        $table = db_prefix().'sc_sales_discussions';
        if ($this->db->table_exists($table)) {
            $this->db->insert($table,[
                'rel_type'=>$type,'rel_id'=>$id,
                'author_name'=>mb_substr($name,0,191),
                'author_email'=>mb_substr($email,0,191),
                'message'=>$message,'created_at'=>date('Y-m-d H:i:s'),
                'ip_address'=>$this->input->ip_address(),
            ]);
            set_alert('success',_l('sc_comment_added'));
        }
        redirect($returnUrl,'refresh');
    }

    public function admin_add()
    {
        if (!is_staff_logged_in() || !$this->input->post()) { show_404(); }
        $type = strtolower(trim((string)$this->input->post('rel_type')));
        $id = (int)$this->input->post('rel_id');
        $message = trim((string)$this->input->post('message'));
        if (!in_array($type,$this->allowedTypes,true) || $id < 1 || $message === '') {
            set_alert('danger',_l('sc_discussion_comment_required'));
            redirect($type === 'invoice' ? admin_url('invoices/list_invoices/'.$id) : admin_url('estimates/list_estimates/'.$id));
        }
        $table = db_prefix().'sc_sales_discussions';
        if ($this->db->table_exists($table)) {
            $staffId = get_staff_user_id();
            $this->db->insert($table,[
                'rel_type'=>$type,'rel_id'=>$id,
                'author_name'=>get_staff_full_name($staffId),
                'author_email'=>(string)get_staff_email_by_id($staffId),
                'message'=>$message,'created_at'=>date('Y-m-d H:i:s'),
                'ip_address'=>$this->input->ip_address(),
            ]);
            set_alert('success',_l('sc_comment_added'));
        }
        redirect(($type === 'invoice' ? admin_url('invoices/list_invoices/'.$id) : admin_url('estimates/list_estimates/'.$id)).'#tab_sc_summary_discussion');
    }
}

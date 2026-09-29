<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Smart_choice_bulk extends AdminController
{
    public function delete($feature)
    {
        if (!$this->input->is_ajax_request()) show_404();
        $map = [
            'invoices'   => ['model'=>'invoices_model','method'=>'delete','permission'=>'invoices'],
            'estimates'  => ['model'=>'estimates_model','method'=>'delete','permission'=>'estimates'],
            'proposals'  => ['model'=>'proposals_model','method'=>'delete','permission'=>'proposals'],
            'contracts'  => ['model'=>'contracts_model','method'=>'delete','permission'=>'contracts'],
            'credit_notes'=>['model'=>'credit_notes_model','method'=>'delete','permission'=>'credit_notes'],
            'payments'   => ['model'=>'payments_model','method'=>'delete','permission'=>'payments'],
        ];
        if (!isset($map[$feature]) || staff_cant('delete', $map[$feature]['permission'])) ajax_access_denied();
        $ids = array_values(array_unique(array_filter(array_map('intval', (array)$this->input->post('ids')))));
        if (!$ids) return $this->output->set_content_type('application/json')->set_output(json_encode(['success'=>false,'deleted'=>0]));
        $this->load->model($map[$feature]['model']);
        $model = $this->{$map[$feature]['model']};
        $deleted = 0; $failed=[];
        foreach ($ids as $id) {
            try { if ($model->{$map[$feature]['method']}($id)) $deleted++; else $failed[]=$id; }
            catch (Throwable $e) { log_message('error','Bulk delete '.$feature.' #'.$id.': '.$e->getMessage()); $failed[]=$id; }
        }
        return $this->output->set_content_type('application/json')->set_output(json_encode(['success'=>$deleted>0,'deleted'=>$deleted,'failed'=>$failed]));
    }
}

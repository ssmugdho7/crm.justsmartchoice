<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Smart_choice_bulk extends AdminController
{
    public function delete($feature)
    {
        if (!$this->input->is_ajax_request()) { show_404(); return; }
        if ($this->input->method(true) !== 'POST') { show_error('POST required',405); return; }
        if ($this->input->post('delete_confirmation') !== 'DELETE') {
            show_error('Type DELETE to confirm deletion',422); return;
        }
        $map = [
            'invoices'   => ['model'=>'invoices_model','method'=>'delete','permission'=>'invoices'],
            'estimates'  => ['model'=>'estimates_model','method'=>'delete','permission'=>'estimates'],
            'proposals'  => ['model'=>'proposals_model','method'=>'delete','permission'=>'proposals'],
            'contracts'  => ['model'=>'contracts_model','method'=>'delete','permission'=>'contracts'],
            'credit_notes'=>['model'=>'credit_notes_model','method'=>'delete','permission'=>'credit_notes'],
            'payments'   => ['model'=>'payments_model','method'=>'delete','permission'=>'payments'],
        ];
        if (!isset($map[$feature]) || staff_cant('delete', $map[$feature]['permission'])) { ajax_access_denied(); return; }
        $raw = $this->input->post('ids');
        if (!is_array($raw) || count($raw)>200) { show_error('Select between 1 and 200 records',422); return; }
        $ids=[];
        foreach($raw as $id){
            if(!is_scalar($id) || !preg_match('/^[1-9][0-9]*$/D',(string)$id)){show_error('Invalid record selection',422);return;}
            $ids[]=(int)$id;
        }
        $ids=array_values(array_unique($ids));
        // Delete newest invoices first so native last-invoice restrictions stay effective.
        if($feature==='invoices')rsort($ids,SORT_NUMERIC);
        if (!$ids) return $this->output->set_content_type('application/json')->set_output(json_encode(['success'=>false,'deleted'=>0]));
        $this->load->model($map[$feature]['model']);
        $model = $this->{$map[$feature]['model']};
        // Check every object before changing any selected record.
        foreach($ids as $id){
            $record=$model->get($id);
            if(!$record || (!is_admin() && !staff_can('view',$map[$feature]['permission'])
                && (int)($record->addedfrom ?? $record->added_by ?? 0)!==(int)get_staff_user_id())){
                ajax_access_denied(); return;
            }
        }
        $deleted = 0; $failed=[];
        foreach ($ids as $id) {
            try { if ($model->{$map[$feature]['method']}($id)) $deleted++; else $failed[]=$id; }
            catch (Throwable $e) { log_message('error','Bulk delete '.$feature.' #'.$id.': '.$e->getMessage()); $failed[]=$id; }
        }
        return $this->output->set_content_type('application/json')->set_output(json_encode(['success'=>$deleted>0,'deleted'=>$deleted,'failed'=>$failed]));
    }
}

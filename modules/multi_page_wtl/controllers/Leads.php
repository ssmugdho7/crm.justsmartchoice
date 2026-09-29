<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Leads extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('leads_model');
        $this->load->model('roles_model');
    }

    private function can($capability)
    {
        return is_admin() || has_permission(MPWTL_MODULE_NAME, '', $capability);
    }

    private function get_owned_form($id)
    {
        $form = $this->leads_model->get_form(['id' => $id, 'is_mpwtl' => 1]);
        if (!$form) {
            show_404();
        }
        if (!$this->can('view') && (int)$form->created_by !== (int)get_staff_user_id()) {
            access_denied(_l('multi_page_wtl'));
        }
        return $form;
    }

    public function forms()
    {
        if (!$this->can('view') && !$this->can('view_own')) {
            access_denied(_l('multi_page_wtl'));
        }
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path(MPWTL_MODULE_NAME, 'admin/tables/web_to_lead'));
            return;
        }
        $data['title'] = _l(MPWTL_MODULE_NAME);
        $this->load->view('admin/leads/forms', $data);
    }

    public function form($id = '')
    {
        if ($id === '') {
            if (!$this->can('create')) {
                access_denied(_l('permission_create'));
            }
        } else {
            $form = $this->get_owned_form($id);
            if (!$this->can('edit') && !is_admin()) {
                access_denied(_l('permission_edit'));
            }
        }

        if ($this->input->post()) {
            $data = $this->input->post();
            if ($id === '') {
                $data['is_mpwtl'] = 1;
                $data['created_by'] = get_staff_user_id();
                $data['form_data'] = json_encode([[]]);
                $id = $this->leads_model->add_form($data);
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('web_to_lead_form')));
                    redirect(admin_url(MPWTL_MODULE_NAME . '/leads/form/' . $id));
                }
            } else {
                unset($data['created_by'], $data['is_mpwtl']);
                $success = $this->leads_model->update_form($id, $data);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('web_to_lead_form')));
                }
                redirect(admin_url(MPWTL_MODULE_NAME . '/leads/form/' . $id));
            }
        }

        $data['formData'] = [];
        $custom_fields = get_custom_fields('leads', 'type != "link"');
        $data['cfields'] = format_external_form_custom_fields($custom_fields);
        $data['title'] = _l('web_to_lead');

        if ($id !== '') {
            $data['form'] = $form;
            $data['title'] = $form->name . ' - ' . _l('web_to_lead_form');
            $data['formData'] = !empty($form->form_data) ? $form->form_data : json_encode([[]]);
            $data['media'] = $this->db->where('form_id', $id)->order_by('id', 'DESC')->get(db_prefix().'mpwtl_media')->result();
        } else {
            $data['media'] = [];
        }

        $data['roles'] = $this->roles_model->get();
        $data['sources'] = $this->leads_model->get_source();
        $data['statuses'] = $this->leads_model->get_status();
        $data['members'] = $this->staff_model->get('', ['active' => 1, 'is_not_staff' => 0]);
        $data['languages'] = $this->app->get_available_languages();

        $db_fields = [];
        $fields = ['html_block','name','title','email','phonenumber','lead_value','company','address','city','state','country','zip','description','website'];
        $fields = hooks()->apply_filters('lead_form_available_database_fields', $fields);
        foreach ($fields as $f) {
            $type='text'; $subtype=''; $className='form-control';
            if ($f === 'email') $subtype='email';
            elseif ($f === 'description' || $f === 'address') $type='textarea';
            elseif ($f === 'country') $type='select';
            elseif ($f === 'html_block') { $type='paragraph'; $className=''; $subtype='html_block'; }
            if ($f === 'html_block') $label=_l('lead_html_block');
            elseif ($f === 'name') $label=_l('lead_add_edit_name');
            elseif ($f === 'email') $label=_l('lead_add_edit_email');
            elseif ($f === 'phonenumber') $label=_l('lead_add_edit_phonenumber');
            elseif ($f === 'lead_value') { $label=_l('lead_add_edit_lead_value'); $type='number'; }
            else $label=_l('lead_'.$f);
            $field_array=['subtype'=>$subtype,'type'=>$type,'label'=>$label,'className'=>$className,'name'=>$f];
            if ($f === 'country') {
                $field_array['values']=[['label'=>'','value'=>'','selected'=>false]];
                foreach (get_all_countries() as $country) {
                    $field_array['values'][]=['label'=>$country['short_name'],'value'=>(int)$country['country_id'],'selected'=>(get_option('customer_default_country') == $country['country_id'])];
                }
            }
            if ($f === 'name') $field_array['required']=true;
            $obj=new stdClass(); $obj->label=$label; $obj->name=$f; $obj->fields=[$field_array]; $db_fields[]=$obj;
        }
        $data['db_fields']=$db_fields;
        $data['bodyclass']='web-to-lead-form';
        $this->load->view('admin/leads/formbuilder', $data);
    }

    public function save_form_data()
    {
        if (!$this->can('edit')) { ajax_access_denied(); }
        $data=$this->input->post();
        if (empty($data['id']) || !isset($data['formData'])) { echo json_encode(['success'=>false]); return; }
        $this->get_owned_form((int)$data['id']);
        $decoded=json_decode($data['formData']);
        if (!is_array($decoded)) { echo json_encode(['success'=>false,'message'=>_l('mpwtl_invalid_form_data')]); return; }
        $clean=json_encode($decoded, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        $this->db->where('id',(int)$data['id'])->update(db_prefix().'web_to_lead',['form_data'=>$clean]);
        echo json_encode(['success'=>true,'message'=>_l('updated_successfully', _l('web_to_lead_form'))]);
    }

    public function delete_form($id)
    {
        if (!$this->can('delete')) access_denied(_l('permission_delete'));
        $this->get_owned_form($id);
        $this->delete_media_files_for_form($id);
        $success=$this->leads_model->delete_form($id);
        if ($success) set_alert('success', _l('deleted', _l('web_to_lead_form')));
        redirect(admin_url(MPWTL_MODULE_NAME.'/leads/forms'));
    }

    public function upload_media($form_id)
    {
        if (!$this->can('edit')) access_denied(_l('permission_edit'));
        $this->get_owned_form($form_id);
        if (empty($_FILES['media_file']['name'])) { set_alert('warning', _l('mpwtl_choose_file')); redirect(admin_url(MPWTL_MODULE_NAME.'/leads/form/'.$form_id)); }
        $dir=FCPATH.'uploads/multi_page_wtl/'.$form_id.'/';
        if (!is_dir($dir)) @mkdir($dir, 0755, true);
        $original=$_FILES['media_file']['name'];
        $ext=strtolower(pathinfo($original, PATHINFO_EXTENSION));
        $allowed=['jpg','jpeg','png','gif','webp','svg','mp4','webm','mov','pdf','doc','docx','xls','xlsx','csv','txt','zip'];
        if (!in_array($ext,$allowed,true)) { set_alert('danger',_l('mpwtl_invalid_media_type')); redirect(admin_url(MPWTL_MODULE_NAME.'/leads/form/'.$form_id)); }
        $safe=app_generate_hash().'.'.$ext;
        if (!move_uploaded_file($_FILES['media_file']['tmp_name'],$dir.$safe)) { set_alert('danger',_l('mpwtl_upload_failed')); redirect(admin_url(MPWTL_MODULE_NAME.'/leads/form/'.$form_id)); }
        $mime=function_exists('mime_content_type') ? mime_content_type($dir.$safe) : $_FILES['media_file']['type'];
        $type=strpos($mime,'image/')===0?'image':(strpos($mime,'video/')===0?'video':'file');
        $this->db->insert(db_prefix().'mpwtl_media',['form_id'=>$form_id,'file_name'=>$safe,'original_name'=>$original,'mime_type'=>$mime,'file_type'=>$type,'addedfrom'=>get_staff_user_id(),'dateadded'=>date('Y-m-d H:i:s')]);
        set_alert('success',_l('mpwtl_media_uploaded'));
        redirect(admin_url(MPWTL_MODULE_NAME.'/leads/form/'.$form_id).'#tab_form_media');
    }

    public function delete_media($id)
    {
        if (!$this->can('delete')) access_denied(_l('permission_delete'));
        $m=$this->db->where('id',$id)->get(db_prefix().'mpwtl_media')->row();
        if (!$m) show_404();
        $this->get_owned_form($m->form_id);
        $path=FCPATH.'uploads/multi_page_wtl/'.$m->form_id.'/'.$m->file_name;
        if (is_file($path)) @unlink($path);
        $this->db->where('id',$id)->delete(db_prefix().'mpwtl_media');
        set_alert('success',_l('mpwtl_media_deleted'));
        redirect(admin_url(MPWTL_MODULE_NAME.'/leads/form/'.$m->form_id).'#tab_form_media');
    }

    private function delete_media_files_for_form($id)
    {
        $rows=$this->db->where('form_id',$id)->get(db_prefix().'mpwtl_media')->result();
        foreach($rows as $m){$p=FCPATH.'uploads/multi_page_wtl/'.$id.'/'.$m->file_name;if(is_file($p))@unlink($p);} 
        $this->db->where('form_id',$id)->delete(db_prefix().'mpwtl_media');
    }
}

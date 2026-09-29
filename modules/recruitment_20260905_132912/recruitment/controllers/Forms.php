<?php
defined('BASEPATH') or exit('No direct script access allowed');
/**
 * Forms Controller
 */
class Forms extends ClientsController
{
    public function __construct()
    {
        parent::__construct();
        $portal_language = $this->input->get('lang');
        if (!in_array($portal_language, ['english', 'spanish'], true)) {
            $portal_language = $this->input->cookie('sc_recruitment_language', true);
        }
        if (!in_array($portal_language, ['english', 'spanish'], true)) { $portal_language = 'english'; }
        $this->input->set_cookie([
            'name' => 'sc_recruitment_language',
            'value' => $portal_language,
            'expire' => 2592000,
            'secure' => is_https(),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        $GLOBALS['language'] = $portal_language;
        $this->lang->load('recruitment', $portal_language);
    }
    public function index()
    {
        show_404();
    }

    /**
     * Web to lead form
     * User no need to see anything like LEAD in the url, this is the reason the method is named wtl
     * @param  string $key web to lead form key identifier
     * @return mixed
     */
    public function wtl($rec_campaignid="",$key="")
    {
        $this->load->model('recruitment_model');
        $form = $this->recruitment_model->get_form([
            'form_key' => $key

            ]);

        if (!$form) {
            show_404();
        }

        // Change the locale so the validation loader function can load
        // the proper localization file
        $GLOBALS['locale'] = get_locale_key($form->language);

        $data['form_fields'] = json_decode($form->form_data);
        if (!$data['form_fields']) {
            $data['form_fields'] = [];
        }
        $data['form_fields'] = $this->smartchoice_normalize_application_fields($data['form_fields']);
        if ($this->input->post('key')) {
            $data1 = $this->input->post();
            if ($this->input->post('key') == $key) {

                $regular_fields = [];
                $custom_fields  = [];
                foreach ($data1 as $name => $val) {
                    if (strpos($name, 'form-cf-') !== false) {
                        array_push($custom_fields, [
                            'name'  => $name,
                            'value' => $val,
                        ]);
                    } else {
                        if ($this->db->field_exists($name, db_prefix() . 'rec_candidate')) {
                            if ($name == 'country') {
                                if (!is_numeric($val)) {
                                    if ($val == '') {
                                        $val = 0;
                                    } else {
                                        $this->db->where('iso2', $val);
                                        $this->db->or_where('short_name', $val);
                                        $this->db->or_where('long_name', $val);
                                        $country = $this->db->get(db_prefix() . 'countries')->row();
                                        if ($country) {
                                            $val = $country->country_id;
                                        } else {
                                            $val = 0;
                                        }
                                    }
                                }
                            } elseif ($name == 'address') {
                                $val = trim($val);
                                $val = nl2br($val);
                            }

                            $regular_fields[$name] = $val;
                        }elseif($name == 'rec_campaignid'){
                            $regular_fields['rec_campaign'] = $val;
                        }
                    }
                }

                if(isset($data1['csrf_token_name'])){
                    unset($data1['csrf_token_name']);
                }
                $data1['new_candidate'] = true;
                $ids = $this->recruitment_model->add_candidate_forms($regular_fields, $key);
                if ($ids) {
                    $custom_fields_build['candidate'] = [];
                    foreach ($custom_fields as $cf) {
                        $cf_id                                = strafter($cf['name'], 'form-cf-');
                        $custom_fields_build['candidate'][$cf_id] = $cf['value'];
                    }
                    handle_custom_fields_post($ids, $custom_fields_build);
                        
                    portal_handle_rec_candidate_file_form($ids);
                    handle_rec_candidate_avar_file($ids);
                    $success = true;
                    $message = _l('added_successfully').' '. _l('candidate_profile');

                    $data['form'] = $form;
                    $data['message'] =$form->success_submit_msg;


                    if(is_numeric($rec_campaignid) && $rec_campaignid != 0){

                        echo json_encode([
                            'success' => $success,
                            'message' => $form->success_submit_msg. ' <a href="'.site_url('recruitment/recruitment_portal/job_detail/'.$rec_campaignid).'" class="btn btn-default">'. _l('go_back').'</a>',
                        ]);
                        die;
                    } else{
                       echo json_encode([
                        'success' => $success,
                        'message' => $form->success_submit_msg,
                    ]);
                       die;
                   }
                }
            }
        }
        $data['form'] = $form;
        $data['rec_campaignid'] = $rec_campaignid;
        $this->load->view('forms/recruitment_channel_form', $data);
    }

    /**
     * Web to lead form
     * User no need to see anything like LEAD in the url, this is the reason the method is named eq lead
     * @param  string $hash lead unique identifier
     * @return mixed
     */


    /**
     * Smart Choice branded construction job application.
     * URL: /recruitment/forms/application
     */
    public function application()
    {
        $this->load->model('recruitment_model');
        $data = ['errors' => [], 'success' => false];

        if ($this->input->post()) {
            $post = $this->input->post(null, true);
            $required = [
                'full_name','email','phone','position','experience_years','available_date',
                'employment_type','education_level','legal_authorized','criminal_record',
                'q_heights','q_lift_weight','q_overtime','q_safety_first','q_attention',
                'signature','signature_date'
            ];
            foreach ($required as $field) {
                if (!isset($post[$field]) || trim((string) $post[$field]) === '') {
                    $data['errors'][] = _l('recruitment_application_required_error', _l('recruitment_application_' . $field));
                }
            }
            if (!empty($post['email']) && !filter_var($post['email'], FILTER_VALIDATE_EMAIL)) {
                $data['errors'][] = _l('recruitment_application_invalid_email');
            }

            if (!$data['errors']) {
                $parts = preg_split('/\s+/', trim($post['full_name']), 2);
                $candidate = [
                    'candidate_name' => $parts[0],
                    'last_name' => isset($parts[1]) ? $parts[1] : '',
                    'email' => trim($post['email']),
                    'phonenumber' => trim($post['phone']),
                    'birthday' => !empty($post['dob']) ? $post['dob'] : null,
                    'resident' => trim(($post['address'] ?? '') . ', ' . ($post['city'] ?? '') . ', ' . ($post['state'] ?? '') . ' ' . ($post['zip'] ?? '')),
                    'introduce_yourself' => trim($post['additional_info'] ?? ''),
                    'year_experience' => trim($post['experience_years']),
                    'skill' => isset($post['skills']) && is_array($post['skills']) ? $post['skills'] : [],
                    'status' => 1,
                    'application_position' => trim($post['position']),
                    'available_date' => $post['available_date'],
                    'employment_type' => trim($post['employment_type']),
                    'vehicle_type' => trim($post['vehicle_type'] ?? ''),
                    'education_level' => trim($post['education_level']),
                    'construction_certificate' => trim($post['has_construction_certificate'] ?? ''),
                    'criminal_record_answer' => trim($post['criminal_record']),
                    'legal_authorized_answer' => trim($post['legal_authorized']),
                    'driver_license_answer' => trim($post['has_driver_license'] ?? ''),
                    'screening_answers' => json_encode([
                        'heights'=>$post['q_heights'], 'lift_weight'=>$post['q_lift_weight'],
                        'overtime'=>$post['q_overtime'], 'safety_first'=>$post['q_safety_first'],
                        'attention'=>$post['q_attention'],
                    ]),
                    'reference_name' => trim($post['ref_name'] ?? ''),
                    'reference_phone' => trim($post['ref_phone'] ?? ''),
                    'emergency_name' => trim($post['emergency_name'] ?? ''),
                    'emergency_phone' => trim($post['emergency_phone'] ?? ''),
                    'emergency_relation' => trim($post['emergency_relation'] ?? ''),
                    'digital_signature' => trim($post['signature']),
                    'signature_date' => $post['signature_date'],
                    'application_source' => 'smart_choice_public_application',
                    'signature_ip' => $this->input->ip_address(),
                    'signature_datetime' => date('Y-m-d H:i:s'),
                    'signature_user_agent' => substr((string)$this->input->user_agent(), 0, 1000),
                    'skill_test_token' => bin2hex(random_bytes(24)),
                ];
                if ($this->db->field_exists('candidate_code', db_prefix().'rec_candidate')) {
                    $candidate['candidate_code'] = 'APP-' . date('YmdHis');
                }
                // Keep only columns that exist on the installed database.
                foreach (array_keys($candidate) as $column) {
                    if (!$this->db->field_exists($column, db_prefix().'rec_candidate')) {
                        unset($candidate[$column]);
                    }
                }
                $candidate_id = $this->recruitment_model->add_candidate($candidate);
                if ($candidate_id) {
                    handle_rec_candidate_file($candidate_id);
                    $this->smartchoice_handle_driver_license($candidate_id);
                    recruitment_sync_candidate_resume_to_staff($candidate_id, 0);
                    $this->recruitment_model->smart_choice_notify_staff_new_application($candidate_id, $candidate);
                    hooks()->do_action('recruitment_new_application_submitted', ['candidate_id'=>$candidate_id,'candidate'=>$candidate]);
                    $this->smartchoice_notify_telegram($candidate_id, $candidate);

                    $candidate_link = admin_url('recruitment/candidate/' . $candidate_id);
                    $mail_data = (object) [
                        'candidate_name' => $parts[0], 'last_name' => $parts[1] ?? '',
                        'email' => trim($post['email']), 'candidate_link' => $candidate_link,
                        'position' => trim($post['position']),
                    ];
                    try {
                        mail_template('smart_choice_job_application_received', 'recruitment', $mail_data)->send();
                        mail_template('smart_choice_new_job_application_internal', 'recruitment', $mail_data)->send();
                    } catch (Throwable $e) {
                        log_message('error', 'Recruitment application email: ' . $e->getMessage());
                    }
                    if (get_option('recruitment_tests_required') != '0') { redirect(site_url('recruitment/forms/assessments/'.$candidate_id.'/'.$candidate['skill_test_token'])); }
                    $data['success'] = true;
                } else {
                    $data['errors'][] = _l('recruitment_application_save_failed');
                }
            }
        }

        $data['title'] = _l('recruitment_application_title');
        $this->load->view('forms/smart_choice_job_application', $data);
    }

    public function skills_test($candidate_id = 0, $token = '')
    {
        $this->load->model('recruitment_model'); $table=db_prefix().'rec_candidate';
        $candidate=$this->db->where('id',(int)$candidate_id)->where('skill_test_token',$token)->get($table)->row();
        if(!$candidate){show_404();}
        $questions=$this->smartchoice_skill_questions(); $data=['candidate'=>$candidate,'questions'=>$questions,'submitted'=>false];
        if($this->input->post()){
            $answers=$this->input->post('answers'); if(!is_array($answers))$answers=[]; $correct=0;
            foreach($questions as $k=>$q){if(isset($answers[$k]) && (string)$answers[$k]===(string)$q['correct'])$correct++;}
            $total=count($questions);$score=$total?round(($correct/$total)*100,2):0;
            $row=['candidate_id'=>(int)$candidate_id,'token'=>$token,'answers'=>json_encode($answers),'score'=>$score,'correct_answers'=>$correct,'total_questions'=>$total,'ip_address'=>$this->input->ip_address(),'user_agent'=>substr((string)$this->input->user_agent(),0,1000),'submitted_at'=>date('Y-m-d H:i:s')];
            $this->db->replace(db_prefix().'rec_candidate_skill_tests',$row);
            $this->db->where('id',(int)$candidate_id)->update($table,['skill_test_score'=>$score,'skill_test_completed_at'=>date('Y-m-d H:i:s')]);
            $candidate_link=admin_url('recruitment/candidate/'.(int)$candidate_id);$mail=(object)['candidate_name'=>trim(($candidate->candidate_name??'').' '.($candidate->last_name??'')),'position'=>$candidate->application_position??'','test_score'=>$score.'%','correct_answers'=>$correct,'total_questions'=>$total,'test_ip'=>$this->input->ip_address(),'test_datetime'=>date('Y-m-d H:i:s'),'candidate_link'=>$candidate_link,'recipient'=>get_option('recruitment_skill_test_email')?:'employees@justasmartchoice.com'];
            try{mail_template('recruitment_skill_test_internal','recruitment',$mail)->send();}catch(Throwable $e){log_message('error','Recruitment skill test email: '.$e->getMessage());}
            $data['submitted']=true;$data['score']=$score;$data['correct']=$correct;$data['total']=$total;
        }
        $data['title']=_l('recruitment_skill_test_title');$this->load->view('forms/construction_skill_test',$data);
    }

    public function assessments($candidate_id = 0, $token = '')
    {
        $table=db_prefix().'rec_candidate';
        $candidate=$this->db->where('id',(int)$candidate_id)->where('skill_test_token',$token)->get($table)->row();
        if(!$candidate){show_404();}
        $sets=$this->smartchoice_assessment_sets();
        $enabled=[]; foreach($sets as $type=>$set){if(get_option('recruitment_test_'.$type.'_enabled')!='0'){$enabled[$type]=$set;}}
        $data=['candidate'=>$candidate,'sets'=>$enabled,'submitted'=>false];
        if($this->input->post()){
            $posted=$this->input->post('answers'); if(!is_array($posted))$posted=[]; $results=[];
            foreach($enabled as $type=>$set){$answers=$posted[$type]??[];$points=0;$max=0;foreach($set['questions'] as $qk=>$q){$max+=$q['max'];$points+=(int)($q['scores'][$answers[$qk]??'']??0);} $score=$max?round($points/$max*100,2):0;$summary=$score>=80?_l('recruitment_assessment_strong') : ($score>=60?_l('recruitment_assessment_moderate'):_l('recruitment_assessment_review'));$row=['candidate_id'=>(int)$candidate_id,'assessment_type'=>$type,'answers'=>json_encode($answers),'score'=>$score,'summary'=>$summary,'ip_address'=>$this->input->ip_address(),'user_agent'=>substr((string)$this->input->user_agent(),0,1000),'submitted_at'=>date('Y-m-d H:i:s')];$this->db->replace(db_prefix().'rec_candidate_assessments',$row);$results[$type]=['score'=>$score,'summary'=>$summary];}
            hooks()->do_action('recruitment_assessments_completed',['candidate_id'=>(int)$candidate_id,'results'=>$results]);
            $data['submitted']=true;$data['results']=$results;
        }
        $data['title']=_l('recruitment_assessments_title');$this->load->view('forms/pre_employment_assessments',$data);
    }

    private function smartchoice_assessment_sets()
    {
        $o=['a'=>_l('recruitment_answer_a'),'b'=>_l('recruitment_answer_b'),'c'=>_l('recruitment_answer_c'),'d'=>_l('recruitment_answer_d')];
        return [
          'construction'=>['title'=>_l('recruitment_test_construction'),'questions'=>[
            'q1'=>['text'=>_l('recruitment_assess_construction_q1'),'options'=>$o,'scores'=>['a'=>0,'b'=>3,'c'=>1,'d'=>0],'max'=>3],
            'q2'=>['text'=>_l('recruitment_assess_construction_q2'),'options'=>$o,'scores'=>['a'=>3,'b'=>0,'c'=>1,'d'=>0],'max'=>3],
            'q3'=>['text'=>_l('recruitment_assess_construction_q3'),'options'=>$o,'scores'=>['a'=>3,'b'=>0,'c'=>0,'d'=>1],'max'=>3]]],
          'behavior'=>['title'=>_l('recruitment_test_behavior'),'questions'=>[
            'q1'=>['text'=>_l('recruitment_assess_behavior_q1'),'options'=>$o,'scores'=>['a'=>3,'b'=>2,'c'=>0,'d'=>1],'max'=>3],
            'q2'=>['text'=>_l('recruitment_assess_behavior_q2'),'options'=>$o,'scores'=>['a'=>3,'b'=>1,'c'=>0,'d'=>2],'max'=>3],
            'q3'=>['text'=>_l('recruitment_assess_behavior_q3'),'options'=>$o,'scores'=>['a'=>3,'b'=>1,'c'=>0,'d'=>2],'max'=>3]]],
          'safety'=>['title'=>_l('recruitment_test_safety'),'questions'=>[
            'q1'=>['text'=>_l('recruitment_assess_safety_q1'),'options'=>$o,'scores'=>['a'=>3,'b'=>0,'c'=>1,'d'=>0],'max'=>3],
            'q2'=>['text'=>_l('recruitment_assess_safety_q2'),'options'=>$o,'scores'=>['a'=>3,'b'=>0,'c'=>1,'d'=>0],'max'=>3],
            'q3'=>['text'=>_l('recruitment_assess_safety_q3'),'options'=>$o,'scores'=>['a'=>3,'b'=>0,'c'=>1,'d'=>0],'max'=>3]]],
          'reliability'=>['title'=>_l('recruitment_test_reliability'),'questions'=>[
            'q1'=>['text'=>_l('recruitment_assess_reliability_q1'),'options'=>$o,'scores'=>['a'=>3,'b'=>1,'c'=>0,'d'=>2],'max'=>3],
            'q2'=>['text'=>_l('recruitment_assess_reliability_q2'),'options'=>$o,'scores'=>['a'=>3,'b'=>2,'c'=>0,'d'=>1],'max'=>3],
            'q3'=>['text'=>_l('recruitment_assess_reliability_q3'),'options'=>$o,'scores'=>['a'=>3,'b'=>1,'c'=>0,'d'=>2],'max'=>3]]],
        ];
    }

    private function smartchoice_skill_questions(){return [
      ['key'=>'tape_1','question'=>'recruitment_test_q_tape_1','options'=>['a'=>'recruitment_test_a_15_8','b'=>'recruitment_test_a_17_8','c'=>'recruitment_test_a_2'],'correct'=>'b'],
      ['key'=>'tape_2','question'=>'recruitment_test_q_tape_2','options'=>['a'=>'recruitment_test_a_16','b'=>'recruitment_test_a_12','c'=>'recruitment_test_a_8'],'correct'=>'a'],
      ['key'=>'saw_1','question'=>'recruitment_test_q_saw_1','options'=>['a'=>'recruitment_test_a_guard','b'=>'recruitment_test_a_remove_guard','c'=>'recruitment_test_a_hold'],'correct'=>'a'],
      ['key'=>'cut_1','question'=>'recruitment_test_q_cut_1','options'=>['a'=>'recruitment_test_a_waste','b'=>'recruitment_test_a_line_center','c'=>'recruitment_test_a_guess'],'correct'=>'a'],
      ['key'=>'ppe','question'=>'recruitment_test_q_ppe','options'=>['a'=>'recruitment_test_a_ppe','b'=>'recruitment_test_a_phone','c'=>'recruitment_test_a_none'],'correct'=>'a'],
      ['key'=>'ladder','question'=>'recruitment_test_q_ladder','options'=>['a'=>'recruitment_test_a_ladder','b'=>'recruitment_test_a_top','c'=>'recruitment_test_a_move'],'correct'=>'a'],
      ['key'=>'drill','question'=>'recruitment_test_q_drill','options'=>['a'=>'recruitment_test_a_drill','b'=>'recruitment_test_a_force','c'=>'recruitment_test_a_wrongbit'],'correct'=>'a'],
      ['key'=>'wood','question'=>'recruitment_test_q_wood','options'=>['a'=>'recruitment_test_a_check','b'=>'recruitment_test_a_immediate','c'=>'recruitment_test_a_reverse'],'correct'=>'a']
    ];}

    private function smartchoice_notify_telegram($candidate_id, array $candidate)
    {
        $name = trim(($candidate['candidate_name'] ?? '') . ' ' . ($candidate['last_name'] ?? ''));
        $position = $candidate['application_position'] ?? '';
        $message = 'New job application: ' . $name . ($position ? ' - ' . $position : '') . ' - ' . admin_url('recruitment/candidate/' . (int)$candidate_id);
        foreach (['send_telegram_notification','telegram_send_message','send_telegram_message'] as $fn) {
            if (function_exists($fn)) {
                try { $fn($message); return true; } catch (Throwable $e) { log_message('error', 'Recruitment Telegram notification: ' . $e->getMessage()); }
            }
        }
        hooks()->do_action('telegram_notification', ['message'=>$message,'module'=>'recruitment','candidate_id'=>(int)$candidate_id]);
        return false;
    }

    private function smartchoice_handle_driver_license($candidate_id)
    {
        if (!isset($_FILES['driver_license']) || empty($_FILES['driver_license']['name']) || (int) $_FILES['driver_license']['error'] !== UPLOAD_ERR_OK) {
            return false;
        }
        $allowed = ['pdf','jpg','jpeg','png'];
        $extension = strtolower(pathinfo($_FILES['driver_license']['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $allowed, true)) {
            return false;
        }
        $path = RECRUITMENT_MODULE_UPLOAD_FOLDER . '/candidate/driver_licenses/' . (int) $candidate_id . '/';
        _maybe_create_upload_path($path);
        $filename = unique_filename($path, $_FILES['driver_license']['name']);
        if (!move_uploaded_file($_FILES['driver_license']['tmp_name'], $path . $filename)) {
            return false;
        }
        $this->misc_model->add_attachment_to_database($candidate_id, 'rec_candidate_driver_license', [[
            'file_name'=>$filename, 'filetype'=>$_FILES['driver_license']['type'],
        ]]);
        return true;
    }

    public function l($hash)
    {
        if (get_option('gdpr_enable_lead_public_form') == '0') {
            show_404();
        }
        $this->load->model('leads_model');
        $this->load->model('gdpr_model');
        $lead = $this->leads_model->get('', ['hash' => $hash]);

        if (!$lead || count($lead) > 1) {
            show_404();
        }

        $lead = array_to_object($lead[0]);
        load_lead_language($lead->id);

        if ($this->input->post('update')) {
            $data = $this->input->post();
            unset($data['update']);
            $this->leads_model->update($data, $lead->id);
            redirect($_SERVER['HTTP_REFERER']);
        } elseif ($this->input->post('export') && get_option('gdpr_data_portability_leads') == '1') {
            $this->load->library('gdpr/gdpr_lead');
            $this->gdpr_lead->export($lead->id);
        } elseif ($this->input->post('removal_request')) {
            $success = $this->gdpr_model->add_removal_request([
                'description'  => nl2br($this->input->post('removal_description')),
                'request_from' => $lead->name,
                'lead_id'      => $lead->id,
            ]);
            if ($success) {
                send_gdpr_email_template('gdpr_removal_request_by_lead', $lead->id);
                set_alert('success', _l('data_removal_request_sent'));
            }
            redirect($_SERVER['HTTP_REFERER']);
        }

        $lead->attachments = $this->leads_model->get_lead_attachments($lead->id);
        $this->disableNavigation();
        $this->disableSubMenu();
        $data['title'] = $lead->name;
        $data['lead']  = $lead;
        $this->view('forms/lead');
        $this->data($data);
        $this->layout(true);
    }

    /**
     * ticket
     * @return view
     */
    public function ticket()
    {
        $form            = new stdClass();
        $form->language  = get_option('active_language');
        $form->recaptcha = 1;

        $this->lang->load($form->language . '_lang', $form->language);
        if (file_exists(APPPATH . 'language/' . $form->language . '/custom_lang.php')) {
            $this->lang->load('custom_lang', $form->language);
        }

        $form->success_submit_msg = _l('success_submit_msg');

        $form = hooks()->apply_filters('ticket_form_settings', $form);

        if ($this->input->post() && $this->input->is_ajax_request()) {
            $post_data = $this->input->post();

            $required = ['subject', 'department', 'email', 'name', 'message', 'priority'];

            if (is_gdpr() && get_option('gdpr_enable_terms_and_conditions_ticket_form') == 1) {
                $required[] = 'accept_terms_and_conditions';
            }

            foreach ($required as $field) {
                if (!isset($post_data[$field]) || isset($post_data[$field]) && empty($post_data[$field])) {
                    $this->output->set_status_header(422);
                    die;
                }
            }

            if (get_option('recaptcha_secret_key') != '' && get_option('recaptcha_site_key') != '' && $form->recaptcha == 1) {
                if (!do_recaptcha_validation($post_data['g-recaptcha-response'])) {
                    echo json_encode([
                            'success' => false,
                            'message' => _l('recaptcha_error'),
                            ]);
                    die;
                }
            }

            $post_data = [
                    'email'      => $post_data['email'],
                    'name'       => $post_data['name'],
                    'subject'    => $post_data['subject'],
                    'department' => $post_data['department'],
                    'priority'   => $post_data['priority'],
                    'service'    => isset($post_data['service']) && is_numeric($post_data['service'])
                    ? $post_data['service']
                    : null,
                    'custom_fields' => isset($post_data['custom_fields']) && is_array($post_data['custom_fields'])
                    ? $post_data['custom_fields']
                    : [],
                    'message' => $post_data['message'],
            ];

            $success = false;

            $this->db->where('email', $post_data['email']);
            $result = $this->db->get(db_prefix() . 'contacts')->row();

            if ($result) {
                $post_data['userid']    = $result->userid;
                $post_data['contactid'] = $result->id;
                unset($post_data['email']);
                unset($post_data['name']);
            }

            $this->load->model('tickets_model');

            $post_data = hooks()->apply_filters('ticket_external_form_insert_data', $post_data);
            $ticket_id = $this->tickets_model->add($post_data);

            if ($ticket_id) {
                $success = true;
            }

            if ($success == true) {
                hooks()->do_action('ticket_form_submitted', [
                        'ticket_id' => $ticket_id,
                     ]);
            }

            echo json_encode([
                    'success' => $success,
                    'message' => $form->success_submit_msg,
                    ]);

            die;
        }

        $this->load->model('tickets_model');
        $this->load->model('departments_model');
        $data['departments'] = $this->departments_model->get();
        $data['priorities']  = $this->tickets_model->get_priority();

        $data['priorities']['callback_translate'] = 'ticket_priority_translate';
        $data['services']                         = $this->tickets_model->get_service();

        $data['form'] = $form;
        $this->load->view('forms/ticket', $data);
    }

        /**
     * add candidate form recruitment channel
     * @param redirect
     */
    public function add_candidate_form_recruitment_channel($form_key) {
        $data = $this->input->post();
        if ($data) {
            $ids = $this->recruitment_model->add_candidate_forms($data, $form_key);
            if ($ids) {
                handle_rec_candidate_file_form($ids);
                handle_rec_candidate_avar_file($ids);
                $success = true;
                $message = _l('added_successfully', _l('candidate_profile'));
                set_alert('success', $message);
                redirect(site_url('recruitment/forms/wtl/' . $form_key));
            }
        }
    }



    private function smartchoice_default_application_fields()
    {
        return json_decode('[{"type":"header","subtype":"h3","label":"Job Application"},{"type":"paragraph","label":"Complete the information below and upload your resume if available."},{"type":"text","required":true,"label":"First Name","className":"form-control","name":"candidate_name","subtype":"text"},{"type":"text","required":true,"label":"Last Name","className":"form-control","name":"last_name","subtype":"text"},{"type":"text","required":true,"label":"Email","className":"form-control","name":"email","subtype":"email"},{"type":"text","required":true,"label":"Phone Number","className":"form-control","name":"phonenumber","subtype":"text"},{"type":"text","label":"Position Applied For","className":"form-control","name":"position_name","subtype":"text"},{"type":"textarea","label":"Construction Skills / Experience","className":"form-control","name":"experience","subtype":"textarea"},{"type":"textarea","label":"Message","className":"form-control","name":"introduction","subtype":"textarea"}]');
    }

    private function smartchoice_normalize_application_fields($fields)
    {
        if (!is_array($fields) || count($fields) === 0) {
            return $this->smartchoice_default_application_fields();
        }
        return $fields;
    }

}

<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Sales_center_portal extends App_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('sales_center/sales_center_model');
        $this->load->helper('sales_center/sales_center');
    }

    public function register()
    {
        if ($this->input->post()) {
            if (!$this->validate_portal_security()) {
                set_alert('danger', 'Security verification failed. Please refresh the page and try again.');
                redirect(sales_center_portal_register_url() . '?error=security');
            }
            $data = $this->input->post();
            if (!isset($_POST['custom_fields']) || !is_array($_POST['custom_fields'])) {
                $_POST['custom_fields'] = [];
            }
            unset($data['custom_fields'], $data['g-recaptcha-response'], $data['sales_center_website']);
            $data['status'] = 'pending';
            $data['portal_enabled'] = 1;
            $data['portal_token'] = bin2hex(random_bytes(24));

            $tempImage = $this->handle_profile_image_upload(0);
            if ($tempImage !== '') {
                $data['profile_image'] = $tempImage;
            }

            $existing = $this->sales_center_model->find_existing_portal_salesperson($data);
            if ($existing) {
                $token = $this->sales_center_model->ensure_portal_token((int) $existing->id);
                $this->sales_center_model->update_from_portal($token, $data);
                $profileImage = $this->handle_profile_image_upload((int) $existing->id);
                if ($profileImage !== '') {
                    $this->sales_center_model->update(['profile_image' => $profileImage], (int) $existing->id);
                }
                $uploaded = $this->handle_portal_files((int) $existing->id);
                $this->notify_staff(
                    'Existing Salesperson Portal Profile Updated',
                    'An existing salesperson profile was updated from the portal: ' . ($data['company'] ?? $existing->company),
                    'sales_center/view/' . (int) $existing->id
                );
                redirect(sales_center_portal_success_url($token));
            }

            $id = $this->sales_center_model->add($data);

            if ($id) {
                $this->handle_portal_files($id);
                $this->notify_staff(
                    'New Salesperson Portal Registration',
                    'A new salesperson registered through the portal: ' . ($data['company'] ?? 'Unknown Company'),
                    'sales_center/view/' . $id
                );
                redirect(sales_center_portal_success_url($data['portal_token']));
            }

            redirect(sales_center_portal_register_url() . '?error=save_failed');
        }

        $data['title'] = 'Salesperson Registration Portal';
        $data['token'] = '';
        $data['salesperson'] = null;
        $data['files'] = [];
        $data['is_register'] = true;
        $this->load->view('sales_center/portal/profile', $data);
    }

    public function profile($token = '')
    {
        $salesperson = $this->sales_center_model->get_by_portal_token($token);
        if (!$salesperson) {
            show_404();
        }

        if ($this->input->get('saved')) {
            $data['title'] = 'Submission Received';
            $data['token'] = $token;
            $data['salesperson'] = $salesperson;
            $this->load->view('sales_center/portal/success', $data);
            return;
        }

        if ($this->input->post()) {
            if (!$this->validate_portal_security()) {
                set_alert('danger', 'Security verification failed. Please refresh the page and try again.');
                redirect(sales_center_portal_profile_url($token) . '?error=security');
            }
            $data = $this->input->post();
            if (!isset($_POST['custom_fields']) || !is_array($_POST['custom_fields'])) {
                $_POST['custom_fields'] = [];
            }
            unset($data['custom_fields'], $data['g-recaptcha-response'], $data['sales_center_website']);
            $profileImage = $this->handle_profile_image_upload((int) $salesperson->id);
            if ($profileImage !== '') {
                $data['profile_image'] = $profileImage;
            }
            $id = $this->sales_center_model->update_from_portal($token, $data);
            if ($id) {
                $uploaded = $this->handle_portal_files($id);
                $this->notify_staff(
                    'Salesperson Portal Profile Updated',
                    'A salesperson updated their portal profile: ' . ($data['company'] ?? $salesperson->company),
                    'sales_center/view/' . $id
                );
                if ($uploaded > 0) {
                    $this->notify_staff(
                        'Salesperson Uploaded New Documents',
                        'A salesperson uploaded ' . $uploaded . ' new document(s): ' . ($data['company'] ?? $salesperson->company),
                        'sales_center/view/' . $id
                    );
                }
                redirect(sales_center_portal_success_url($token));
            }
            redirect(sales_center_portal_profile_url($token) . '?error=save_failed');
        }

        $data['title'] = 'Salesperson Portal';
        $data['token'] = $token;
        $data['salesperson'] = $salesperson;
        $data['files'] = $this->sales_center_model->get_files($salesperson->id, 'salesperson');
        $data['is_register'] = false;
        $this->load->view('sales_center/portal/profile', $data);
    }


    public function success($token = '')
    {
        $token = $token !== '' ? $token : (string) $this->input->get('token');
        if ($token !== '') {
            $salesperson = $this->sales_center_model->get_by_portal_token($token);
            if ($salesperson) {
                redirect(sales_center_portal_profile_url($token) . '?saved=1');
            }
        }

        $data['title'] = 'Submission Received';
        $data['token'] = $token;
        $data['salesperson'] = null;
        $this->load->view('sales_center/portal/success', $data);
    }

    private function handle_profile_image_upload($id)
    {
        if (empty($_FILES['profile_image_file']['name'])) {
            return '';
        }
        $path = SALES_CENTER_UPLOAD_FOLDER . 'profiles/';
        if (!is_dir($path)) {
            @mkdir($path, 0755, true);
        }
        $original = $_FILES['profile_image_file']['name'];
        $ext = pathinfo($original, PATHINFO_EXTENSION);
        $fileName = 'salesperson-profile-' . (int) $id . '-' . date('YmdHis') . '-' . mt_rand(1000, 9999) . '.' . strtolower($ext ?: 'png');
        $fileName = preg_replace('/[^A-Za-z0-9._-]/', '_', $fileName);
        if (move_uploaded_file($_FILES['profile_image_file']['tmp_name'], $path . $fileName)) {
            return 'uploads/sales_center/profiles/' . $fileName;
        }
        return '';
    }

    private function handle_portal_files($id)
    {
        if (empty($_FILES['file']['name'])) {
            return 0;
        }

        $path = SALES_CENTER_UPLOAD_FOLDER . 'salesperson/' . (int) $id . '/';
        if (!is_dir($path)) {
            @mkdir($path, 0755, true);
        }

        $uploaded = 0;

        if (is_array($_FILES['file']['name'])) {
            foreach ($_FILES['file']['name'] as $index => $original) {
                if ($original === '') {
                    continue;
                }

                $tmpName = $_FILES['file']['tmp_name'][$index] ?? '';
                if ($tmpName === '' || !is_uploaded_file($tmpName)) {
                    continue;
                }

                $fileName = time() . '_' . $index . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $original);
                if (move_uploaded_file($tmpName, $path . $fileName)) {
                    $this->sales_center_model->add_file($id, 'salesperson', [
                        'file_name' => $fileName,
                        'original_file_name' => $original,
                        'filetype' => $_FILES['file']['type'][$index] ?? '',
                        'visible_to_customer' => 0,
                    ]);
                    $uploaded++;
                }
            }
        } else {
            $original = (string) $_FILES['file']['name'];
            $tmpName  = (string) ($_FILES['file']['tmp_name'] ?? '');
            if ($original !== '' && $tmpName !== '' && is_uploaded_file($tmpName)) {
                $fileName = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $original);
                if (move_uploaded_file($tmpName, $path . $fileName)) {
                    $this->sales_center_model->add_file($id, 'salesperson', [
                        'file_name' => $fileName,
                        'original_file_name' => $original,
                        'filetype' => $_FILES['file']['type'] ?? '',
                        'visible_to_customer' => 0,
                    ]);
                    $uploaded++;
                }
            }
        }

        return $uploaded;
    }

    private function notify_staff($subject, $description, $link = '')
    {
        $staff = $this->db->select('staffid,email,firstname,lastname')
            ->where('active', 1)
            ->get(db_prefix() . 'staff')
            ->result_array();

        foreach ($staff as $member) {
            if (function_exists('add_notification')) {
                add_notification([
                    'description' => $description,
                    'touserid' => (int) $member['staffid'],
                    'fromcompany' => 1,
                    'link' => $link,
                ]);
            }

            if (function_exists('pusher_trigger_notification')) {
                pusher_trigger_notification([(int) $member['staffid']]);
            }

            if (!empty($member['email']) && function_exists('sales_center_send_basic_email')) {
                sales_center_send_basic_email($member['email'], $subject, '<p>' . html_escape($description) . '</p>');
            }
        }
    }


    private function validate_portal_security()
    {
        if ($this->input->post('sales_center_website')) {
            return false;
        }

        if (function_exists('show_recaptcha') && show_recaptcha()) {
            $captcha = $this->input->post('g-recaptcha-response');
            if (function_exists('do_recaptcha_validation') && !do_recaptcha_validation($captcha)) {
                return false;
            }
        }

        return true;
    }
}

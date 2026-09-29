<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Quick_share extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('quickshare_model');
        $this->load->model('emails_model');
    }

    public function index()
    {
        if (!has_permission('quick_share', '', 'view')) {
            access_denied('quick_share');
        }
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('quick_share', 'table'));
        }

        $data['title'] = _l('quick_share');
        $this->load->view('manage', $data);
    }

    public function manage_downloads()
    {
        if (!has_permission('quick_share', '', 'view_downloads')) {
            access_denied('quick_share');
        }
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('quick_share', 'downloads_table'));
        }

        $data['title'] = _l('quick_share').' - '. _l('qs_permission_manage_downloads');
        $this->load->view('manage_downloads', $data);
    }

    public function settings()
    {
        if (!is_admin()) {
            access_denied('quick_share');
        }

        if ($this->input->post()) {
            if (!is_admin()) {
                access_denied('settings');
            }
            $this->load->model('payment_modes_model');
            $this->load->model('settings_model');

            $logo_uploaded = (handle_company_logo_upload() ? true : false);
            $favicon_uploaded = (handle_favicon_upload() ? true : false);
            $signatureUploaded = (handle_company_signature_upload() ? true : false);

            $post_data = $this->input->post();
            $tmpData = $this->input->post(null, false);

            if (isset($post_data['settings']['email_header'])) {
                $post_data['settings']['email_header'] = $tmpData['settings']['email_header'];
            }

            if (isset($post_data['settings']['email_footer'])) {
                $post_data['settings']['email_footer'] = $tmpData['settings']['email_footer'];
            }

            if (isset($post_data['settings']['email_signature'])) {
                $post_data['settings']['email_signature'] = $tmpData['settings']['email_signature'];
            }

            if (isset($post_data['settings']['smtp_password'])) {
                $post_data['settings']['smtp_password'] = $tmpData['settings']['smtp_password'];
            }

            $success = $this->settings_model->update($post_data);

            if ($success > 0) {
                set_alert('success', _l('settings_updated'));
            }

            if ($logo_uploaded || $favicon_uploaded) {
                set_debug_alert(_l('logo_favicon_changed_notice'));
            }

            redirect(admin_url('quick_share/settings'), 'refresh');
        }

        $data['title'] = _l('quick_share') . ' - ' . _l('qs_menu_settings');
        $this->load->view('settings', $data);
    }

    public function uploadFile($fileId = '')
    {

        if (!has_permission('quick_share', '', 'create')) {
            access_denied('quick_share');
        }

        if ($this->input->post()) {

            $postData = $this->input->post();

            if (!isset($postData['settings']['share_link_type'], $postData['settings']['enable_self_destruct'])) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Please check share file and self destruct options',
                ]);
                die;
            }

            if (isset($postData['password']) && !empty($postData['password'])) {
                $postData['password'] = password_hash($postData['password'], PASSWORD_DEFAULT);
            }

            $postData['auto_destroy'] = $postData['settings']['enable_self_destruct'];
            $postData['share_type'] = $postData['settings']['share_link_type'];
            unset($postData['settings']);

            $postData['file_key'] = app_generate_hash();
            $postData['user_id'] = get_staff_user_id();
            $postData['created_at'] = date('Y-m-d H:i:s');
            $postData['ip'] = $this->input->ip_address();

            if (get_option('qs_storage_engine') == 's3') {
                $postData['destination'] = 1;
            }

            $id = $this->quickshare_model->add($postData);

            if ($id) {
                set_alert('success', _l('added_successfully', _l('file')));
                echo json_encode([
                    'url' => admin_url('quick_share/upload/'),
                    'uploadid' => $id,
                ]);
                die;
            }
            echo json_encode([
                'url' => admin_url('quick_share/upload'),
            ]);
            die;
        }
    }

    public function queue_uploaded_file($fileId)
    {
        if (isset($_FILES['file']) && _perfex_upload_error($_FILES['file']['error'])) {
            header('HTTP/1.0 400 Bad error');
            echo _perfex_upload_error($_FILES['file']['error']);
            die;
        }

        $getFileData = $this->quickshare_model->getFileBasedOnId($fileId);

        $path = FCPATH . 'modules/quick_share/uploads/' . $fileId . '/';

        if (isset($_FILES['file']['name'])) {
            // Get the temp file path
            $tmpFilePath = $_FILES['file']['tmp_name'];
            // Make sure we have a filepath
            if (!empty($tmpFilePath) && $tmpFilePath != '') {
                _maybe_create_upload_path($path);

                if ($getFileData->destination == 1) {
                    $filename = $_FILES['file']['name'];
                    $filenameWithoutExtension = pathinfo($filename, PATHINFO_FILENAME) .'-'.$fileId;
                    $extension = pathinfo($filename, PATHINFO_EXTENSION);

                    $filename = $filenameWithoutExtension .'.'. $extension;
                } else {
                    $filename = $_FILES['file']['name'];
                    $newFilePath = $path . $filename;
                }

                if ($_FILES['file']['size'] > get_option('qs_max_upload_size')) {
                    set_alert('danger', _l('qs_file_maximum_size'));
                } elseif (!qs_upload_extension_allowed($filename)){
                    set_alert('danger', _l('qs_settings_allowed_file_extensions_err'));
                } else {

                    if ($getFileData->destination == 1){

                        $s3 = new Aws\S3\S3Client([
                            'region'  => get_option('qs_aws_region'),
                            'version' => 'latest',
                            'credentials' => [
                                'key'    => get_option('qs_aws_key'),
                                'secret' => get_option('qs_aws_secret'),
                            ]
                        ]);

                        try {
                            $s3->putObject([
                                'Bucket' => get_option('qs_aws_bucket'),
                                'Key'    => $filename,
                                'SourceFile' => $tmpFilePath
                            ]);

                            $attachment = [];
                            $attachment[] = [
                                'file_path' => $filename,
                                'file_size' => $_FILES['file']['size'],
                            ];

                            $this->quickshare_model->update_file_content($fileId, $attachment[0]);

                        } catch (Aws\S3\Exception\S3Exception $e) {
                            set_alert('danger', $e->getMessage());
                        }

                    }else {
                        // Upload the file into the temp dir
                        if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                            $attachment = [];
                            $attachment[] = [
                                'file_path' => $filename,
                                'file_size' => $_FILES['file']['size'],
                            ];

                            $this->quickshare_model->update_file_content($fileId, $attachment[0]);
                        }
                    }
                }
            }
        }

        //todo send email to users if it is meant like that
        $getFileData = $this->quickshare_model->getFileBasedOnId($fileId);

        if (!is_null($getFileData)) {

            if (!is_null($getFileData->send_emails_to) && !empty($getFileData->send_emails_to)) {

                $listEmail = explode(',', $getFileData->send_emails_to);

                foreach ($listEmail as $email) {
                    $this->emails_model->send_simple_email(
                        $email,
                        'You have received some files !',
                        '
Dear ' . $email . ',
<br>
You have received some file from ' . get_option('companyname') . ' with a total size of ' . $getFileData->file_size . '.
<br>
<b>File:</b><br>
' . $getFileData->file_path . '
<br>
<b>Message:</b><br>
' . $getFileData->file_message . '
<br>
Best regards,<br>
' . get_option('companyname') . '

            '
                    );
                }

            }

        }

        echo json_encode([
            'url' => admin_url('quick_share'),
        ]);
    }

    public function upload()
    {
        if (!has_permission('quick_share', '', 'create')) {
            access_denied('quick_share');
        }

        $data['title'] = _l('quick_share_upload_title');
        $this->load->view('upload', $data);
    }

    public function delete_file($file_id)
    {
        if (!has_permission('quick_share', '', 'delete')) {
            access_denied('quick_share');
        }
        if (!$file_id) {
            redirect(admin_url('quick_share'));
        }

        $getFileData = $this->quickshare_model->getFileBasedOnId($file_id);

        if ($getFileData->destination == 1) {

            $s3 = new Aws\S3\S3Client([
                'region'  => get_option('qs_aws_region'),
                'version' => 'latest',
                'credentials' => [
                    'key'    => get_option('qs_aws_key'),
                    'secret' => get_option('qs_aws_secret'),
                ]
            ]);

            try {
                $s3->deleteObject(
                    [
                        'Bucket' => get_option('qs_aws_bucket'),
                        'Key' => $getFileData->file_path
                    ]
                );
            } catch (Aws\S3\Exception\S3Exception $e) {
                set_alert('danger', $e->getMessage());
            }

        }

        $response = $this->quickshare_model->delete($file_id);

        if ($response == true) {
            set_alert('success', _l('deleted', _l('quick_share')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('quick_share')));
        }

        redirect(admin_url('quick_share'));
    }

}

<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Download extends App_Controller
{
    public function __construct()
    {
        hooks()->do_action('after_clients_area_init', $this);

        parent::__construct();

        $this->load->model('quickshare_model');
        $this->load->model('emails_model');
        $this->load->model('staff_model');
    }

    public function index()
    {
        show_404();
    }

    public function file($file_key = '')
    {

        // Sanitize the file_key value
        $file_key = stripslashes($file_key);
        $file_key = htmlspecialchars($file_key, ENT_QUOTES, 'UTF-8');

        $data['title']            = _l('quickshare');
        $data['password_verified']            = '0';
        $data['is_file_destroyed'] = '0';

        $getFileData = $this->quickshare_model->getFileBasedOnKey($file_key);

        if (is_null($getFileData)) {
            die('Invalid file');
        }

        if ($getFileData->auto_destroy == 1) {
            $totalFileDownloads = $this->quickshare_model->check_downloads_of_file($getFileData->id);

            if ($totalFileDownloads > 0) {
                $data['is_file_destroyed'] = '1';
            }
        }

        if (!empty($getFileData->password)) {
            $data['password_verified'] = '0';
        } else {
            $data['password_verified'] = '1';
        }

        if ($this->input->get('password')) {
            $password = stripslashes($this->input->get('password'));
            $password = htmlspecialchars($this->input->get('password'), ENT_QUOTES, 'UTF-8');

            if (password_verify($password, $getFileData->password)) {
                $data['password_verified'] = '1';
            } else {
                $data['password_verified'] = '0';
            }
        }

        $data['file_data'] = $getFileData;
        $this->load->view('download_page/download', $data);
    }

    public function downloadFile($fileKey)
    {

        $getFileData = $this->quickshare_model->getFileBasedOnKey($fileKey);

        if (is_null($getFileData)) {
            die('Invalid file');
        }

        if (!empty($getFileData->password)) {

            if (isset($_GET['password']) && !empty($_GET['password'])) {
                $password = stripslashes($_GET['password']);
                $password = htmlspecialchars($_GET['password'], ENT_QUOTES, 'UTF-8');

                if ($password == '') {
                    redirect(base_url('quick_share/download/file/'.$getFileData->file_key));
                }

                if (!password_verify($password, $getFileData->password)) {
                    $data['password_verified'] = '1';
                    set_alert('danger', 'Invalid Password');
                    redirect(base_url('quick_share/download/file/'.$getFileData->file_key));
                }
            } else {
                set_alert('danger', 'Invalid Password');
                redirect(base_url('quick_share/download/file/'.$getFileData->file_key));
            }

        }

        $data = [
            'download_id' => $getFileData->id,
            'ip' => $this->input->ip_address(),
            'created_at' => date('Y-m-d H:i:s')
        ];

        $this->quickshare_model->add_download($data);

        if ($getFileData->auto_destroy == 1) {
            $this->quickshare_model->update_file_content($getFileData->id, ['status'=>1]);
        }

        if ($getFileData->destination != 1) {

            get_instance()->load->helper('download');
            force_download(FCPATH . 'modules/quick_share/uploads/' . $getFileData->id . '/' . $getFileData->file_path, null);

        } else {
            $s3 = new Aws\S3\S3Client([
                'region'  => get_option('qs_aws_region'),
                'version' => 'latest',
                'credentials' => [
                    'key'    => get_option('qs_aws_key'),
                    'secret' => get_option('qs_aws_secret'),
                ]
            ]);

            $object = $s3->getObject(array(
                'Bucket' => get_option('qs_aws_bucket'),
                'Key'    => $getFileData->file_path
            ));

            header('Content-Description: File Transfer');
            header('Content-Type: ' . $object->get('ContentType'));
            header('Content-Disposition: attachment; filename='. $getFileData->file_path);
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');

            echo $object['Body']->getContents();
        }

        $staffData = $this->staff_model->get($getFileData->user_id);
        $this->emails_model->send_simple_email(
            $staffData->email,
            'Someone has downloaded your items !',
            '
            Dear,
<br>
A User has downloaded your file(s).
<br>
<b>File downloaded:</b><br>
'.$getFileData->file_path.'
<br>
Best regards,<br>
'.get_option('companyname').'
            '
        );

    }

}

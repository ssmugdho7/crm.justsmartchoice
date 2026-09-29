<?php

defined('BASEPATH') or exit('No direct script access allowed');


class Projectspot extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('projectspot_model');
    }

    public function index()
    {
        show_404();
    }

    public function projects()
    {

        if (!has_permission('projectspot', '', 'view')) {
            access_denied('projectspot');
        }

        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('projectspot', 'tables/projects'));
        }

        $data['title'] = _l('projectspot') . ' - ' . _l('projects');
        $this->load->view('projects', $data);
    }

    public function create_project($project_id = '')
    {
        if (!has_permission('projectspot', '', 'create')) {
            access_denied('projectspot');
        }

        if ($this->input->post() && $project_id === '') {

            $id = $this->projectspot_model->addProjectGallery($this->input->post() + ['created_at' => date('Y-m-d H:i:s')]);

            if ($id) {
                set_alert('success', _l('added_successfully', _l('projectspot_project')));
                echo json_encode([
                    'project_id' => $id,
                    'url' => admin_url('projectspot/create_project/' . $id)
                ]);
                die;
            }
            echo json_encode([
                'url' => admin_url('projectspot/create_project'),
            ]);
            die;

        } elseif ($this->input->post() && $project_id !== '') {

            $response = $this->projectspot_model->updateProjectGallery($project_id, $this->input->post());

            if ($response) {
                set_alert('success', _l('updated_successfully', _l('projectspot_project')));
                echo json_encode([
                    'project_id' => $project_id,
                    'url' => admin_url('projectspot/create_project/' . $project_id)
                ]);
                die;
            }
            echo json_encode([
                'project_id' => $project_id,
                'url' => admin_url('projectspot/create_project/' . $project_id),
            ]);
            die;
        }

        $data['title'] = _l('projectspot') . ' - ' . _l('projects');
        if ($project_id) {
            $data['project_data'] = $this->projectspot_model->getSingleProjectGalleryList($project_id);
            $data['project_images'] = $this->projectspot_model->getProjectGalleryListImage($project_id);
        }
        $data['project_categories'] = $this->projectspot_model->getProjectCategories();

        $this->load->view('create_project', $data);

    }

    public function queue_uploaded_file($project_id)
    {
        if (isset($_FILES['file']) && _perfex_upload_error($_FILES['file']['error'])) {
            header('HTTP/1.0 400 Bad error');
            echo _perfex_upload_error($_FILES['file']['error']);
            die;
        }

        $path = FCPATH . 'modules/projectspot/uploads/gallery_images/' . $project_id . '/';

        if (isset($_FILES['file']['name'])) {
            // Get the temp file path
            $tmpFilePath = $_FILES['file']['tmp_name'];
            // Make sure we have a filepath
            if (!empty($tmpFilePath) && $tmpFilePath != '') {
                _maybe_create_upload_path($path);
                $filename = $_FILES['file']['name'];
                $newFilePath = $path . $filename;

                // Upload the file into the temp dir
                if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                    $attachment = [];
                    $attachment[] = [
                        'image_url' => $filename,
                        'project_id' => $project_id,
                        'created_at' => date('Y-m-d H:i:s')
                    ];

                    $this->projectspot_model->addGalleryListImage($attachment[0]);
                }
            }
        }

        echo json_encode([
            'url' => admin_url('projectspot/create_project/' . $project_id),
        ]);

    }

    public function delete_project($project_id)
    {
        if (!has_permission('projectspot', '', 'delete')) {
            access_denied('projectspot');
        }

        if (!$project_id) {
            redirect(admin_url('projectspot/projects'));
        }

        $response = $this->projectspot_model->deleteProjectGallery($project_id);

        if ($response == true) {
            set_alert('success', _l('deleted', _l('projectspot')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('projectspot')));
        }

        redirect(admin_url('projectspot/projects'));
    }

    public function update_project_status($id, $status)
    {
        if ($this->input->is_ajax_request()) {
            $this->projectspot_model->changeProjectStatus($id, $status);
        }
    }

    public function project_categories()
    {
        if (!has_permission('projectspot', '', 'view')) {
            access_denied('projectspot');
        }

        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('projectspot', 'tables/categories'));
        }

        $data['title'] = _l('projectspot') . ' - ' . _l('projectspot_categories');
        $this->load->view('categories', $data);
    }

    public function create_project_category($category_id = '')
    {
        if (!has_permission('projectspot', '', 'create')) {
            access_denied('projectspot');
        }

        if ($this->input->post() && $category_id === '') {

            $response = $this->projectspot_model->addProjectCategory($this->input->post() + ['created_at' => date('Y-m-d H:i:s')]);

            if ($response == true) {
                set_alert('success', _l('projectspot_category_created_successfully'));
            } else {
                set_alert('warning', _l('projectspot_category_not_created_successfully'));
            }

            redirect(admin_url('projectspot/project_categories'));

        } elseif ($this->input->post() && $category_id !== '') {
            $response = $this->projectspot_model->updateProjectCategory($category_id, $this->input->post());

            if ($response == true) {
                set_alert('success', _l('projectspot_category_updated_successfully'));
            } else {
                set_alert('warning', _l('projectspot_category_not_updated_successfully'));
            }

            redirect(admin_url('projectspot/project_categories'));
        }

        $data['title'] = _l('projectspot') . ' - ' . _l('projectspot_categories');
        if ($category_id) {
            $data['category_data'] = $this->projectspot_model->getProjectCategory($category_id);
        }

        $this->load->view('create_category', $data);
    }

    public function delete_project_category($category_id)
    {
        if (!has_permission('projectspot', '', 'delete')) {
            access_denied('projectspot');
        }

        if (!$category_id) {
            redirect(admin_url('projectspot/project_categories'));
        }

        $response = $this->projectspot_model->deleteProjectCategory($category_id);

        if (is_array($response) && isset($response['referenced'])) {
            set_alert('warning', _l('is_referenced', _l('projectspot_category')));
        } elseif ($response == true) {
            set_alert('success', _l('deleted', _l('projectspot_category')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('projectspot_category')));
        }

        redirect(admin_url('projectspot/project_categories'));

    }

    public function update_images_order()
    {
        if ($this->input->post()) {
            $imageids_arr = array();

            if ($this->input->post('imageids')) {
                $imageids_arr = $this->input->post('imageids');
            }

            if (count($imageids_arr) > 0) {
                $position = 1;
                foreach ($imageids_arr as $id) {
                    $this->projectspot_model->updateGalleryListImage($id, ['image_order' => $position]);
                    $position++;
                }
                echo json_encode([
                    'error' => '0',
                    'message' => 'Updated Successfully!',
                ]);
                die;
            } else {
                echo json_encode([
                    'error' => '0',
                    'message' => 'Failed To Update!',
                ]);
                die;
            }
        }
    }

    public function delete_image()
    {
        if (!has_permission('projectspot', '', 'delete')) {
            access_denied('projectspot');
        }

        if ($this->input->post()) {

            $project_id = $this->input->post('project_id');
            $image_id = $this->input->post('image_id');
            $image_path = $this->input->post('image_path');

            $response = $this->projectspot_model->deleteGalleryListImage($project_id, $image_id, $image_path);

            if ($response == true) {
                set_alert('success', _l('deleted', _l('projectspot_image')));
            } else {
                set_alert('warning', _l('problem_deleting', _l('projectspot_image')));
            }

            redirect(admin_url('projectspot/create_project/'.$project_id));

        }

    }

    public function update_project_category_status($id, $status)
    {
        if ($this->input->is_ajax_request()) {
            $this->projectspot_model->changeCategoryStatus($id, $status);
        }
    }

    public function settings()
    {
        if (!is_admin()) {
            access_denied('projectspot');
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

            redirect(admin_url('projectspot/settings'), 'refresh');
        }

        $data['title'] = _l('projectspot') . ' - ' . _l('settings');
        $this->load->view('settings', $data);
    }

}

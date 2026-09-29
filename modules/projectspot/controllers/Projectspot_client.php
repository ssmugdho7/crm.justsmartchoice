<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Projectspot_client extends ClientsController
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

    public function projects_showcase()
    {

        $accessMenu = false;

        if (get_option('projectspot_show_menu_client_side') == '1') {
            $accessMenu = true;
        }
        if (get_option('projectspot_should_client_be_logged_in') == '0' && !is_client_logged_in()) {
            $accessMenu = false;
        }

        if (!$accessMenu) {
            redirect(site_url('knowledge-base'));
        }

        $data = [];

        $data['title'] = _l('projectspot_showcase_gallery');

        $data['categories_list'] = $this->projectspot_model->getProjectCategories('1');
        $data['projects_list'] = $this->projectspot_model->getProjectGalleryList('1');

        $this->data($data);
        $this->view('client/showcase');
        $this->layout();
    }

    public function view_project($project_id)
    {

        $accessMenu = false;

        if (get_option('projectspot_show_menu_client_side') == '1') {
            $accessMenu = true;
        }
        if (get_option('projectspot_should_client_be_logged_in') == '0' && !is_client_logged_in()) {
            $accessMenu = false;
        }

        if (!$accessMenu) {
            redirect(site_url('knowledge-base'));
        }

        $data = [];

        $data['project_data'] = $this->projectspot_model->getSingleProjectGalleryListWithImages($project_id);

        $data['title'] = _l('projectspot_showcase_gallery') . ' - '. $data['project_data']->project_name;

        $this->data($data);
        $this->view('client/showcase_single');
        $this->layout();
    }
}
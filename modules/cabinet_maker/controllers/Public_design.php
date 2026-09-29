<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Public_design extends App_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('cabinet_maker/cabinet_maker_model');
        $this->load->helper('cabinet_maker/cabinet_maker');
    }

    public function view($slug)
    {
        $share = $this->cabinet_maker_model->get_share_by_slug((string) $slug);
        if (!$share || ($share->expires_at && strtotime($share->expires_at) < time())) {
            show_404();
        }
        $design = $this->cabinet_maker_model->get_design($share->design_id);
        if (!$design) {
            show_404();
        }
        $this->db->set('views', 'views+1', false)->where('id', $share->id)->update(cabinet_maker_table('shares'));
        $this->load->view('client/view', [
            'title' => $design->name,
            'design' => $design,
            'share' => $share,
            'parts' => $this->cabinet_maker_model->get_parts($design->id),
        ]);
    }
}

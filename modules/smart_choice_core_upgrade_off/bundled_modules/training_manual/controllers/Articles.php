<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Articles extends AdminController
{
    private function get_staff_member_options()
    {
        return $this->db->select("staffid, CONCAT(firstname, ' ', lastname) as full_name", false)
            ->where('active', 1)
            ->order_by('firstname', 'asc')
            ->get(db_prefix() . 'staff')
            ->result_array();
    }

    public function __construct()
    {
        parent::__construct();
        $this->load->model('training_manual_articles_model');
        $this->load->model('training_manual_books_model');
    }

    public function index()
    {
        $staff_id = function_exists('get_staff_user_id') ? get_staff_user_id() : $this->session->userdata('tfa_staffid');
        $user = get_staff($staff_id);
        $data['title'] = _l('training_manual_articles_list');

        $filter_query = $this->input->get('filter_query');
        if(!isset($filter_query)){
            $filter_query = "";
        }
        $filter_book_id = $this->input->get('filter_book_id');
        $filter_book = null;
        if(!isset($filter_book_id) || $filter_book_id == ""){
            $filter_book_id = null;
        }else{
            $filter_book = $this->training_manual_books_model->get($filter_book_id);
            if(!isset($filter_book)){
                show_404();
            }
        }
        $filter_is_owner = $this->input->get('filter_is_owner');
        if(!isset($filter_is_owner)){
            $filter_is_owner = null;
            $filter_owner_id = null;
        }else{
            $filter_owner_id = $user->staffid;
        }

        $filter_is_bookmark = $this->input->get('filter_is_bookmark');
        $filter_language_code = $this->input->get('filter_language_code');
        $filter_style_preset = $this->input->get('filter_style_preset');
        $filter_audience = $this->input->get('filter_audience');
        $filter_content_kind = $this->input->get('filter_content_kind');
        if ($filter_audience === 'customer_portal' && $filter_content_kind === 'video' && !has_permission('training_manual_customer_videos', '', 'view') && !is_admin()) {
            access_denied('training_manual_customer_videos');
        }
        $view_mode = $this->input->get('view_mode');
        if (!in_array($view_mode, ['cards', 'pipeline'], true)) { $view_mode = 'cards'; }

        $data['filter_query'] = $filter_query;
        $data['filter_book_id'] = $filter_book_id;
        $data['filter_is_owner'] = $filter_is_owner;
        $data['filter_is_bookmark'] = $filter_is_bookmark;
        $data['filter_language_code'] = $filter_language_code;
        $data['filter_style_preset'] = $filter_style_preset;
        $data['filter_audience'] = $filter_audience;
        $data['filter_content_kind'] = $filter_content_kind;
        $data['view_mode'] = $view_mode;
        $data['staff_members'] = $this->get_staff_member_options();
        $data['articles'] = $this->training_manual_articles_model->get_all_articles([
            'book_id' => $filter_book_id,
            'query' => $filter_query,
            'is_owner' => $filter_is_owner,
            'owner_id' => $filter_owner_id,
            'is_bookmark' => $filter_is_bookmark,
            'language_code' => $filter_language_code,
            'style_preset' => $filter_style_preset,
            'audience' => $filter_audience,
            'content_kind' => $filter_content_kind,
        ]);
        $data['user_id'] = isset($user->staffid) ? $user->staffid : 0;
        $data['books']    = $this->training_manual_books_model->get_all_books();
        $this->load->view('articles_manage', $data);
    }

    public function article($id = '')
    {
        if (!has_permission('training_manual_articles', '', 'view')) {
            access_denied('training_manual_articles');
        }
        if ($this->input->post()) {
            if ($id == '') {
                if (!has_permission('training_manual_articles', '', 'create')) {
                    access_denied('training_manual_articles');
                }
                $data = $this->input->post();
                $data['content'] = $this->input->post('content', false);
                if (($data['audience'] ?? '') === 'customer_portal' && ($data['content_kind'] ?? '') === 'video' && !has_permission('training_manual_customer_videos', '', 'create') && !is_admin()) {
                    access_denied('training_manual_customer_videos');
                }

                $id = $this->training_manual_articles_model->add($data);
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('training_manual_article')));
                    $submit_value = $this->input->post('submit');
                    if(isset($submit_value) && $submit_value == 'SAVE_AND_BUILD'){
                        redirect(admin_url('training_manual/articles/mindmap?article_id=' . $id));
                    }else{
                        redirect(admin_url('training_manual/articles/article/' . $id));
                    }
                }
            } else {
                if (!has_permission('training_manual_articles', '', 'edit')) {
                    access_denied('training_manual_articles');
                }
                $data = $this->input->post();
                $data['content'] = $this->input->post('content', false);
                if (($data['audience'] ?? '') === 'customer_portal' && ($data['content_kind'] ?? '') === 'video' && !has_permission('training_manual_customer_videos', '', 'edit') && !is_admin()) {
                    access_denied('training_manual_customer_videos');
                }

                $success = $this->training_manual_articles_model->update($data, $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('training_manual_article')));
                }
                $submit_value = $this->input->post('submit');
                if(isset($submit_value) && $submit_value == 'SAVE_AND_BUILD'){
                    redirect(admin_url('training_manual/articles/mindmap?article_id=' . $id));
                }else{
                    redirect(admin_url('training_manual/articles/article/' . $id));
                }
            }
        }
        $back_url = $this->input->get('back_url');
        if(isset($back_url)){
            $data['back_url'] = $back_url;
        }
        if ($id == '') {
            $title = _l('training_manual_create_article');
            $clone_id = $this->input->get('clone_id');
            if(isset($clone_id)){
                $data['article'] = $this->training_manual_articles_model->get($clone_id);
                $data['clone_id'] = isset($data['article']) ? $data['article']->id : null;
            }
        } else {
            $data['article']        = $this->training_manual_articles_model->get($id);
            $title = _l('edit', _l('training_manual_article_lowercase'));
        }
        $data['title']                 = $title;
        $data['books']    = $this->training_manual_books_model->get_all_books();
        $data['staff_members'] = $this->get_staff_member_options();
        $this->load->view('article', $data);
    }

    public function show($id)
    {
        $data['articles'] = $this->training_manual_articles_model->get_all_articles([
            'article_id' => $id,
        ]);
        if (isset($data['articles'][0])) {
            $article = $data['articles'][0];
            $data['article'] = $article;
            $data['title']                 = $article['title'];
            // counter
            $this->add_count($id);
            switch ($article['type']) {
                case 'document':
                    $this->load->view('article_show', $data);
                    break;
                case 'mindmap':
                    $this->load->view('article_mindmap_show', $data);
                    break;
                default:
                    show_404();
                    break;
            }
        } else {
            show_404();
        }
    }
    // This is the counter function.. 
    function add_count($id)
    {
        $cookie_name = 'training_manual_article_counter_'.$id;
        // load cookie helper
        $this->load->helper('cookie');
        // this line will return the cookie which has slug name
        $check_visitor = $this->input->cookie($cookie_name, TRUE);
        // this line will return the visitor ip address
        $ip = $this->input->ip_address();
        
        if ($check_visitor == false) {

            $cookie = array(
                "name"   => $cookie_name,
                "value"  => true,
                "expire" =>  time() + 7200,
                "secure" => false
            );

            $this->input->set_cookie($cookie);
            
            $this->training_manual_articles_model->count_view($id);
        }

    
    }

    public function countView()
    {
        if ($this->input->post()) {
            if ($this->input->is_ajax_request()) {

                $articleId = $this->input->post('article_id');

                if(isset($articleId)){
                    $this->training_manual_articles_model->count_view($articleId);
                }

                echo json_encode(["done" => 1]);
                die();
            }
        }
    }

    public function delete($id)
    {
        if (!has_permission('training_manual_articles', '', 'delete')) {
            access_denied('training_manual_articles');
        }
        if (!$id) {
            redirect(admin_url('training_manual/articles'));
        }
        $response = $this->training_manual_articles_model->delete($id);
        if ($response == true) {
            set_alert('success', _l('deleted', _l('training_manual_article')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('training_manual_article_lowercase')));
        }
        redirect(admin_url('training_manual/articles'));
    }

    public function validateslug()
    {
        $rs = false;

        if ($this->input->post()) {
            if ($this->input->is_ajax_request()) {

                $slug = $this->input->post('slug');
                $except_id = $this->input->post('except_id');

                if(!isset($except_id)){
                    $except_id = null;
                }

                if(isset($slug)){
                    $exist = $this->training_manual_articles_model->exist_slug($slug, $except_id);
                }

                $rs = !$exist;

            }
        }

        echo json_encode(["result" => $rs]);
        die();
    }

    public function bookmark_switch()
    {
        $rs = true;

        if ($this->input->post()) {
            if ($this->input->is_ajax_request()) {

                $is_on = $this->input->post('is_on');
                $article_id = $this->input->post('article_id');

                $staff_id = function_exists('get_staff_user_id') ? get_staff_user_id() : $this->session->userdata('tfa_staffid');
        $user = get_staff($staff_id);

                if(isset($is_on) && isset($article_id)){
                    $article = $this->training_manual_articles_model->get($article_id);
                    if(!isset($article)){
                        $rs = false;
                    }else{
                        $this->training_manual_articles_model->switch_bookmark($user->staffid, $article_id, $is_on);
                        $rs = true;
                    }
                }

            }
        }

        echo json_encode(["result" => $rs]);
        die();
    }

    public function mindmap()
    {
        $data['title'] = _l('training_manual_design_mindmap');
        $article_id = $this->input->get('article_id');

        if(!isset($article_id) || $article_id == ''){
            show_404();
        }

        $article = $this->training_manual_articles_model->get($article_id);

        if(!isset($article)){
            show_404();
        }

        $data['article'] = $article;
        $data['back_url'] = admin_url('training_manual/articles/article/' . $article->id);
        $this->load->view('mindmap', $data);
    }

    public function mindmap_save()
    {
        $rs = false;

        if ($this->input->post()) {
            if ($this->input->is_ajax_request()) {
                $data = $this->input->post();

                $rs_update = $this->training_manual_articles_model->update_mindmap($data);
                if($rs_update){
                    $rs = true;
                }else{
                    $rs = false;
                }
            }
        }

        echo json_encode(["result" => $rs]);
        die();
    }


    public function export_articles()
    {
        if (!has_permission('training_manual_articles', '', 'view')) {
            access_denied('training_manual_articles');
        }
        $articles = $this->training_manual_articles_model->get_all_articles([]);
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="training_manual_articles_' . date('Ymd_His') . '.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['Book', 'Title', 'Description', 'Content', 'Language', 'Style', 'Published']);
        foreach ($articles as $article) {
            fputcsv($out, [
                $article['book_name'] ?? '',
                $article['title'] ?? '',
                $article['description'] ?? '',
                $article['content'] ?? '',
                $article['language_code'] ?? 'en',
                $article['style_preset'] ?? 'smart_choice',
                !empty($article['is_publish']) ? 'Yes' : 'No',
            ]);
        }
        fclose($out);
        exit;
    }

    public function sample_articles()
    {
        if (!has_permission('training_manual_articles', '', 'view')) {
            access_denied('training_manual_articles');
        }
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="training_manual_articles_sample_header.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['book_id', 'title', 'description', 'content', 'language_code', 'style_preset', 'is_publish']);
        fputcsv($out, ['1', 'Sales Hub Training', 'How to use the Sales Hub module.', '<div class="sc-manual"><h2>Sales Hub Training</h2><p>Paste article content here.</p></div>', 'en', 'smart_choice', '1']);
        fclose($out);
        exit;
    }

    public function import_articles()
    {
        if (!has_permission('training_manual_articles', '', 'create')) {
            access_denied('training_manual_articles');
        }
        if (!$this->input->post()) {
            redirect(admin_url('training_manual/articles'));
        }
        if (!isset($_FILES['import_file']) || empty($_FILES['import_file']['tmp_name'])) {
            set_alert('warning', 'Please select a CSV file to import.');
            redirect(admin_url('training_manual/articles'));
        }
        $handle = fopen($_FILES['import_file']['tmp_name'], 'r');
        if (!$handle) {
            set_alert('warning', 'The import file could not be opened.');
            redirect(admin_url('training_manual/articles'));
        }
        $has_header = $this->input->post('has_header') == '1';
        if ($has_header) {
            fgetcsv($handle);
        }
        $imported = 0;
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 4) {
                continue;
            }
            $data = [
                'book_id' => $row[0] ?? '',
                'title' => $row[1] ?? '',
                'description' => $row[2] ?? '',
                'content' => $row[3] ?? '',
                'language_code' => $row[4] ?? 'en',
                'style_preset' => $row[5] ?? 'smart_choice',
                'is_publish' => (isset($row[6]) && in_array(strtolower((string) $row[6]), ['1', 'yes', 'true'], true)) ? 1 : 0,
                'type' => 'document',
            ];
            if (!empty($data['book_id']) && !empty($data['title'])) {
                if ($this->training_manual_articles_model->add($data)) {
                    $imported++;
                }
            }
        }
        fclose($handle);
        set_alert('success', $imported . ' training article records imported successfully.');
        redirect(admin_url('training_manual/articles'));
    }

    public function mass_action()
    {
        if (!$this->input->post()) {
            redirect(admin_url('training_manual/articles'));
        }
        $action = $this->input->post('mass_action');
        $ids = $this->input->post('article_ids');
        if (!is_array($ids)) {
            $ids = [];
        }
        if ($action === 'delete') {
            if (!has_permission('training_manual_articles', '', 'delete')) {
                access_denied('training_manual_articles');
            }
            $deleted = $this->training_manual_articles_model->mass_delete($ids);
            set_alert('success', $deleted . ' training articles deleted successfully.');
        }
        redirect(admin_url('training_manual/articles'));
    }

    public function change_creator()
    {
        if (!has_permission('training_manual_articles', '', 'edit')) {
            access_denied('training_manual_articles');
        }
        $article_id = (int) $this->input->post('article_id');
        $staff_id = (int) $this->input->post('staff_id');
        if ($article_id && $staff_id && $this->training_manual_articles_model->change_creator($article_id, $staff_id)) {
            set_alert('success', 'Training article creator updated successfully.');
        } else {
            set_alert('warning', 'The creator could not be updated.');
        }
        redirect(admin_url('training_manual/articles'));
    }

}

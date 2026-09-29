<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Publishx extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('publishx_model');
    }

    private function can_view()
    {
        return has_permission('publishx_posts', '', 'view') || has_permission('publishx_posts', '', 'view_own');
    }

    public function index()
    {
        redirect(admin_url('publishx/posts'));
    }

    public function posts()
    {
        if (!$this->can_view()) {
            access_denied('publishx_posts');
        }
        if ($this->input->is_ajax_request()) {
            return $this->app->get_table_data(module_views_path('publishx', 'tables/posts'));
        }
        $this->load->view('posts', ['title' => _l('publishx_posts')]);
    }

    public function post($id = '')
    {
        if ($id) {
            if (!has_permission('publishx_posts', '', 'edit')) {
                access_denied('publishx_posts');
            }
        } elseif (!has_permission('publishx_posts', '', 'create')) {
            access_denied('publishx_posts');
        }

        if ($this->input->post()) {
            $data = $this->input->post();
            foreach (['short_content','full_content'] as $htmlField) { if (isset($data[$htmlField])) $data[$htmlField] = publishx_strip_code_fences($data[$htmlField]); }
            foreach (['meta_title','meta_description','meta_keywords'] as $textField) { if (isset($data[$textField])) $data[$textField] = trim(strip_tags(publishx_strip_code_fences($data[$textField]))); }
            if (isset($data['hero_video_url'])) $data['hero_video_url'] = publishx_normalize_video_url($data['hero_video_url']);
            $data['post_slug'] = slug_it(!empty($data['post_slug']) ? $data['post_slug'] : $data['post_title']);
            $existing = $id ? $this->publishx_model->getPost($id) : null;
            $data['author_id'] = $existing ? $existing->author_id : get_staff_user_id();
            $data['created_at'] = $existing ? $existing->created_at : date('Y-m-d H:i:s');
            $data['scheduled'] = !empty($data['scheduled']) ? date('Y-m-d H:i:s', strtotime($data['scheduled'])) : null;

            if ($id) {
                $this->publishx_model->updatePost($id, $data);
                $postId = (int) $id;
            } else {
                $postId = (int) $this->publishx_model->addPost($data);
            }

            if (!$postId) {
                set_alert('danger', 'Unable to save the post.');
                redirect(admin_url('publishx/posts'));
            }

            $upload = publishx_handle_post_feature_image_upload($postId);
            if (!empty($upload['success'])) {
                $this->publishx_model->updatePost($postId, [
                    'featured_image'     => $upload['file_name'],
                    'featured_image_url' => $upload['url'],
                ]);
            } elseif (!empty($upload['message'])) {
                set_alert('warning', $upload['message']);
            }

            $videoUpload = publishx_handle_post_video_upload($postId);
            if (!empty($videoUpload['success'])) {
                $this->publishx_model->updatePost($postId, ['hero_video_url' => $videoUpload['url']]);
            } elseif (!empty($videoUpload['message'])) {
                set_alert('warning', $videoUpload['message']);
            }

            if (isset($_POST['publish_now'])) {
                $result = $this->publishx_model->publish_to_website($postId);
                set_alert($result['success'] ? 'success' : 'warning', $result['success'] ? _l('publishx_published_to_website') : $result['message']);
            } else {
                set_alert('success', _l('updated_successfully', _l('publishx_posts')));
            }

            redirect(admin_url('publishx/post/' . $postId));
        }

        $data = [
            'title'           => _l('publishx_create_post'),
            'post_categories' => $this->publishx_model->getCategories(),
            'post_languages'  => $this->publishx_model->getLanguages(),
            'websites'        => $this->publishx_model->getWebsites(),
            'templates'       => publishx_supported_blog_themes(),
            'media'           => publishx_scan_media_library(),
        ];
        if ($id) {
            $data['post_data'] = $this->publishx_model->getPost($id);
        } elseif ($this->input->get('template')) {
            $templateKey = preg_replace('/[^a-z0-9_\-]/i', '', (string) $this->input->get('template'));
            $data['post_data'] = (object) ['template_key' => $templateKey];
        }
        $this->load->view('create_post', $data);
    }

    public function publish($id)
    {
        if (!has_permission('publishx_posts', '', 'edit')) {
            access_denied('publishx_posts');
        }
        $result = $this->publishx_model->publish_to_website($id);
        set_alert($result['success'] ? 'success' : 'warning', $result['success'] ? _l('publishx_published_to_website') : $result['message']);
        redirect(admin_url('publishx/post/' . $id));
    }

    public function delete_post($id)
    {
        if (!has_permission('publishx_posts', '', 'delete')) {
            access_denied('publishx_posts');
        }
        $this->publishx_model->deletePost($id);
        set_alert('success', _l('deleted', _l('publishx_posts')));
        redirect(admin_url('publishx/posts'));
    }

    public function categories()
    {
        if (!has_permission('publishx_categories', '', 'view')) {
            access_denied('publishx_categories');
        }
        if ($this->input->is_ajax_request()) {
            return $this->app->get_table_data(module_views_path('publishx', 'tables/categories'));
        }
        $this->load->view('categories', ['title' => _l('publishx_categories')]);
    }

    public function category($id = '')
    {
        if ($this->input->post()) {
            $id ? $this->publishx_model->updateCategory($id, $this->input->post()) : $this->publishx_model->addCategory($this->input->post() + ['created_at' => date('Y-m-d H:i:s')]);
            set_alert('success', _l('updated_successfully', _l('publishx_categories')));
            redirect(admin_url('publishx/categories'));
        }
        $this->load->view('create_category', ['title' => _l('publishx_categories'), 'category_data' => $id ? $this->publishx_model->getCategory($id) : null]);
    }

    public function delete_category($id)
    {
        if (!has_permission('publishx_categories', '', 'delete')) {
            access_denied('publishx_categories');
        }
        $result = $this->publishx_model->deleteCategory($id);
        set_alert(is_array($result) ? 'warning' : 'success', is_array($result) ? _l('is_referenced', _l('publishx_categories')) : _l('deleted', _l('publishx_categories')));
        redirect(admin_url('publishx/categories'));
    }

    public function languages()
    {
        redirect(admin_url('settings?group=publishx-settings'));
    }

    public function websites()
    {
        if (!has_permission('publishx_websites', '', 'view')) {
            access_denied('publishx_websites');
        }
        $this->load->view('websites', ['title' => _l('publishx_websites'), 'websites' => $this->publishx_model->getWebsites()]);
    }

    public function website($id = '')
    {
        if ($this->input->post()) {
            $saved = $this->publishx_model->saveWebsite($this->input->post(), $id ?: null);
            set_alert('success', _l('updated_successfully', _l('publishx_websites')));
            redirect(admin_url('publishx/website/' . $saved));
        }
        $this->load->view('website', ['title' => _l('publishx_websites'), 'website' => $id ? $this->publishx_model->getWebsite($id) : null]);
    }

    public function test_website($id)
    {
        if (!has_permission('publishx_websites', '', 'edit')) {
            access_denied('publishx_websites');
        }
        $result = $this->publishx_model->testWebsite($id);
        set_alert($result['success'] ? 'success' : 'warning', $result['message']);
        redirect(admin_url('publishx/websites'));
    }

    public function delete_website($id)
    {
        $result = $this->publishx_model->deleteWebsite($id);
        set_alert(is_array($result) ? 'warning' : 'success', is_array($result) ? _l('is_referenced', _l('publishx_websites')) : _l('deleted', _l('publishx_websites')));
        redirect(admin_url('publishx/websites'));
    }

    public function themes()
    {
        $this->load->view('themes', ['title' => _l('publishx_templates'), 'themes' => publishx_supported_blog_themes()]);
    }

    public function theme_preview($key)
    {
        $themes = publishx_supported_blog_themes();
        $selected = null;
        foreach ($themes as $theme) {
            if ($theme['id'] === $key) {
                $selected = $theme;
                break;
            }
        }
        if (!$selected) {
            show_404();
        }
        $this->load->view('theme_preview', ['title' => $selected['title'], 'theme' => $selected]);
    }

    public function media()
    {
        $this->load->view('media', ['title' => _l('publishx_media_library'), 'media' => publishx_scan_media_library()]);
    }

    public function upload_media()
    {
        if (!has_permission('publishx_posts', '', 'create') && !has_permission('publishx_posts', '', 'edit')) {
            access_denied('publishx_posts');
        }
        $result = publishx_handle_library_upload();
        set_alert($result['success'] ? 'success' : 'warning', $result['success'] ? 'Blogging media uploaded successfully.' : $result['message']);
        redirect(admin_url('publishx/media'));
    }

    public function reports()
    {
        $this->load->view('reports', ['title' => _l('publishx_reports'), 'summary' => $this->publishx_model->reportSummary()]);
    }

    public function how_to()
    {
        $this->load->view('how_to', ['title' => _l('publishx_how_to')]);
    }

    public function settings()
    {
        redirect(admin_url('settings?group=publishx-settings'));
    }

    public function ai()
    {
        if (!has_permission('publishx_posts', '', 'create') && !has_permission('publishx_posts', '', 'edit')) {
            access_denied('publishx_posts');
        }
        if (!$this->input->is_ajax_request()) {
            show_404();
        }
        $result = publishx_ai_generate($this->input->post('field'), trim((string) $this->input->post('title')), trim((string) $this->input->post('prompt')));
        echo json_encode($result);
    }
}

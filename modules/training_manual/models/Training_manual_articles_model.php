<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Training_manual_articles_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    private function current_staff_id()
    {
        if (function_exists('get_staff_user_id')) {
            $id = get_staff_user_id();
            if ($id) {
                return (int) $id;
            }
        }
        $id = $this->session->userdata('tfa_staffid');
        return $id ? (int) $id : 0;
    }

    private function make_slug($title, $except_id = null)
    {
        $base = preg_replace('/[^a-z0-9]+/i', '-', strtolower((string) $title));
        $base = trim($base, '-');
        if ($base === '') {
            $base = (string) time();
        }
        $base = substr($base, 0, 45);
        $slug = $base;
        $i = 1;
        while ($this->exist_slug($slug, $except_id)) {
            $slug = substr($base, 0, 38) . '-' . $i;
            $i++;
        }
        return $slug;
    }

    private function handle_thumbnail_upload($old_file = '')
    {
        if (!isset($_FILES['thumbnail_file']) || empty($_FILES['thumbnail_file']['name'])) {
            return $old_file;
        }

        $file = $_FILES['thumbnail_file'];
        if (!empty($file['error'])) {
            return $old_file;
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'heic'];
        if (!in_array($ext, $allowed, true)) {
            return $old_file;
        }

        $path = FCPATH . 'uploads/training_manual/';
        if (function_exists('_maybe_create_upload_path')) {
            _maybe_create_upload_path($path);
        } elseif (!is_dir($path)) {
            @mkdir($path, 0755, true);
        }

        $filename = 'article_' . $this->current_staff_id() . '_' . time() . '_' . mt_rand(1000, 9999) . '.' . $ext;
        $destination = $path . $filename;
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            if ($old_file && file_exists($path . $old_file)) {
                @unlink($path . $old_file);
            }
            return $filename;
        }

        return $old_file;
    }

    public function add($data)
    {
        $staff_id = $this->current_staff_id();
        $type = isset($data['type']) && in_array($data['type'], ['document', 'mindmap'], true) ? $data['type'] : 'document';

        $bookId = isset($data['book_id']) ? (int) $data['book_id'] : 0;
        if (!$bookId) {
            return false;
        }
        if (!isset($this->training_manual_books_model)) {
            $this->load->model('training_manual_books_model');
        }
        $book = $this->training_manual_books_model->get($bookId);
        if (!$book) {
            return false;
        }

        $title = trim((string) ($data['title'] ?? ''));
        $slug = $this->make_slug($title);
        $authorId = !empty($data['author_id']) ? (int) $data['author_id'] : $staff_id;
        $dataDB = [
            'author_id' => $authorId,
            'created_by' => $authorId,
            'updated_by' => $staff_id,
            'title' => $title,
            'description' => (string) ($data['description'] ?? ''),
            'type' => $type,
            'book_id' => $book->id,
            'slug' => $slug,
            'short_code' => $slug,
            'visibility' => (string) ($data['visibility'] ?? 'manual_permissions'),
            'language_code' => (string) ($data['language_code'] ?? get_option('training_manual_default_language') ?: 'en'),
            'style_preset' => (string) ($data['style_preset'] ?? get_option('training_manual_default_style') ?: 'smart_choice'),
            'audience' => in_array(($data['audience'] ?? 'internal'), ['internal', 'customer_portal'], true) ? (string) $data['audience'] : 'internal',
            'content_kind' => in_array(($data['content_kind'] ?? 'article'), ['article', 'video'], true) ? (string) $data['content_kind'] : 'article',
            'is_publish' => isset($data['is_publish']) ? 1 : 0,
            'thumbnail' => $this->handle_thumbnail_upload(''),
        ];

        if ($type === 'document') {
            $dataDB['content'] = (string) ($data['content'] ?? '');
        } else {
            $clone = null;
            if (!empty($data['clone_id'])) {
                $clone = $this->get((int) $data['clone_id']);
            }
            if ($clone) {
                $dataDB['mindmap_content'] = $clone->mindmap_content;
                $new_thumb = function_exists('training_manual_copy_thumb_mindmap') ? training_manual_copy_thumb_mindmap($clone->mindmap_thumb, 'mindmap' . $staff_id . '_') : false;
                if ($new_thumb) {
                    $dataDB['mindmap_thumb'] = $new_thumb;
                }
            } else {
                $dataDB['mindmap_content'] = function_exists('training_manual_get_mindmap_content') ? training_manual_get_mindmap_content() : '';
                $new_thumb = function_exists('training_manual_copy_default_mindmap_thumb') ? training_manual_copy_default_mindmap_thumb('mindmap' . $staff_id . '_') : false;
                if ($new_thumb) {
                    $dataDB['mindmap_thumb'] = $new_thumb;
                }
            }
        }

        $this->db->insert(db_prefix() . 'wiki_articles', $dataDB);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            log_activity('Training Article Added [ID:' . $insert_id . ']');
            return $insert_id;
        }
        return false;
    }

    public function get($id = '')
    {
        if (is_numeric($id)) {
            return $this->db->where('id', (int) $id)->get(db_prefix() . 'wiki_articles')->row();
        }
        return $this->db->get(db_prefix() . 'wiki_articles')->result_array();
    }

    public function update($data, $id)
    {
        $article = $this->get((int) $id);
        if (!$article) {
            return false;
        }
        $staff_id = $this->current_staff_id();
        $type = isset($data['type']) && in_array($data['type'], ['document', 'mindmap'], true) ? $data['type'] : 'document';

        $bookId = isset($data['book_id']) ? (int) $data['book_id'] : 0;
        if (!$bookId) {
            return false;
        }
        if (!isset($this->training_manual_books_model)) {
            $this->load->model('training_manual_books_model');
        }
        $book = $this->training_manual_books_model->get($bookId);
        if (!$book) {
            return false;
        }

        $title = trim((string) ($data['title'] ?? ''));
        $slug = !empty($article->slug) ? $article->slug : $this->make_slug($title, (int) $id);
        if (!empty($data['refresh_short_link'])) {
            $slug = $this->make_slug($title, (int) $id);
        }

        $dataDB = [
            'updated_by' => $staff_id,
            'author_id' => !empty($data['author_id']) ? (int) $data['author_id'] : (int) $article->author_id,
            'title' => $title,
            'description' => (string) ($data['description'] ?? ''),
            'type' => $type,
            'is_publish' => isset($data['is_publish']) ? 1 : 0,
            'book_id' => $book->id,
            'slug' => $slug,
            'short_code' => $slug,
            'visibility' => (string) ($data['visibility'] ?? 'manual_permissions'),
            'language_code' => (string) ($data['language_code'] ?? ($article->language_code ?? 'en')),
            'style_preset' => (string) ($data['style_preset'] ?? ($article->style_preset ?? 'smart_choice')),
            'audience' => in_array(($data['audience'] ?? ($article->audience ?? 'internal')), ['internal', 'customer_portal'], true) ? (string) ($data['audience'] ?? ($article->audience ?? 'internal')) : 'internal',
            'content_kind' => in_array(($data['content_kind'] ?? ($article->content_kind ?? 'article')), ['article', 'video'], true) ? (string) ($data['content_kind'] ?? ($article->content_kind ?? 'article')) : 'article',
            'thumbnail' => $this->handle_thumbnail_upload($article->thumbnail ?? ''),
        ];

        if ($type === 'document') {
            $dataDB['content'] = (string) ($data['content'] ?? '');
        }

        $this->db->set('updated_at', 'NOW()', false);
        $this->db->where('id', (int) $id)->update(db_prefix() . 'wiki_articles', $dataDB);
        log_activity('Training Article Updated [ID:' . (int) $id . ']');
        return true;
    }

    public function change_creator($article_id, $staff_id)
    {
        $article = $this->get((int) $article_id);
        if (!$article || !$staff_id) {
            return false;
        }
        $staff_exists = $this->db->where('staffid', (int) $staff_id)
            ->where('active', 1)
            ->count_all_results(db_prefix() . 'staff') > 0;
        if (!$staff_exists) {
            return false;
        }

        $this->db->trans_start();
        $this->db->set('author_id', (int) $staff_id);
        $this->db->set('created_by', (int) $staff_id);
        $this->db->set('last_creator_change_by', $this->current_staff_id());
        $this->db->set('last_creator_change_at', date('Y-m-d H:i:s'));
        $this->db->set('updated_by', $this->current_staff_id());
        $this->db->set('updated_at', 'NOW()', false);
        $this->db->where('id', (int) $article_id);
        $this->db->update(db_prefix() . 'wiki_articles');
        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return false;
        }

        $saved = $this->db->select('author_id, created_by')
            ->where('id', (int) $article_id)
            ->get(db_prefix() . 'wiki_articles')
            ->row();
        $success = $saved && (int) $saved->author_id === (int) $staff_id && (int) $saved->created_by === (int) $staff_id;
        if ($success) {
            log_activity('Training Article Creator Changed [ID:' . (int) $article_id . '][Staff ID:' . (int) $staff_id . ']');
        }
        return $success;
    }

    public function delete($id)
    {
        $article = $this->get((int) $id);
        if (!$article) {
            return false;
        }
        if (function_exists('training_manual_remove_thumb_mindmap') && !empty($article->mindmap_thumb)) {
            training_manual_remove_thumb_mindmap($article->mindmap_thumb);
        }
        $this->db->where('id', (int) $id)->delete(db_prefix() . 'wiki_articles');
        if ($this->db->affected_rows() > 0) {
            log_activity('Training Article Deleted [ID:' . (int) $id . ']');
            return true;
        }
        return false;
    }

    public function mass_delete($ids)
    {
        $deleted = 0;
        foreach ((array) $ids as $id) {
            if ($this->delete((int) $id)) {
                $deleted++;
            }
        }
        return $deleted;
    }

    public function delete_by_book($book_id)
    {
        $this->db->where('book_id', (int) $book_id)->delete(db_prefix() . 'wiki_articles');
        return $this->db->affected_rows() > 0;
    }

    public function get_all_articles($filters = [])
    {
        $tbl_articles = db_prefix() . 'wiki_articles';
        $tbl_books = db_prefix() . 'wiki_books';
        $tbl_authors = db_prefix() . 'staff';
        $tbl_staff_article = db_prefix() . 'wiki_staff_article';
        if (!isset($this->training_manual_books_model)) {
            $this->load->model('training_manual_books_model');
        }
        $sqlPermissionBook = '1 = 1';
        if (!has_permission('training_manual_articles', '', 'view')) {
            $sqlPermissionBook = $this->training_manual_books_model->getPermissionClause('TBLBooks');
        }
        $staff_id = $this->current_staff_id();
        $sql = "SELECT TBLArticles.*, CONCAT(IFNULL(TBLAuthors.firstname, ''), ' ', IFNULL(TBLAuthors.lastname, '')) AS author_fullname, CONCAT(IFNULL(TBLAuthors.firstname, ''), ' ', IFNULL(TBLAuthors.lastname, '')) AS creator_name, TBLAuthors.staffid AS creator_staff_id, TBLAuthors.profile_image AS creator_profile_image, TBLBooks.name AS book_name, TBLStaffArticle.id AS bookmark_id, TBLStaffArticle.created_by AS bookmark_created_by FROM {$tbl_articles} TBLArticles LEFT JOIN {$tbl_authors} TBLAuthors ON TBLAuthors.staffid = TBLArticles.author_id INNER JOIN {$tbl_books} TBLBooks ON TBLBooks.id = TBLArticles.book_id LEFT JOIN {$tbl_staff_article} TBLStaffArticle ON TBLArticles.id = TBLStaffArticle.article_id AND TBLStaffArticle.staff_id = ? WHERE 1 = 1 AND {$sqlPermissionBook}";
        $params = [$staff_id];
        if (isset($filters['article_id'])) { $sql .= ' AND TBLArticles.id = ?'; $params[] = (int) $filters['article_id']; }
        if (isset($filters['book_id'])) { $sql .= ' AND TBLArticles.book_id = ?'; $params[] = (int) $filters['book_id']; }
        if (!empty($filters['query'])) { $sql .= ' AND (TBLArticles.title LIKE ? OR TBLArticles.description LIKE ? OR TBLArticles.content LIKE ?)'; $params[] = '%' . $filters['query'] . '%'; $params[] = '%' . $filters['query'] . '%'; $params[] = '%' . $filters['query'] . '%'; }
        if (!empty($filters['language_code'])) { $sql .= ' AND TBLArticles.language_code = ?'; $params[] = $filters['language_code']; }
        if (!empty($filters['style_preset'])) { $sql .= ' AND TBLArticles.style_preset = ?'; $params[] = $filters['style_preset']; }
        if (!empty($filters['audience'])) { $sql .= ' AND TBLArticles.audience = ?'; $params[] = $filters['audience']; }
        if (!empty($filters['content_kind'])) { $sql .= ' AND TBLArticles.content_kind = ?'; $params[] = $filters['content_kind']; }
        if (isset($filters['is_owner'], $filters['owner_id']) && $filters['is_owner'] == '1') { $sql .= ' AND TBLArticles.author_id = ?'; $params[] = (int) $filters['owner_id']; }
        if (isset($filters['slug'])) { $sql .= ' AND TBLArticles.slug = ?'; $params[] = $filters['slug']; }
        if (isset($filters['is_publish'])) { $sql .= ' AND TBLArticles.is_publish = ?'; $params[] = (int) $filters['is_publish']; }
        if (isset($filters['is_bookmark']) && $filters['is_bookmark'] == 1) { $sql .= ' AND TBLStaffArticle.id IS NOT NULL'; }
        $sql .= ' ORDER BY TBLArticles.updated_at DESC';
        return array_values($this->db->query($sql, $params)->result_array());
    }

    public function exist_slug($slug, $except_id = null)
    {
        $this->db->where('slug', $slug);
        if ($except_id) {
            $this->db->where('id !=', (int) $except_id);
        }
        return $this->db->count_all_results(db_prefix() . 'wiki_articles') > 0;
    }

    public function count_view($articleId)
    {
        if (!$articleId) { return; }
        $this->db->set('view_counter', 'view_counter + 1', false)->where('id', (int) $articleId)->update(db_prefix() . 'wiki_articles');
    }

    public function get_published($slug)
    {
        $tbl_articles = db_prefix() . 'wiki_articles';
        $tbl_authors = db_prefix() . 'staff';
        $sql = "SELECT TBLArticles.*, CONCAT(IFNULL(TBLAuthors.firstname, ''), ' ', IFNULL(TBLAuthors.lastname, '')) AS author_fullname FROM {$tbl_articles} TBLArticles LEFT JOIN {$tbl_authors} TBLAuthors ON TBLArticles.author_id = TBLAuthors.staffid WHERE TBLArticles.is_publish = 1 AND (TBLArticles.slug = ? OR TBLArticles.short_code = ?) LIMIT 1";
        return array_values($this->db->query($sql, [$slug, $slug])->result_array());
    }

    public function switch_bookmark($staff_id, $article_id, $is_on)
    {
        $is_on == 1 ? $this->switch_bookmark_on($staff_id, $article_id) : $this->switch_bookmark_off($staff_id, $article_id);
    }

    public function switch_bookmark_on($staff_id, $article_id, $check = true)
    {
        if ($check && count($this->get_bookmark($staff_id, $article_id)) > 0) { return; }
        $this->db->insert(db_prefix() . 'wiki_staff_article', [
            'staff_id' => (int) $staff_id,
            'article_id' => (int) $article_id,
            'created_by' => (int) $staff_id,
        ]);
    }

    public function switch_bookmark_off($staff_id, $article_id, $check = true)
    {
        if ($check && count($this->get_bookmark($staff_id, $article_id)) === 0) { return; }
        $this->db->where('staff_id', (int) $staff_id)->where('article_id', (int) $article_id)->delete(db_prefix() . 'wiki_staff_article');
    }

    public function get_bookmark($staff_id, $article_id)
    {
        return $this->db->where('staff_id', (int) $staff_id)->where('article_id', (int) $article_id)->get(db_prefix() . 'wiki_staff_article')->result_array();
    }

    public function update_mindmap($data)
    {
        $article_id = $this->input->post('article_id');
        $mindmap_content = $this->input->post('mindmap_content', false);
        $mindmap_thumb = $this->input->post('mindmap_thumb');
        if (!$article_id || !$mindmap_content || !$mindmap_thumb) { return false; }
        $article = $this->get((int) $article_id);
        if (!$article) { return false; }
        $dataDB = ['mindmap_content' => $mindmap_content, 'updated_by' => $this->current_staff_id()];
        if (function_exists('training_manual_handle_thumb_mindmap_upload')) {
            $new_thumb = training_manual_handle_thumb_mindmap_upload($mindmap_thumb, 'mindmap' . $this->current_staff_id() . '_', $article->mindmap_thumb);
            if ($new_thumb) { $dataDB['mindmap_thumb'] = $new_thumb; }
        }
        $this->db->set('updated_at', 'NOW()', false)->where('id', (int) $article->id)->update(db_prefix() . 'wiki_articles', $dataDB);
        return true;
    }
}

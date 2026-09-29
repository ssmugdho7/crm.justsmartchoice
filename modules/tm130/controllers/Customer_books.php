<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Customer_books extends ClientsController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('training_manual_books_model');
    }

    public function index()
    {
        if (!is_client_logged_in()) {
            redirect(site_url('authentication/login'));
        }
        $books = $this->training_manual_books_model->get_customer_books('', true);
        foreach ($books as &$book) {
            $book['articles'] = $this->training_manual_books_model->get_customer_book_articles($book['id']);
        }
        unset($book);
        $data['books'] = $books;
        $data['title'] = _l('training_manual_customer_books');
        $this->load->view('client_portal_books', $data);
    }

    public function article($id = 0)
    {
        if (!is_client_logged_in()) {
            redirect(site_url('authentication/login'));
        }

        $article = $this->db->select('A.*')
            ->from(db_prefix() . 'wiki_articles A')
            ->join(db_prefix() . 'wiki_books B', 'B.id = A.book_id', 'inner')
            ->where('A.id', (int) $id)
            ->where('A.is_publish', 1)
            ->where('B.customer_visible', 1)
            ->get()->row_array();

        if (!$article) {
            show_404();
        }

        $data['article'] = $article;
        $data['title'] = $article['title'];
        $this->load->view('customer_portal_article', $data);
    }
}

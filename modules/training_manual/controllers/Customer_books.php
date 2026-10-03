<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Customer_books extends ClientsController {
 public function __construct(){parent::__construct();$this->load->model('training_manual_books_model');}
 public function index()
 {
     $books = $this->training_manual_books_model->get_customer_books('', true);
     $articles = $this->training_manual_books_model->get_customer_book_article_cards(array_column($books, 'id'));
     foreach ($books as &$book) { $book['articles'] = $articles[$book['id']] ?? []; }
     unset($book);
     $this->data(['books' => $books, 'title' => _l('training_manual_training_library')]);
     $this->view('client_portal_books');
     $this->layout();
 }
 public function article($id=0){$a=db_prefix().'wiki_articles';$b=db_prefix().'wiki_books';$this->db->select('A.*,B.name AS book_name,B.customer_visible,B.cover_image AS book_cover')->from($a.' A')->join($b.' B','B.id=A.book_id','left')->where('A.id',(int)$id)->where('A.is_publish',1)->where('B.customer_visible',1);$article=$this->db->get()->row_array();if(!$article){show_404();return;}$this->data(['article'=>$article,'title'=>$article['title']]);$this->view('customer_portal_article');$this->layout();}
}

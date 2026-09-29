<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Share extends App_Controller
{
    public function index($token = '')
    {
        $token = trim((string) $token);
        if ($token === '') { show_404(); }

        $table = db_prefix() . 'notes';
        if (!$this->db->table_exists($table) || !$this->db->field_exists('share_token', $table)) { show_404(); }

        $note = $this->db->where('share_token', $token)
            ->where('share_enabled', 1)
            ->get($table)
            ->row();
        if (!$note) { show_404(); }

        $data['title'] = !empty($note->title) ? $note->title : _l('note');
        $data['note'] = $note;
        $this->load->view('share_note', $data);
    }
}

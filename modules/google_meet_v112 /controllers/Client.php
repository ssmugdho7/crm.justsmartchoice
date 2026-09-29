<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Client extends ClientsController
{
    public function index()
    {
        redirect(site_url('google-meet-client'));
    }
    public function view($id)
    {
        redirect(site_url('google-meet-client/' . (int)$id));
    }
    public function join($id)
    {
        redirect(site_url('google-meet-join/' . (int)$id));
    }
}

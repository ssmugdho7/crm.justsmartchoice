<?php

defined('BASEPATH') or exit('No direct script access allowed');

class opportunity_send_email extends App_mail_template
{
    protected $for = 'opportunity';

    protected $opportunity;

    public $slug = 'opportunity_send_email';
    public $cc = '';
    public $send_to = '';
    public $attachments = [];


    public function __construct($params)
    {
        parent::__construct();

        $this->cc = $params->cc;
        $this->opportunity = $params;
        $this->send_to = $params->recipient;
        $this->attachments = (!empty($params->attachments)) ? $params->attachments : [];
        $this->template = $params->template;

        $this->set_merge_fields('opportunity_merge_fields', $this->opportunity);

    }

    public function build()
    {

    }
}

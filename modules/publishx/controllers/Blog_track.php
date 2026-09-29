<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Blog_track extends App_Controller
{
    public function view($postId = 0)
    {
        $postId=(int)$postId;
        if($postId<=0 || get_option('publishx_enable_view_tracking')!=='1'){return $this->pixel();}
        $this->load->model('publishx_model');
        $ua=(string)$this->input->user_agent();
        $ref=(string)$this->input->server('HTTP_REFERER');
        $source=parse_url($ref,PHP_URL_HOST) ?: 'direct';
        $device=preg_match('/mobile|android|iphone|ipad/i',$ua)?'mobile':'desktop';
        $ip=(string)$this->input->ip_address();
        $this->publishx_model->recordView($postId,[
            'ip_hash'=>hash('sha256',$ip.'|'.config_item('encryption_key')),
            'referrer'=>substr($ref,0,500),'source'=>substr($source,0,191),'medium'=>$ref?'referral':'direct','device'=>$device,
        ]);
        return $this->pixel();
    }
    private function pixel(){header('Content-Type: image/gif');header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');echo base64_decode('R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==');exit;}
}

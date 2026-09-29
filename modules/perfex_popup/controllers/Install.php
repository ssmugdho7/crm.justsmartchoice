<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Install extends ClientsController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('popup_model');
        $this->load->model('roles_model');
        $this->load->model('staff_model');
    }

    public function index()
    {
        $key = $this->input->get('key');
        if (!isset($key)) {
            show_404(); die;
        }

        $popup = $this->popup_model->get_with_key($key);
        if (!isset($popup)) {
            show_404(); die;
        }

        header("Content-Type: application/javascript");
        header("Expires: ".gmdate('D, d M Y H:i:s', time() + 60) . ' GMT');
        header("Pragma: cache");
        header("Cache-Control: max-age=60");

        $data['popup']                 = $popup;
        $this->load->view('install/javascript', $data);

    }
    public function install_iframe($key)
    {
        $popup = $this->popup_model->get_with_key($key);
        if (!isset($popup)) {
            show_404(); die;
        }
        // Add div parent
        $popup->html = '<div id="tfg-popup-wrapper">' . $popup->html . "</div>";
        $popup->thank_you_html = '<div id="tfg-popup-wrapper">' . $popup->thank_you_html . "</div>";
        $popup->settings = json_decode($popup->settings);
        $data['popup']                 = $popup;
        $this->load->view('install/iframe', $data);
    }

    public function collect()
    {
        try {

            $stream_clean = $this->security->xss_clean($this->input->raw_input_stream);
            $request = json_decode($stream_clean);

            if (!isset($request->key)) {
                header('Content-Type: application/json');
                echo json_encode(['error'=> 'Not found key']); die;
            }
            $popup = $this->popup_model->get_with_key($request->key);
            if (!$popup) {
                header('Content-Type: application/json');
                echo json_encode(['error'=> 'Not found popup']); die;
            }

            $popup_data = $request->data;
            $data_insert = [
                'popup_id' => $popup->id,
                'data' => json_encode($popup_data),
                'url' => $request->url,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            $this->db->insert(db_prefix() . 'popups_subscribers', $data_insert);
            $insert_id = $this->db->insert_id();
            if ($insert_id) {
                        
                log_activity('New subscriber submit from popup: '.$popup->name.': [Data ID:' . $insert_id . ']');
                
                // notification
                $this->popup_model->subscriber_submit_notification($popup);
                
                header('Content-Type: application/json');
                echo json_encode(['success'=> 'success']); die;
            }
            else{
                header('Content-Type: application/json');
                echo json_encode(['error'=> 'Insert data failed']); die;
            }
        }
          
        //catch exception
        catch(Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['error'=> $e->getMessage()]); die;
        }
        
    }
    
    public function tracking()
    {
        $key = $request->input('key');
        $type = $request->input('type');
        $url = $request->input('url');
        $popup = Popup::where('popup_key', '=', $key)->with(['user'])->firstOrFail();

        TrackPopup::create([
            'user_id' => $popup->user->id,
            'popup_id' => $popup->id,
            'type' => $type,
            'url' => $url,
        ]);

        return response()->json([]);
    }

   
    
}

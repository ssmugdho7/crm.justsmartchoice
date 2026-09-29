<?php

defined('BASEPATH') || exit('No direct script access allowed');

/**
 * Flows Controller
 *
 * Handles operations related to WhatsApp flows.
 */
class Flows extends AdminController {
    use modules\whatsbot\traits\Whatsapp; // Uses a trait for WhatsApp related methods

    /**
     * Constructor
     *
     * Initializes the controller and checks module activation status.
     */
    public function __construct() {
        parent::__construct();

        // Check if the whatsbot module is inactive; deny access if so
        $this->app_modules->is_inactive('whatsbot') ? access_denied() : '';

        $this->load->model('whatsbot_model'); // Load the WhatsApp bot model
    }

    /**
     * Index method
     *
     * Loads the main view for WhatsApp flows management.
     */
    public function index() {
        // Check if user has permission to view WhatsApp flows
        if (!staff_can('view', 'wtc_template')) {
            access_denied();
        }

        $viewData['title'] = _l('flows'); // Set view title

        $this->load->view('flows', $viewData); // Load flows view
    }

    /**
     * Get Table Data method
     *
     * Retrieves data for the flows table via AJAX.
     *
     * @return bool Returns false if the request is not an AJAX request.
     */
    public function get_table_data($table = 'flows', $flow_id = "") {
        if (!$this->input->is_ajax_request()) {
            return false;
        }

        $this->app->get_table_data(module_views_path(WHATSBOT_MODULE, 'tables/'. $table), compact('flow_id')); // Get table data
    }

    /**
     * Load Flows method
     *
     * Loads WhatsApp flows asynchronously.
     *
     * @return bool Returns false if the request is not an AJAX request or if the user lacks permission.
     */
    public function load_flows() {
        if (!$this->input->is_ajax_request() && !staff_can('load_template', 'wtc_template')) {
            return false;
        }

        $response = $this->whatsbot_model->load_flows(); // Call model method to load flows

        if (false == $response['success']) {
            // If loading flows fails, return error response
            echo json_encode([
                'success' => $response['success'],
                'type' => $response['type'],
                'message' => $response['message'],
            ]);
            exit();
        }

        // If flows are loaded successfully, return success response
        echo json_encode([
            'success' => true,
            'type' => 'success',
            'message' => _l('template_data_loaded'),
        ]);
    }

    function get_preview($flow_id) {
        $flow_data = $this->whatsbot_model->get_flow($flow_id);
        if($flow_data->preview_expiry <= time()){
            $preview = $this->getFlowPreview($flow_id);
            $this->whatsbot_model->update_flow($flow_id, ["preview_url" => $preview['preview'], "preview_expiry" => $preview['expires_at']]);
        }
        echo $preview['preview'] ?? $flow_data->preview_url;
    }

    public function flows_statistics($flow_id) {
        $data['flow'] = $this->whatsbot_model->get_flow($flow_id);
        $data['flow']->responses = $this->whatsbot_model->get_flow_res($flow_id);
        $this->load->view('flows_view', $data);
    }

    public function flow_review($res_id) {
        $res_flow = $this->whatsbot_model->get_flow_res("", $res_id);
        $response_data = json_decode($res_flow->response_data, true);
        $flow_data = $this->whatsbot_model->get_flow($res_flow->flow_id);
        $flow_json = json_decode($flow_data->flow_json);
        $html = "";
        foreach ($flow_json->screens as $screen) {
            $html .= '<div class="panel_s panel-default" style="border: 2px solid #cbd5e1">';
                $html .= '<div class="panel-heading">';
                    $html .= '<div class="panel-title">';
                        $html .= $screen->title;
                    $html .= '</div>';
                $html .= '</div>';
                $html .= '<div class="panel-body padding-5">';
                    // $html .= '<p class="tw-font-medium tw-flex tw-items-center tw-mb-0 tw-space-x-1.5 rtl:tw-space-x-reverse tw-p-1.5">';
                    //     $html .= '<span class="tw-text-neutral-700">';
                    //         $html .= $screen->title;
                    //     $html .= '</span>';
                    // $html .= '</p>';
                    // $html .= '<hr class="-tw-mx-3 tw-mt-3 tw-mb-0">';
                    $html .= '<div class="tw-rounded-md tw-bg-white">';
                        $html .= '<div class="tw-px-4 tw-py-5 sm:tw-px-4 sm:tw-py-2">';
                            foreach ($screen->layout->children ?? [] as $form) {
                                foreach($form->children as $form_fields){
                                    if(!empty($form_fields->{"on-click-action"})){
                                        continue;
                                    }
                                    if(!empty($form_fields->text)){
                                        $html .= '<dt class="tw-font-medium text-warning tw-mt-5">'.$form_fields->text.'</dt>';
                                    }
                                    if(!empty($form_fields->label)){
                                        $html .= '<dd class="tw-mt-1 tw-items-baseline tw-justify-between md:tw-block" style="display: block ruby">';
                                            $html .= '<span class="tw-flex tw-items-baseline tw-text-base tw-font-semibold tw-text-primary-600">'.$form_fields->label.':</span> &nbsp;';
                                            if(isset($form_fields->{"data-source"})){
                                                $data_source = array_column($form_fields->{"data-source"},"title", 'id');
                                                $html .= $data_source[$response_data[$form_fields->name]] ?? '';
                                            } else {
                                                $html .= $response_data[$form_fields->name] ?? '';
                                            }
                                        $html .= "</dd>";
                                    }
                                }
                            }
                        $html .= '</div>';
                    $html .= '</div>';
                $html .= '</div>';
            $html .= '</div>';
        }
        echo $html;
    }
}

<?php
public function updatebuilder($code, $type = 'sales-page')
{

    if ($this->input->post()) {
        $type_arr = array('sales-page', 'checkout-page' ,'thank-you-page');
        
        if (!in_array($type, $type_arr)) {
            echo json_encode(['error'=>_l('not_found_type')]); die;
        }

        if ($code) {    
            
            $this->db->where('code', $code);
            $item = $this->db->get(db_prefix() . 'sales_page')->row();

            $data = [];

            if ($item) {

                if ($type == 'thank-you-page') {

                    $data['thank_you_page_components'] = $this->input->post('gjs-components');
                    $data['thank_you_page_styles'] = $this->input->post('gjs-styles');
                    $data['thank_you_page_html'] = $this->input->post('gjs-html');
                    $data['thank_you_page_css'] = $this->input->post('gjs-css');
                }
                elseif($type == 'checkout-page'){

                    $data['checkout_page_components'] = $this->input->post('gjs-components');
                    $data['checkout_page_styles'] = $this->input->post('gjs-styles');
                    $data['checkout_page_html'] = $this->input->post('gjs-html');
                    $data['checkout_page_css'] = $this->input->post('gjs-css');

                }
                else{

                    $data['sales_page_components'] = $this->input->post('gjs-components');
                    $data['sales_page_styles'] = $this->input->post('gjs-styles');
                    $data['sales_page_html'] = $this->input->post('gjs-html');
                    $data['sales_page_css'] = $this->input->post('gjs-css');
                    $data['sales_page_script'] = $this->input->post('sales_page_script');
                    
                }
                // update
                $this->db->where('code', $code);
                $this->db->update(db_prefix() . 'sales_page', $data);

                header('Content-Type: application/json');
                echo json_encode(['success'=> _l('updated_successfully')]); die;
            }
            else{
                header('Content-Type: application/json');
                echo json_encode(['error'=> _l('not_found_code')]); die;
            }
            
        }
        header('Content-Type: application/json');
        echo json_encode(['error'=> _l('fail')]); die;

    }else {
        header('Content-Type: application/json');
        echo json_encode(['error'=> _l('fail')]); die;
    }

}
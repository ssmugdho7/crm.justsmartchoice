<?php
defined('BASEPATH') or exit('No direct script access allowed');
hooks()->add_action('after_email_templates', 'add_spreadsheet_share_email_templates');

/**
 * Get all staff by department
 * @param  string $departmentid Optional
 * @return array
 */
function get_all_staff_by_department($departmentid) {
	$CI = &get_instance();
	if ($departmentid) {
		$CI->db->where('departmentid', $departmentid);
		$staffids = $CI->db->select('staffid')->from(db_prefix() . 'staff_departments')->get()->result_array();
	} else {
		$staffids = [];
	}

	return $staffids;
}

/**
 * Get all client by group
 * @param  string $groupid Optional
 * @return array
 */
function get_all_client_by_group($groupid) {
	$CI = &get_instance();
	if ($groupid) {
		$CI->db->where('groupid', $groupid);
		$clientids = $CI->db->get(db_prefix() . 'customer_groups')->result_array();
	} else {
		$clientids = [];
	}
	return $clientids;
}

if (!function_exists('add_spreadsheet_share_email_templates')) {
	/**
	 * Init appointly email templates and assign languages
	 * @return void
	 */
	function add_spreadsheet_share_email_templates() {
		$CI = &get_instance();

		$data['spreadsheet_share'] = $CI->emails_model->get(['type' => 'spreadsheet_online', 'language' => 'english']);

		$CI->load->view('spreadsheet_online/email_templates', $data);
	}
}

/**
 * { email staff }
 *
 * @param        $staffid  The staffid
 *
 * @return      ( description_of_the_return_value )
 */
function spreadsheet_email_staff($staffid) {
	$CI = &get_instance();
	$CI->db->where('staffid', $staffid);
	return $CI->db->get(db_prefix() . 'staff')->row()->email;
}

/**
 * replace spreadsheet value
 * @param  $string
 * @return string
 */
function replace_spreadsheet_value($string) {
	$findme = 'images';
	$pos = strpos($string, $findme);
	$data_string = "";
	if ($pos) {
		$data_string = str_replace('""', '"', $string);
	} else {
		$data_string = $string;
	}

	$data_string = str_replace('"color":",', '"color":"",', $data_string);
	$data_string = str_replace('"value2":",', '"value2":"",', $data_string);

	$data_string = str_replace('"suffix":","optimize":', '"suffix":"","optimize":', $data_string);
	$data_string = str_replace('"suffix":","ratio":', '"suffix":"","ratio":', $data_string);
	$data_string = str_replace('"prefix":","suffix":', '"prefix":"","suffix":', $data_string);
	$data_string = str_replace('"text":","label":', '"text":"","label":', $data_string);
	$data_string = str_replace('"text":","nameGap":', '"text":"","nameGap":', $data_string);
	$data_string = str_replace('"data":["],"type":', '"data":[""],"type":', $data_string);
	$data_string = str_replace('[["]]', '[[""]]', $data_string);

	$data_string = str_replace('"series":[["]]', '"series":[[""]]', $data_string);
	$data_string = str_replace('\"}]},"fs":', '\""}]},"fs":', $data_string);
	$data_string = str_replace('\"}]},"bg":', '"}]},"bg":', $data_string);
	$data_string = str_replace('"v":"}],"', '"v":""}],"', $data_string);
	$data_string = str_replace('"v":"},{"r"', '"v":""},{"r"', $data_string);
	$data_string = str_replace('"m":","bg', '"m":"","bg', $data_string);
	$data_string = str_replace('"ff":""Times New Roman""', '"ff":"Times New Roman"', $data_string);
	$data_string = str_replace('":"},', '":""},', $data_string);
	$data_string = str_replace('":"}},', '":""}},', $data_string);
	$data_string = str_replace('"\'', '"\\\'', $data_string);
	$data_string = str_replace(':"\"', ':"', $data_string);
	$data_string = str_replace('\","fc', '","fc', $data_string);
	$data_string = str_replace('"fa":"$\"#,', '"fa":"$#,', $data_string);
	$data_string = str_replace(':"}}},{"', ':""}}},{"', $data_string);

	$data_string = str_replace('\","', '","', $data_string);
	$data_string = str_replace(')\","', ')","', $data_string);
	$data_string = str_replace('":"}}}]', '":""}}}]', $data_string);
	$data_string = str_replace('":"}}]', '":""}}]', $data_string);
	$data_string = str_replace('\\" #', ' #', $data_string);
	$data_string = str_replace('€\\"', '€', $data_string);
	
	return $data_string;
}
/**
 * file force contents
 * @param string $filename <p>file name including folder.
 * example :: /path/to/file/filename.ext or filename.ext</p>
 * @param string $data <p> The data to write.
 * </p>
 * @param int $flags same flags used for file_put_contents.
 * @return bool <b>TRUE</b> file created succesfully <br> <b>FALSE</b> failed to create file.
 */
function file_force_contents($filename, $data, $flags = 0) {
	if (!is_dir(dirname($filename))) {
		mkdir(dirname($filename) . '/', 0777, TRUE);
	}

	return file_put_contents($filename, $data, $flags);
}

function process_file($id) {
	$CI = &get_instance();
	$CI->load->model('spreadsheet_online/spreadsheet_online_model');
	$file_excel = $CI->spreadsheet_online_model->get_file_sheet($id);
	if($file_excel){
		$path = SPREAD_ONLINE_MODULE_UPLOAD_FOLDER . '/spreadsheet_online/' . $id . '-' . $file_excel->name . '.txt';
		$realpath_data = '/spreadsheet_online/' . $id . '-' . $file_excel->name . '.txt';
		$data = [];
		if (!file_exists(SPREAD_ONLINE_MODULE_UPLOAD_FOLDER . $file_excel->realpath_data) && $file_excel->data_form != NULL && $file_excel->data_form != '') {
			file_force_contents($path, $file_excel->data_form);
			$CI->db->where('id', $id);
			$CI->db->update(db_prefix() . 'spreadsheet_online_my_folder', ['realpath_data' => $realpath_data]);
			$mystring = file_get_contents($path, true);


			return ['data_form' => replace_spreadsheet_value($mystring), 'name' => $file_excel->name];
		} elseif (file_exists(SPREAD_ONLINE_MODULE_UPLOAD_FOLDER . $file_excel->realpath_data)) {
			$mystring = file_get_contents(SPREAD_ONLINE_MODULE_UPLOAD_FOLDER . $file_excel->realpath_data, true);
			return ['data_form' => replace_spreadsheet_value($mystring), 'name' => $file_excel->name];
		} else {
			
		}
	}

	return [];
}

function convert_old_share() {
	$CI = &get_instance();

	$convert_old_share = get_option('spreadsheet_online_convert_old_share');
	if($convert_old_share == 0){
		$hash_shares = $CI->db->get(db_prefix() . 'spreadsheet_online_hash_share')->result_array();

		foreach($hash_shares as $share){
			if($share['rel_id'] == 0){
				continue;
			}

			$data_share = [];
			$data_share['type'] = $share['rel_type'];
			$data_share['is_read'] = 1;
			if($share['role'] == 2){
				$data_share['is_write'] = 1;
			}

			$CI->db->where('id', $share['id']);
			$CI->db->update(db_prefix() . 'spreadsheet_online_hash_share', $data_share);

			$rel_type = $share['rel_type'];
			if($share['rel_type'] == 'client'){
				$rel_type = 'customer';
			}

			$CI->db->insert(db_prefix() . 'spreadsheet_online_sharing_details', ['share_id' => $share['id'], 'type' => $rel_type, 'value' => $share['rel_id'], ]);
		}

		update_option('spreadsheet_online_convert_old_share', 1);
	}

	return true;
}

/**
 * handle commmodity list add edit file
 * @param  integer $id
 * @return boolean
 */
function handle_spreadsheet_add_file($id){

    if (isset($_FILES['upload']['name']) && $_FILES['upload']['name'] != '') {
        $path = SPREAD_ONLINE_MODULE_UPLOAD_FOLDER.'/spreadsheet_online/'. $id . '-';
        // Get the temp file path
        $tmpFilePath = $_FILES['upload']['tmp_name'];
        // Make sure we have a filepath
        if (!empty($tmpFilePath) && $tmpFilePath != '') {
            _maybe_create_upload_path($path);
            $filename    = unique_filename($path, $_FILES['upload']['name']);
            $newFilePath = $path . $filename;
            // Upload the file into the company uploads dir
            if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                // $CI           = & get_instance();
                // $attachment   = [];
                // $attachment[] = [
                //     'file_name' => $filename,
                //     'filetype'  => $_FILES['upfile']['type'],
                // ];
                // $CI->misc_model->add_attachment_to_database($id, 'commodity_item_file', $attachment);

                return true;
            }
        }
    }

    return false;
}
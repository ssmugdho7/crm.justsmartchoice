<?php

defined('BASEPATH') or exit('No direct script access allowed');

class switchclass
{
    public function switch_html($status, $name, $id, $input_attr = '')
    {
        $checked = $status == 1 ? "checked" : "";

        $input_attr = $input_attr != '' ? $input_attr : 'disabled';

        return '
        <div class="fs-permisstion-switch">
        <div class="form-group">
        <div class="checkbox checbox-switch switch-primary">
        <label class="swith-label">
        <input data-id="' . $id . '" type="checkbox" name="' . $name . '"  ' . $checked . ' ' . $input_attr . '/>
        <span></span>
        </label>
        </div>
        </div>
        </div>';
    }
}

/**
 * Class Spreadsheet_online
 */
class Spreadsheet_online extends AdminController {
	/**
	 * __construct
	 */
	public function __construct() {
		parent::__construct();
		$this->load->model('spreadsheet_online_model');
		$this->load->model('departments_model');
		$this->load->model('clients_model');
		$this->load->model('staff_model');
		hooks()->do_action('spreadsheet_online_init');
	}

	/**
	 * manage
	 * @return view
	 */
	public function manage() {
		$data['title'] = _l('spreadsheet_online');
		$data['tab'] = $this->input->get('tab');
		$data['departments'] = $this->departments_model->get();
		$data['staffs'] = $this->staff_model->get();
		$data['clients'] = $this->clients_model->get();
		$data['client_groups'] = $this->clients_model->get_groups();

		if ($data['tab'] == '') {
			$data['tab'] = 'my_folder';
		}
		if ($data['tab'] == 'my_folder') {
			$data['folder_my_tree'] = $this->spreadsheet_online_model->tree_my_folder();
		}
		if ($data['tab'] == 'my_share_folder') {
			$data['folder_my_share_tree'] = $this->spreadsheet_online_model->tree_my_folder_share();
		}
		$this->load->view('manage', $data);
	}

	/**
	 * Add edit folder
	 */
	public function add_edit_folder() {
		if ($this->input->post()) {
			$data = $this->input->post();
			if ($data['id'] == '') {
				$id = $this->spreadsheet_online_model->add_folder($data);
				if (is_numeric($id)) {
					$message = _l('added_successfully');
					set_alert('success', $message);
				} else {
					$message = _l('added_fail');
					set_alert('warning', $message);
				}
			} else {
				$res = $this->spreadsheet_online_model->edit_folder($data);
				if ($res == true) {
					$message = _l('updated_successfully');
					set_alert('success', $message);
				} else {
					$message = _l('updated_fail');
					set_alert('warning', $message);
				}
			}
			redirect(admin_url('spreadsheet_online/manage?tab=my_folder'));
		}
	}
	/**
	 * new file view
	 * @param  int $parent_id
	 * @param  int $id
	 * @return  view or json
	 */
	public function new_file_view($parent_id, $id = "") {
		$data_form = $this->input->post();
		$data['title'] = _l('new_file');
		$data['parent_id'] = $parent_id;
		$data['folder'] = $this->spreadsheet_online_model->get_my_folder_all();
		$data['role'] = "";
		$data['departments'] = $this->departments_model->get();
		$data['staffs'] = $this->staff_model->get();
		$data['clients'] = $this->clients_model->get();
		$data['client_groups'] = $this->clients_model->get_groups();
		if (isset($data_form['id'])) {
			if (isset($data_form['type'])) {
				$type = $data_form['type'];
				if ($type == 2) {
					$data_form['id'] = '';
				}
				unset($data_form['type']);
			}
			if ($data_form['id'] == "") {
				if ($data_form['id'] == "") {
					$success = $this->spreadsheet_online_model->add_file_sheet($data_form);
					if (is_numeric($success)) {
						$message = _l('added_successfully');
						$file_excel = $this->spreadsheet_online_model->get_file_sheet($success);

						echo json_encode(['success' => true, 'message' => $message, 'is_create' => true, 'name_excel' => html_entity_decode($file_excel->name), 'parent_id' => $file_excel->parent_id, 'id' => $file_excel->id]);
						die;
					} else {
						$message = _l('added_fail');
						echo json_encode(['success' => false, 'message' => $message]);
						die;
					}
				}
			}
			if ($data_form['id'] != "") {

				if (isset($data_form['id'])) {
					if ($data_form['id'] != "") {
						$data['id'] = $data_form['id'];
					}
				} else {
					$data['id'] = $id;
					$data['file_excel'] = $this->spreadsheet_online_model->get_file_sheet($data['id']);
					$path = SPREAD_ONLINE_MODULE_UPLOAD_FOLDER . html_entity_decode($data['file_excel']->realpath_data);
					$mystring = file_get_contents($path, true);
					$data['data_form'] = replace_spreadsheet_value($mystring);
				}

				if ($data_form && $data_form['id'] != "") {
					$success = $this->spreadsheet_online_model->edit_file_sheet($data_form);
					if ($success == true) {
						$message = _l('updated_successfully');
						echo json_encode(['success' => $success, 'message' => $message]);
						die;
					} else {
						$message = _l('updated_fail');
						echo json_encode(['success' => $success, 'message' => $message]);
						die;
					}
				}
			}
		}

		$data['realpath_data'] = '';

		if ($id != '') {
			$data['id'] = $id;
			//process handle file
			$data_file = process_file($id);
			$data['file_excel'] = $this->spreadsheet_online_model->get_file_sheet($id);
			
			if(!$data['file_excel']){
				access_denied('file_not_found');
			}
			
			$data['realpath_data'] = html_entity_decode($data['file_excel']->realpath_data).'?t='.time();

			if (isset($data_file['data_form'])) {
				$data['data_form'] = $data_file['data_form'];
				$data['name'] = $data_file['name'];

			}
		}
		$data['tree_save'] = json_encode($this->spreadsheet_online_model->get_folder_tree());
		$this->db->select('id, parent_id');
		$this->db->where('parent_id', '');
		$folder_save_empty_parent = $this->db->get(db_prefix() . 'spreadsheet_online_my_folder')->result_array();
		if (count($folder_save_empty_parent) > 0) {
			foreach ($folder_save_empty_parent as $f_value) {
				$this->db->where('id', $f_value['id']);
				$this->db->update(db_prefix() . 'spreadsheet_online_my_folder', ['parent_id' => 0]);
			}
		}
		if (!isset($success)) {
			$this->load->view('new_file_view', $data);
		}
	}
	/**
	 * delete folder file
	 * @param  int $id
	 * @return  json
	 */
	public function delete_folder_file($id) {
		$success = false;
		$message = _l('deleted_fail');
		if ($id == 1) {
			echo json_encode(['success' => false, 'message' => _l('cannot_deleted_root_directory')]);
			die;
		} else {
			if ($id != '') {
				$success = $this->spreadsheet_online_model->delete_folder_file($id);
				$message = _l('deleted');
			}
			echo json_encode(['success' => $success, 'message' => $message]);
			die;
		}
	}
	/**
	 * get file sheet
	 * @param  int $id
	 * @return  json
	 */
	public function get_file_sheet($id) {
		$data = $this->spreadsheet_online_model->get_file_sheet($id);
		$path = SPREAD_ONLINE_MODULE_UPLOAD_FOLDER . html_entity_decode($data->realpath_data);
		$main_dl = file_get_contents($path, true);
		$data_form = replace_spreadsheet_value($main_dl);
		echo json_encode($data_form);
		die;
	}
	/**
	 * get folder zip
	 * @param   int $id
	 * @param   string $name
	 * @return  json
	 */
	public function get_folder_zip($id, $name) {
		echo json_encode($this->spreadsheet_online_model->get_folder_zip($id, $name));
		die;
	}
	/**
	 * update share spreadsheet online
	 * @return redirect
	 */
	public function update_share_spreadsheet_online() {
		$data = $this->input->post();
		$success = $this->spreadsheet_online_model->update_share($data);

		$staff_notification = get_option('spreadsheet_staff_notification');
		$staff_sent_email = get_option('spreadsheet_email_templates_staff');
		$client_notification = get_option('spreadsheet_client_notification');
		$client_sent_email = get_option('spreadsheet_email_templates_client');

		if ($success == true) {
			$message = _l('updated_successfully');
			set_alert('success', $message);
			if (isset($data['staffs_share']) && count($data['staffs_share']) > 0 && $data['type'] == 'staff') {
				if ($data['staffs_share'][0] != '') {
					foreach ($data['staffs_share'] as $key => $value) {
						$this->db->where('id', $data['id']);
						$share = $this->db->get(db_prefix() . 'spreadsheet_online_my_folder')->row();

						$share->receiver = spreadsheet_email_staff($value);
						$share->staff_share_id = $value;

						$share->type_template = "staff_template";

						if ($staff_sent_email == 1) {
							$template = mail_template('spreadsheet_share', 'spreadsheet_online', array_to_object($share));
							$template->send();
						}

						if ($staff_notification == 1) {
							$link = '';
							$link = 'spreadsheet_online/new_file_view/' . $share->parent_id . '/' . $share->id;
							$string_sub = get_staff_full_name($value) . ' ' . _l('share') . ' ' . $share->type . ' ' . $share->name . ' ' . _l('for_you');
							$this->spreadsheet_online_model->notifications($value, $link, strtolower($string_sub));
						}

					}
				}
			}

			if (isset($data['clients_share']) && count($data['clients_share']) > 0 && $data['type'] == 'client') {
				if ($data['clients_share'][0] != '') {
					foreach ($data['clients_share'] as $key => $value) {
						$this->db->where('id', $data['id']);
						$share = $this->db->get(db_prefix() . 'spreadsheet_online_my_folder')->row();

						$this->db->where('id', $value);
						$contact = $this->db->get(db_prefix() . 'contacts')->row()->email;

						if ($contact != null || $contact != '') {
							$share->receiver = $contact;
							$share->client_share_id = $value;
							$share->type_template = "client_template";
							if ($client_sent_email == 1) {
								$template = mail_template('spreadsheet_share_client', 'spreadsheet_online', array_to_object($share));
								$template->send();
							}

							// TODO client notification
							/*if ($client_notification == 1) {
								$link_client = '';
								$link_client = 'spreadsheet_online/new_file_view/' . $share->parent_id . '/' . $share->id;
								$string_sub = get_staff_full_name($value) . ' ' . _l('share') . ' ' . $share->type . ' ' . $share->name . ' ' . _l('for_you');
								$this->spreadsheet_online_model->notifications($value, $link_client, strtolower($string_sub));
							}
							*/
						}
					}
				}
			}
		} else {
			$message = _l('updated_fail');
			set_alert('warning', $message);
		}

		redirect(admin_url('spreadsheet_online/manage?tab=my_folder'));
	}

	/**
	 * new file view
	 * @param  int $parent_id
	 * @param  int $id
	 * @return  view or json
	 */
	public function file_view_share($parent_id, $id = "") {
		$data_form = $this->input->post();
		$data['tree_save'] = json_encode($this->spreadsheet_online_model->get_folder_tree());

		$file_excel = $this->spreadsheet_online_model->get_file_sheet($id);
		if (!$file_excel) {
			access_denied('spreadsheet_online');
		}
		$data['parent_id'] = $parent_id;

		$share_child = $this->spreadsheet_online_model->get_hash('staff', get_staff_user_id(), $id);
		if (!$share_child) {
			access_denied('spreadsheet_online');
		}

		if($share_child->is_write == 1){
			$data['role'] = 2;
		}else{
			$data['role'] = 1;
		}

		$data_form = $this->input->post();
		$data['title'] = _l('new_file');
		$data['folder'] = $this->spreadsheet_online_model->get_my_folder_all();
		if ($data_form || isset($data_form['id'])) {
			if ($data_form['id'] == "") {
				$success = $this->spreadsheet_online_model->add_file_sheet($data_form);
				if (is_numeric($success)) {
					$message = _l('added_successfully');
					$file_excel = $this->spreadsheet_online_model->get_file_sheet($success);
					echo json_encode(['success' => true, 'message' => $message, 'name_excel' => $file_excel->name]);
					die;
				} else {
					$message = _l('added_fail');
					echo json_encode(['success' => false, 'message' => $message]);
					die;
				}
			}
		}
		if ($id != "" || isset($data_form['id'])) {
			if (isset($data_form['id'])) {
				if ($data_form['id'] != "") {
					$data['id'] = $data_form['id'];
				}
			} else {
				$data['id'] = $id;
				// process hanlde file
				$data_file = process_file($id);
				if (isset($data_file['data_form'])) {
					$data['data_form'] = $data_file['data_form'];
					$data['name'] = $data_file['name'];
				}

			}

			if ($data_form && $data_form['id'] != "") {
				$success = $this->spreadsheet_online_model->edit_file_sheet($data_form);
				if ($success == true) {
					$message = _l('updated_successfully');
					echo json_encode(['success' => $success, 'message' => $message]);
					die;
				} else {
					$message = _l('updated_fail');
					echo json_encode(['success' => $success, 'message' => $message]);
					die;
				}
			}

			$data['file_excel'] = $this->spreadsheet_online_model->get_file_sheet($id);
			$data['realpath_data'] = html_entity_decode($data['file_excel']->realpath_data).'?t='.time();
		}
		if (!isset($success)) {
			$this->load->view('share_file_view', $data);
		}
	}
	/**
	 * get hash staff
	 * @param int $id
	 * @return json
	 */
	public function get_hash_staff($id) {
		$rel_id = get_staff_user_id();
		$rel_type = 'staff';
		echo json_encode($this->spreadsheet_online_model->get_hash($rel_type, $rel_id, $id));
		die;
	}

	/**
	 * get hash client
	 * @param int $id
	 * @return json
	 */
	public function get_hash_client($id) {
		$rel_id = get_client_user_id();
		$rel_type = 'client';
		echo json_encode($this->spreadsheet_online_model->get_hash($rel_type, $rel_id, $id));
		die;
	}
	/**
	 * get related
	 * @param  string $type
	 * @return json
	 */
	public function get_related($type = '', $id = '') {
		$rel_data = get_relation_data($type);
		$html_option = '';
		$html_option .= '<option value=""></option>';
		foreach ($rel_data as $key => $value) {
			$rel_val = get_relation_values($value, $type);
			$selected = $id == $rel_val['id'] ? "selected" : "";
			$html_option .= '<option value="' . $rel_val['id'] . '" ' . $selected . '>' . $rel_val['name'] . '</option>';
		}
		echo json_encode($html_option);
		die;
	}

	/**
	 * update related spreadsheet online
	 * @return redirect
	 */
	public function update_related_spreadsheet_online() {
		$data = $this->input->post();
		$success = $this->spreadsheet_online_model->update_related($data);

		if ($success == true) {
			$message = _l('updated_successfully');
			set_alert('success', $message);
		} else {
			$message = _l('updated_fail');
			set_alert('warning', $message);
		}
		redirect(admin_url('spreadsheet_online/manage?tab=my_folder'));
	}
	/**
	 * get share staff client
	 * @param  int $id
	 * @return json
	 */
	public function get_share_staff_client($id) {

		$data = $this->spreadsheet_online_model->get_share_detail($id);

		$html_staff = "";
		$html_client = "";
		if (count($data['staffs_share']) > 0) {
			foreach ($data['staffs_share'] as $key => $value) {
				$html_staff .= '
    			<tr>
    			<td>' . $value . '</td>
    			<td>' . ($data['staffs_role'][$key] == 1 ? "View" : "Edit") . '</td>
    			</tr>
    			';
			}
		}

		if (count($data['clients_share']) > 0) {
			foreach ($data['clients_share'] as $key => $value) {
				$html_client .= '
    			<tr>
    			<td>' . $value . '</td>
    			<td>' . ($data['clients_role'][$key] == 1 ? "View" : "Edit") . '</td>
    			</tr>
    			';
			}
		}
		echo json_encode(['staffs' => $html_staff, 'clients' => $html_client]);
		die;
	}
	/**
	 * [get_my_folder description]
	 * @param  [type] $id [description]
	 * @return [type]     [description]
	 */
	public function get_my_folder($id) {
		echo json_encode($this->spreadsheet_online_model->get_my_folder($id));
		die;
	}
	/**
	 * [get_my_folder_get_hash description]
	 * @param  [type] $rel_type [description]
	 * @param  [type] $rel_id   [description]
	 * @param  [type] $id_share [description]
	 * @return [type]           [description]
	 */
	public function get_my_folder_get_hash($rel_type, $rel_id, $id_share) {
		echo json_encode($this->spreadsheet_online_model->get_hash($rel_type, $rel_id, $id_share));
		die;
	}
	/**
	 * [append_value_department description]
	 * @param  [type] $id [description]
	 * @return [type]     [description]
	 */
	public function append_value_department($id) {
		$data = get_all_staff_by_department($id);
		$html = '';
		if (count($data) > 0) {
			$html .= '<option value=""></option>';
			foreach ($data as $key => $value) {
				$html .= '<option value="' . $value['staffid'] . '">' . get_staff_full_name($value['staffid']) . '</option>';
			}
		}
		echo json_encode($html);
		die;
	}
	/**
	 * [get_staff_all description]
	 * @return [type] [description]
	 */
	public function get_staff_all() {
		$staffs = $this->staff_model->get();
		$html = '';
		if (count($staffs) > 0) {
			$html .= '<option value=""></option>';
			foreach ($staffs as $key => $value) {
				$html .= '<option value="' . $value['staffid'] . '">' . get_staff_full_name($value['staffid']) . '</option>';
			}
		}
		echo json_encode($html);
		die;
	}
	/**
	 * [append_value_group description]
	 * @param  [type] $id [description]
	 * @return [type]     [description]
	 */
	public function append_value_group($id) {
		$data = get_all_client_by_group($id);
		$html = '';

		if (isset($data)) {
			if (count($data) > 0) {
				$html .= '<option value=""></option>';
				foreach ($data as $key => $value) {
					$client = $this->clients_model->get($value['customer_id']);
					$html .= '<option value="' . $client->userid . '">' . $client->company . '</option>';
				}
			}
		}
		echo json_encode($html);
		die;
	}
	/**
	 * [get_client_all description]
	 * @return [type] [description]
	 */
	public function get_client_all() {
		$client = $this->clients_model->get();
		$html = '';
		if (count($client) > 0) {
			$html .= '<option value=""></option>';
			foreach ($client as $key => $value) {
				$html .= '<option value="' . $value['userid'] . '">' . $value['company'] . '</option>';
			}
		}
		echo json_encode($html);
		die;
	}

	/**
	 * [get_client_all description]
	 * @return [type] [description]
	 */
	public function get_related_id($id) {
		$data = $this->spreadsheet_online_model->data_related_id($id);
		echo json_encode($data);
		die;
	}
	/**
	 * [droppable_event description]
	 * @param  [type] $id        [description]
	 * @param  [type] $parent_id [description]
	 * @return [type]            [description]
	 */
	public function droppable_event($id, $parent_id) {
		echo json_encode($this->spreadsheet_online_model->droppable_event($id, $parent_id));
		die;
	}

	/**
	 * get hash related
	 * @param int $id
	 * @return json
	 */
	public function get_hash_related($rel_id, $rel_type, $parent_id) {
		echo json_encode($this->spreadsheet_online_model->get_hash_related($rel_type, $rel_id, $parent_id));
		die;
	}

	/**
	 * file view related
	 * @param  int $hash
	 * @return  view or json
	 */
	public function file_view_share_related($hash = "") {
		$data_form = $this->input->post();
		$data['tree_save'] = json_encode($this->spreadsheet_online_model->get_folder_tree());

		if ($hash != "") {
			$share_child = $this->spreadsheet_online_model->get_share_form_hash_related($hash);
			$id = $share_child->parent_id;
			$file_excel = $this->spreadsheet_online_model->get_file_sheet($id);
			$data['parent_id'] = $file_excel->parent_id;
			$data['role'] = $share_child->role;
		} else {
			$id = "";
			$data['parent_id'] = "";
			$data['role'] = 1;
		}

		$data_form = $this->input->post();
		$data['title'] = _l('new_file');
		$data['folder'] = $this->spreadsheet_online_model->get_my_folder_all();
		if ($data_form || isset($data_form['id'])) {
			if ($data_form['id'] == "") {
				$success = $this->spreadsheet_online_model->add_file_sheet($data_form);
				if (is_numeric($success)) {
					$message = _l('added_successfully');
					$file_excel = $this->spreadsheet_online_model->get_file_sheet($success);
					echo json_encode(['success' => true, 'message' => $message, 'name_excel' => $file_excel->name]);
					die;
				} else {
					$message = _l('added_fail');
					echo json_encode(['success' => false, 'message' => $message]);
					die;
				}
			}
		}
		if ($id != "" || isset($data_form['id'])) {
			if (isset($data_form['id'])) {
				if ($data_form['id'] != "") {
					$data['id'] = $data_form['id'];
				}
			} else {
				$data['id'] = $id;
				// process hanlde file
				$data_file = process_file($id);
				if (isset($data_file['data_form'])) {
					$data['data_form'] = $data_file['data_form'];
					$data['name'] = $data_file['name'];
				}
			}

			if ($data_form && $data_form['id'] != "") {
				$success = $this->spreadsheet_online_model->edit_file_sheet($data_form);
				if ($success == true) {
					$message = _l('updated_successfully');
					echo json_encode(['success' => $success, 'message' => $message]);
					die;
				} else {
					$message = _l('updated_fail');
					echo json_encode(['success' => $success, 'message' => $message]);
					die;
				}
			}
		}
		if (!isset($success)) {
			$this->load->view('share_file_view', $data);
		}
	}

	public function spreadsheet_online_setting() {
		$data = $this->input->post();
		if (isset($data['spreadsheet_staff_notification'])) {
			if ($data['spreadsheet_staff_notification'] = 'on') {
				update_option('spreadsheet_staff_notification', 1);
			}
		} else {
			update_option('spreadsheet_staff_notification', 0);
		}

		if (isset($data['spreadsheet_email_templates_staff'])) {
			if ($data['spreadsheet_email_templates_staff'] = 'on') {
				update_option('spreadsheet_email_templates_staff', 1);
			}
		} else {
			update_option('spreadsheet_email_templates_staff', 0);
		}

		if (isset($data['spreadsheet_client_notification'])) {
			if ($data['spreadsheet_client_notification'] = 'on') {
				update_option('spreadsheet_client_notification', 1);
			}
		} else {
			update_option('spreadsheet_client_notification', 0);
		}

		if (isset($data['spreadsheet_email_templates_client'])) {
			if ($data['spreadsheet_email_templates_client'] = 'on') {
				update_option('spreadsheet_email_templates_client', 1);
			}
		} else {
			update_option('spreadsheet_email_templates_client', 0);
		}

		redirect(admin_url('spreadsheet_online/manage'));
	}

	public function check_file_exits($id_set) {
		if (is_numeric($id_set)) {
			$data = $this->spreadsheet_online_model->get_my_folder($id_set);
			if ($data->realpath_data != '' && $data->realpath_data != NULL) {
				if (!file_exists(SPREAD_ONLINE_MODULE_UPLOAD_FOLDER . html_entity_decode($data->realpath_data))) {
					echo json_encode(['message' => _l('physical_file_have_been_deleted'), 'success' => false]);
					die;
				} else {
					echo json_encode(['message' => "success", 'success' => true]);
					die;
				}
			} else {
				if ($data->realpath_data == '' && $data->data_form != NULL && $data->data_form != '') {
					$path = SPREAD_ONLINE_MODULE_UPLOAD_FOLDER . '/spreadsheet_online/' . $id_set . '-' . html_entity_decode($data->name) . '.txt';
					$realpath_data = '/spreadsheet_online/' . $id_set . '-' . html_entity_decode($data->name) . '.txt';
					file_force_contents($path, $data->data_form);
					$this->db->where('id', $id_set);
					$this->db->update(db_prefix() . 'spreadsheet_online_my_folder', ['realpath_data' => $realpath_data]);
					echo json_encode(['message' => "success", 'success' => true]);
					die;
				} else {
					echo json_encode(['message' => _l('physical_file_have_been_deleted'), 'success' => false]);
					die;
				}
			}
		} else {
			echo json_encode(['message' => _l('physical_file_have_been_deleted'), 'success' => false]);
			die;
		}
	}

	/**
 * sharing detail table
 * @return json
 */
public function sharing_detail_table()
{
    if ($this->input->is_ajax_request()) {

        $select = [
            'id',
            'hash',
            'type',
            'is_read',
            'is_write',
            'inserted_at',
        ];
        $where = [];
        if ($this->input->post('file_id')) {
            $file_id = $this->input->post('file_id');
            array_push($where, 'AND id_share = "' . $file_id . '"');
        }

        $aColumns     = $select;
        $sIndexColumn = 'id';
        $sTable       = db_prefix() . 'spreadsheet_online_hash_share';
        $join         = [];
        $result       = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, ['(SELECT GROUP_CONCAT(value SEPARATOR ",") FROM ' . db_prefix() . 'spreadsheet_online_sharing_details WHERE share_id=' . db_prefix() . 'spreadsheet_online_hash_share.id and type = "staff") as staffs', '(SELECT GROUP_CONCAT(value SEPARATOR ",") FROM ' . db_prefix() . 'spreadsheet_online_sharing_details WHERE share_id=' . db_prefix() . 'spreadsheet_online_hash_share.id and type = "department") as departments', '(SELECT GROUP_CONCAT(value SEPARATOR ",") FROM ' . db_prefix() . 'spreadsheet_online_sharing_details WHERE share_id=' . db_prefix() . 'spreadsheet_online_hash_share.id and type = "customer") as customers', '(SELECT GROUP_CONCAT(value SEPARATOR ",") FROM ' . db_prefix() . 'spreadsheet_online_sharing_details WHERE share_id=' . db_prefix() . 'spreadsheet_online_hash_share.id and type = "customer_group") as customer_groups',]);

        $output  = $result['output'];
        $rResult = $result['rResult'];
		$switch = new switchclass;

        foreach ($rResult as $aRow) {
            $row = [];

            $options    = '';
            $input_attr = 'disabled';
            //if (is_admin() || $aRow['created_at'] == get_staff_user_id()) {
                $options .= icon_btn('#', 'fa fa-edit', 'btn-default', [
                    'title'                       => _l('edit'),
                    'data-id'                     => $aRow['id'],
                    'data-type'               		=> $aRow['type'],
                    'data-is_read'               	=> $aRow['is_read'],
                    'data-is_write'              		=> $aRow['is_write'],
                    'data-staffs'                 => $aRow['staffs'],
                    'data-departments'                  => $aRow['departments'],
                    'data-customers'              => $aRow['customers'],
                    'data-customer_groups'        => $aRow['customer_groups'],
                    'onclick'                     => 'edit_sharing(this); return false;',
                ]);

                $options .= icon_btn('#', 'fa fa-remove', 'btn-danger', [
                    'title'   => _l('delete'),
                    'onclick' => 'delete_sharing(' . $aRow['id'] . '); return false;',
                ]);
                $input_attr = '';
            //}

            $row[]           = $aRow['hash'];
            $row[]           = _l($aRow['type']);

            $row[] = $switch->switch_html($aRow['is_read'], 'is_read', $aRow['id']);
    		$row[] = $switch->switch_html($aRow['is_write'], 'is_write', $aRow['id']);

            $row[]           = _d($aRow['inserted_at']);
            
            $row[]              = $options;
            $output['aaData'][] = $row;
        }

        echo json_encode($output);
        die();
    }
}
	
	/**
     * delete sharing
     * @param  integer $id
     * @return json
     */
    public function delete_sharing($id)
    {
        $success = $this->spreadsheet_online_model->delete_sharing($id);

        $message = _l('problem_deleting', _l('sharing'));

        if ($success) {
            $message = _l('deleted', _l('sharing'));
        }

        echo json_encode(['success' => $success, 'message' => $message]);
		die;
    }
}
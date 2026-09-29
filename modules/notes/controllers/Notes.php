<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Notes extends AdminController
{
    public $table_note = 'notes';

    public $rel_type_name = [
        'customer'      => 'client',
        'lead'          => 'lead',
        'contract'      => 'contract',
        'proposal'      => 'proposal',
        'invoice'       => 'invoice',
        'estimate'      => 'estimate',
        'staff'         => 'staff',
        'ticket'        => 'ticket',
        'project'       => 'project',
        'personal_note' => 'notes_personal_note',
    ];

    public $brand_colors = [
        '#00A651' => 'Green',
        '#007A3D' => 'Dark Green',
        '#42B95A' => 'Light Green',
        '#F96302' => 'Orange',
        '#FF7A00' => 'Bright Orange',
        '#F5B400' => 'Yellow',
        '#E59A00' => 'Amber',
        '#0077CC' => 'Blue',
        '#2CA8FF' => 'Light Blue',
        '#005FAF' => 'Deep Blue',
        '#555555' => 'Gray',
        '#111111' => 'Black',
    ];

    public function __construct()
    {
        parent::__construct();
        $this->table_note = db_prefix() . 'notes'; // Uses the original Perfex CRM notes table, normally tblnotes.
        $this->load->model('misc_model');
        $this->ensure_schema();

        $defaultColor = $this->normalize_hex(get_option('notes_default_color'));
        if (!isset($this->brand_colors[$defaultColor])) {
            $this->brand_colors = [$defaultColor => _l('notes_custom_default_color')] + $this->brand_colors;
        }
    }


    public function upgrade_database()
    {
        if (!is_admin()) {
            access_denied('notes');
        }

        require_once module_dir_path('notes', 'install.php');

        $modulesTable = db_prefix() . 'modules';
        if ($this->db->table_exists($modulesTable)) {
            $row = $this->db->where('module_name', 'notes')->get($modulesTable)->row();
            if ($row) {
                $this->db->where('module_name', 'notes')->update($modulesTable, [
                    'active' => 1,
                    'installed_version' => '1.2.5',
                ]);
            }
        }

        set_alert('success', 'Notes Database Upgraded Successfully');
        redirect(admin_url('notes/note'));
    }

    public function note()
    {
        if (!$this->can_manage_notes()) {
            access_denied('notes');
        }

        $data['title']        = _l('notes_smart_choice_notes');
        $data['rel_type']     = $this->get_sources();
        $data['note_types']   = $this->get_note_types();
        $data['brand_colors'] = $this->brand_colors;
        $data['staffs']       = $this->db->select("CONCAT(firstname, ' ', lastname) as fullname, staffid")
            ->from(db_prefix() . 'staff')
            ->where('active', 1)
            ->order_by('firstname', 'ASC')
            ->get()
            ->result();
        $data['notes_health'] = $this->build_health_summary();

        $this->load->view('manage_notes', $data);
    }

    public function note_lists()
    {
        if (!$this->can_manage_notes()) {
            ajax_access_denied();
        }

        if (!$this->db->table_exists($this->table_note)) {
            echo json_encode([
                'draw' => (int) $this->input->post('draw'),
                'iTotalRecords' => 0,
                'iTotalDisplayRecords' => 0,
                'aaData' => [],
            ]);
            die;
        }

        // Keep the listing query deliberately simple so every native Perfex note
        // in tblnotes is visible, regardless of relation type or legacy schema.
        $fields = $this->db->list_fields($this->table_note);
        $select = [
            'id',
            in_array('title', $fields) ? 'title' : "'' as title",
            'rel_type',
            'rel_id',
            'description',
            in_array('note_color', $fields) ? 'note_color' : "'#00A651' as note_color",
            in_array('priority', $fields) ? 'priority' : "'medium' as priority",
            in_array('attachment', $fields) ? 'attachment' : "'' as attachment",
            in_array('attachment_original_name', $fields) ? 'attachment_original_name' : "'' as attachment_original_name",
            in_array('dateadded', $fields) ? 'dateadded' : "NULL as dateadded",
        ];

        $where = [];
        $source = trim((string) $this->input->post('source'));
        $priority = trim((string) $this->input->post('priority'));
        $noteColor = trim((string) $this->input->post('note_color'));

        if ($source !== '') {
            $where[] = 'AND rel_type = ' . $this->db->escape($source);
        }
        if ($priority !== '' && in_array('priority', $fields)) {
            $where[] = 'AND priority = ' . $this->db->escape($priority);
        }
        if ($noteColor !== '' && in_array('note_color', $fields)) {
            $where[] = 'AND note_color = ' . $this->db->escape($noteColor);
        }

        $result = data_tables_init($select, 'id', $this->table_note, [], $where, []);
        $output = $result['output'];

        foreach ($result['rResult'] as $aRow) {
            $noteId = (int) $aRow['id'];
            $safeColor = $this->safe_brand_color($aRow['note_color'] ?? '#00A651');
            $title = trim((string) ($aRow['title'] ?? ''));
            if ($title === '') {
                $title = _l('notes_legacy_note') . ' #' . $noteId;
            }

            $row = [];
            $row[] = '<div class="checkbox"><input type="checkbox" class="note-export-checkbox" value="' . $noteId . '"><label></label></div>';
            $row[] = html_escape($title);
            $row[] = '<span class="label" style="background:' . html_escape($safeColor) . ';">' . html_escape($this->source_label($aRow['rel_type'] ?? '')) . '</span>';
            $row[] = $this->build_relation_output_safe($aRow['rel_type'] ?? '', (int) ($aRow['rel_id'] ?? 0));
            $row[] = $this->render_description($aRow['description'] ?? '', $noteId);
            $row[] = $this->priority_badge($aRow['priority'] ?? 'medium');
            $row[] = '<span class="note-color-dot note-color-dot-only" style="background:' . html_escape($safeColor) . ';" title="' . html_escape($safeColor) . '"></span>';
            $row[] = !empty($aRow['dateadded']) ? _dt($aRow['dateadded']) : '';
            $row[] = $this->attachment_link($noteId, $aRow['attachment'] ?? '', $aRow['attachment_original_name'] ?? '');

            $actions = '<div class="notes-action-grid">';
            $actions .= '<a class="btn btn-default btn-xs" href="' . admin_url('notes/notes/view/' . $noteId) . '" title="' . _l('view') . '"><i class="fa fa-eye"></i><span>' . _l('view') . '</span></a>';
            if (is_admin() || staff_can('edit', 'note_manage')) {
                $actions .= '<a class="btn btn-primary btn-xs" href="' . admin_url('notes/notes/edit/' . $noteId) . '" title="' . _l('edit') . '"><i class="fa fa-pencil"></i><span>' . _l('edit') . '</span></a>';
            }
            $actions .= '<button type="button" class="btn btn-info btn-xs" onclick="notes_share_one(' . $noteId . ');" title="' . _l('notes_share') . '"><i class="fa fa-share-alt"></i><span>' . _l('notes_share') . '</span></button>';
            if (is_admin() || staff_can('delete', 'note_manage')) {
                $actions .= '<button type="button" class="btn btn-danger btn-xs" onclick="notes_delete_one(' . $noteId . ');" title="' . _l('delete') . '"><i class="fa fa-trash"></i><span>' . _l('delete') . '</span></button>';
            }
            $actions .= '</div>';
            $row[] = $actions;
            $output['aaData'][] = $row;
        }

        echo json_encode($output);
        die;
    }

    public function add_note()
    {
        if (!$this->can_manage_notes()) {
            access_denied('notes');
        }

        if ($this->input->post()) {
            $this->ensure_schema();
            $rel_type = $this->input->post('rel_type');
            $rel_id = (int) $this->input->post('rel_id');
            $assigned_staff_id = (int) $this->input->post('assigned_staff_id');

            if ($rel_type === 'personal_note') {
                $rel_id = $assigned_staff_id > 0 ? $assigned_staff_id : get_staff_user_id();
            }

            if (empty($rel_type)) {
                $rel_type = 'customer';
            }

            $title = trim((string) $this->input->post('title'));
            if ($title === '') {
                set_alert('danger', _l('notes_title_required'));
                redirect($_SERVER['HTTP_REFERER'] ?? admin_url('notes/note'));
            }

            $description = $this->input->post('description', false);
            if (trim(strip_tags((string) $description)) === '') {
                set_alert('danger', _l('notes_note_not_added'));
                redirect($_SERVER['HTTP_REFERER'] ?? admin_url('notes/note'));
            }

            $fields = $this->db->list_fields($this->table_note);
            $insert = [
                'description' => $description,
                'rel_type'    => $rel_type,
                'rel_id'      => $rel_id,
                'dateadded'   => date('Y-m-d H:i:s'),
                'addedfrom'   => get_staff_user_id(),
            ];

            $optional = [
                'title'             => $title,
                'note_color'        => $this->safe_brand_color($this->input->post('note_color') ?: get_option('notes_default_color')),
                'priority'          => $this->safe_priority($this->input->post('priority')),
                'note_type'         => $this->safe_note_type($this->input->post('note_type')),
                'note_visibility'   => $this->safe_visibility($this->input->post('note_visibility')),
                'assigned_staff_id' => $assigned_staff_id > 0 ? $assigned_staff_id : null,
            ];

            foreach ($optional as $field => $value) {
                if (in_array($field, $fields)) {
                    $insert[$field] = $value;
                }
            }

            $success = $this->db->insert($this->table_note, $insert);
            $note_id = $success ? (int) $this->db->insert_id() : 0;

            if ($note_id > 0) {
                $upload = $this->handle_note_attachment($note_id);
                if (!empty($upload)) {
                    $update = [];
                    if (in_array('attachment', $fields)) {
                        $update['attachment'] = $upload['file_name'];
                    }
                    if (in_array('attachment_original_name', $fields)) {
                        $update['attachment_original_name'] = $upload['original_name'];
                    }
                    if (!empty($update)) {
                        $this->db->where('id', $note_id)->update($this->table_note, $update);
                    }
                }
                set_alert('success', _l('notes_created_successfully'));
                redirect(admin_url('notes/note'));
            } else {
                set_alert('danger', _l('notes_note_not_added'));
            }
        }

        redirect(admin_url('notes/note'));
    }

    public function note_module_model_get_detail()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $note_id = (int) $this->input->post('record_id');
        $note_detail = $this->db->where('id', $note_id)->get($this->table_note)->row();

        if (!empty($note_detail)) {
            $note_detail->description = check_for_links($note_detail->description);
            echo json_encode(['detail' => $note_detail]);
        }
    }

    public function sample_csv()
    {
        if (!$this->can_manage_notes()) {
            access_denied('notes');
        }

        $this->output_csv('smart_choice_notes_sample.csv', [[
            'title',
            'rel_type',
            'rel_id',
            'note_type',
            'description',
            'priority',
            'note_color',
            'note_visibility',
            'assigned_staff_id',
        ], [
            'Customer follow-up',
            'customer',
            '1',
            'general',
            'Customer follow-up note example',
            'medium',
            '#00A651',
            'normal',
            get_staff_user_id(),
        ]]);
    }

    public function export_csv()
    {
        if (!$this->can_manage_notes()) {
            access_denied('notes');
        }

        $notes = $this->get_export_notes();

        $rows = [[
            'id',
            'title',
            'rel_type',
            'rel_id',
            'description',
            'note_type',
            'priority',
            'note_color',
            'note_visibility',
            'assigned_staff_id',
            'attachment_original_name',
            'addedfrom',
            'dateadded',
        ]];

        foreach ($notes as $note) {
            $rows[] = $note;
        }

        $this->output_csv('smart_choice_notes_export_' . date('Ymd_His') . '.csv', $rows);
    }

    public function export_excel()
    {
        if (!$this->can_manage_notes()) {
            access_denied('notes');
        }

        $notes = $this->get_export_notes();
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="smart_choice_notes_export_' . date('Ymd_His') . '.xls"');
        echo '<table border="1">';
        echo '<tr><th>ID</th><th>Source</th><th>Relation ID</th><th>Description</th><th>Priority</th><th>Color</th><th>Assigned Staff ID</th><th>Added From</th><th>Date Added</th></tr>';
        foreach ($notes as $note) {
            echo '<tr>';
            echo '<td>' . html_escape($note['id']) . '</td>';
            echo '<td>' . html_escape($note['rel_type']) . '</td>';
            echo '<td>' . html_escape($note['rel_id']) . '</td>';
            echo '<td>' . html_escape(strip_tags($note['description'])) . '</td>';
            echo '<td>' . html_escape($note['priority'] ?? '') . '</td>';
            echo '<td>' . html_escape($this->brand_colors[$this->safe_brand_color($note['note_color'] ?? '')] ?? '') . '</td>';
            echo '<td>' . html_escape($note['assigned_staff_id'] ?? '') . '</td>';
            echo '<td>' . html_escape($note['addedfrom']) . '</td>';
            echo '<td>' . html_escape($note['dateadded']) . '</td>';
            echo '</tr>';
        }
        echo '</table>';
        exit;
    }

    public function export_pdf()
    {
        if (!$this->can_manage_notes()) {
            access_denied('notes');
        }

        $notes = $this->get_export_notes();
        $html = '<h3>Smart Choice Notes Export</h3><table border="1" cellpadding="5" cellspacing="0" width="100%">';
        $html .= '<tr><th>ID</th><th>Source</th><th>Description</th><th>Priority</th><th>Date Added</th></tr>';
        foreach ($notes as $note) {
            $html .= '<tr>';
            $html .= '<td>' . html_escape($note['id']) . '</td>';
            $html .= '<td>' . html_escape($note['rel_type']) . '</td>';
            $html .= '<td>' . html_escape(strip_tags($note['description'])) . '</td>';
            $html .= '<td>' . html_escape($note['priority'] ?? '') . '</td>';
            $html .= '<td>' . html_escape($note['dateadded']) . '</td>';
            $html .= '</tr>';
        }
        $html .= '</table>';

        if (function_exists('pdf')) {
            $pdf = pdf('Smart Choice Notes Export');
            $pdf->writeHTML($html, true, false, false, false, '');
            $pdf->Output('smart_choice_notes_export_' . date('Ymd_His') . '.pdf', 'D');
            exit;
        }

        header('Content-Type: text/html; charset=utf-8');
        header('Content-Disposition: attachment; filename="smart_choice_notes_export_' . date('Ymd_His') . '.html"');
        echo $html;
        exit;
    }

    public function import_csv()
    {
        if (!(is_admin() || staff_can('create', 'note_manage'))) {
            access_denied('notes');
        }

        if (empty($_FILES['import_file']['tmp_name'])) {
            set_alert('danger', _l('notes_import_file_required'));
            redirect(admin_url('notes/note'));
        }

        $handle = fopen($_FILES['import_file']['tmp_name'], 'r');
        if (!$handle) {
            set_alert('danger', _l('notes_import_failed'));
            redirect(admin_url('notes/note'));
        }

        $headers = fgetcsv($handle);
        $headers = is_array($headers) ? array_map('trim', $headers) : [];
        $created = 0;
        $skipped = 0;
        $fields = $this->db->list_fields($this->table_note);

        while (($row = fgetcsv($handle)) !== false) {
            if (count($headers) !== count($row)) { $skipped++; continue; }
            $data = array_combine($headers, $row);
            if (trim(strip_tags((string)($data['description'] ?? ''))) === '') { $skipped++; continue; }

            $relType = $this->safe_source($data['rel_type'] ?? 'personal_note');
            $relId = (int)($data['rel_id'] ?? 0);
            $assigned = (int)($data['assigned_staff_id'] ?? 0);
            if ($relType === 'personal_note') { $relId = $assigned > 0 ? $assigned : get_staff_user_id(); }

            $insert = [
                'description' => $data['description'],
                'rel_type' => $relType,
                'rel_id' => $relId,
                'dateadded' => date('Y-m-d H:i:s'),
                'addedfrom' => get_staff_user_id(),
            ];
            $optional = [
                'title' => trim((string)($data['title'] ?? 'Imported Note')),
                'note_type' => $this->safe_note_type($data['note_type'] ?? 'general'),
                'priority' => $this->safe_priority($data['priority'] ?? 'medium'),
                'note_color' => $this->safe_brand_color($data['note_color'] ?? '#00A651'),
                'note_visibility' => $this->safe_visibility($data['note_visibility'] ?? 'normal'),
                'assigned_staff_id' => $assigned > 0 ? $assigned : null,
            ];
            foreach ($optional as $field => $value) { if (in_array($field, $fields, true)) { $insert[$field] = $value; } }
            if ($this->db->insert($this->table_note, $insert)) { $created++; } else { $skipped++; }
        }
        fclose($handle);
        set_alert('success', _l('notes_imported_successfully') . ': ' . $created . ($skipped ? ' / ' . _l('notes_skipped') . ': ' . $skipped : ''));
        redirect(admin_url('notes/note'));
    }

    public function view($note_id)
    {
        if (!$this->can_manage_notes()) { access_denied('notes'); }
        $note = $this->db->where('id', (int)$note_id)->get($this->table_note)->row();
        if (!$note) { show_404(); }
        $data['title'] = !empty($note->title) ? $note->title : _l('note') . ' #' . (int)$note_id;
        $data['note'] = $note;
        $data['source'] = $this->get_source_config($note->rel_type);
        $data['type'] = $this->get_note_type_config($note->note_type ?? 'general');
        $data['relation'] = $this->build_relation_output_safe($note->rel_type, (int)$note->rel_id);
        $data['attachment'] = $this->attachment_link((int)$note_id, $note->attachment ?? '', $note->attachment_original_name ?? '');
        $this->load->view('view_note', $data);
    }

    public function edit($note_id)
    {
        if (!(is_admin() || staff_can('edit', 'note_manage'))) { access_denied('notes'); }
        $note = $this->db->where('id', (int)$note_id)->get($this->table_note)->row();
        if (!$note) { show_404(); }
        $data['title'] = _l('notes_edit_note');
        $data['note'] = $note;
        $data['rel_type'] = $this->get_sources();
        $data['note_types'] = $this->get_note_types();
        $data['brand_colors'] = $this->brand_colors;
        $data['staffs'] = $this->db->select("CONCAT(firstname, ' ', lastname) as fullname, staffid")->from(db_prefix().'staff')->where('active',1)->order_by('firstname','ASC')->get()->result();
        $this->load->view('edit_note', $data);
    }

    public function update_note($note_id)
    {
        if (!(is_admin() || staff_can('edit', 'note_manage'))) { access_denied('notes'); }
        $note_id = (int)$note_id;
        $note = $this->db->where('id', $note_id)->get($this->table_note)->row();
        if (!$note || !$this->input->post()) { show_404(); }
        $description = $this->input->post('description', false);
        $title = trim((string)$this->input->post('title'));
        if ($title === '' || trim(strip_tags((string)$description)) === '') {
            set_alert('danger', _l('notes_required_fields'));
            redirect(admin_url('notes/notes/edit/'.$note_id));
        }
        $fields = $this->db->list_fields($this->table_note);
        $update = ['description'=>$description, 'rel_type'=>$this->safe_source($this->input->post('rel_type')), 'rel_id'=>(int)$this->input->post('rel_id')];
        $optional = [
            'title'=>$title,
            'note_type'=>$this->safe_note_type($this->input->post('note_type')),
            'priority'=>$this->safe_priority($this->input->post('priority')),
            'note_color'=>$this->safe_brand_color($this->input->post('note_color')),
            'note_visibility'=>$this->safe_visibility($this->input->post('note_visibility')),
            'assigned_staff_id'=>(int)$this->input->post('assigned_staff_id') ?: null,
        ];
        if ($update['rel_type'] === 'personal_note') { $update['rel_id'] = $optional['assigned_staff_id'] ?: get_staff_user_id(); }
        foreach ($optional as $field=>$value) { if (in_array($field,$fields,true)) { $update[$field]=$value; } }
        $this->db->where('id',$note_id)->update($this->table_note,$update);
        $upload = $this->handle_note_attachment($note_id);
        if ($upload) {
            $attachmentUpdate=[];
            if (in_array('attachment',$fields,true)) $attachmentUpdate['attachment']=$upload['file_name'];
            if (in_array('attachment_original_name',$fields,true)) $attachmentUpdate['attachment_original_name']=$upload['original_name'];
            if ($attachmentUpdate) $this->db->where('id',$note_id)->update($this->table_note,$attachmentUpdate);
        }
        set_alert('success', _l('updated_successfully', _l('note')));
        redirect(admin_url('notes/notes/view/'.$note_id));
    }

    public function save_source()
    {
        if (!is_admin()) { access_denied('notes'); }
        $table = db_prefix() . 'notes_sources';
        $id = (int) $this->input->post('id');
        $key = preg_replace('/[^a-z0-9_]/', '_', strtolower(trim((string) $this->input->post('source_key'))));
        $name = trim((string) $this->input->post('name'));

        if ($key === '' || $name === '') {
            return $this->settings_response(false, _l('notes_settings_required'));
        }

        $duplicate = $this->db->where('source_key', $key);
        if ($id > 0) { $duplicate->where('id !=', $id); }
        if ($duplicate->count_all_results($table) > 0) {
            return $this->settings_response(false, _l('notes_source_key_exists'));
        }

        $data = [
            'source_key' => $key,
            'name'       => $name,
            'color'      => $this->normalize_hex($this->input->post('color')),
            'active'     => $this->input->post('active') ? 1 : 0,
            'sort_order' => (int) $this->input->post('sort_order'),
        ];
        $success = $id > 0
            ? $this->db->where('id', $id)->update($table, $data)
            : $this->db->insert($table, $data);

        return $this->settings_response((bool) $success, $success ? _l('notes_settings_saved') : _l('notes_settings_save_failed'));
    }

    public function delete_source($id)
    {
        if (!is_admin()) { access_denied('notes'); }
        $row = $this->db->where('id', (int) $id)->get(db_prefix() . 'notes_sources')->row();
        if ($row && empty($row->is_system)) {
            $this->db->where('id', (int) $id)->delete(db_prefix() . 'notes_sources');
        } elseif ($row) {
            $this->db->where('id', (int) $id)->update(db_prefix() . 'notes_sources', ['active' => 0]);
        }
        redirect(admin_url('settings?group=notes_settings'));
    }

    public function save_type()
    {
        if (!is_admin()) { access_denied('notes'); }
        $table = db_prefix() . 'notes_types';
        $id = (int) $this->input->post('id');
        $slug = preg_replace('/[^a-z0-9_]/', '_', strtolower(trim((string) $this->input->post('type_key'))));
        $name = trim((string) $this->input->post('name'));

        if ($slug === '' || $name === '') {
            return $this->settings_response(false, _l('notes_settings_required'));
        }

        $duplicate = $this->db->where('type_key', $slug);
        if ($id > 0) { $duplicate->where('id !=', $id); }
        if ($duplicate->count_all_results($table) > 0) {
            return $this->settings_response(false, _l('notes_type_key_exists'));
        }

        $data = [
            'type_key'  => $slug,
            'name'      => $name,
            'color'     => $this->normalize_hex($this->input->post('color')),
            'active'    => $this->input->post('active') ? 1 : 0,
            'sort_order'=> (int) $this->input->post('sort_order'),
        ];
        $success = $id > 0
            ? $this->db->where('id', $id)->update($table, $data)
            : $this->db->insert($table, $data);

        return $this->settings_response((bool) $success, $success ? _l('notes_settings_saved') : _l('notes_settings_save_failed'));
    }

    public function delete_type($id)
    {
        if (!is_admin()) { access_denied('notes'); }
        $row = $this->db->where('id', (int) $id)->get(db_prefix() . 'notes_types')->row();
        if ($row && empty($row->is_system)) {
            $this->db->where('id', (int) $id)->delete(db_prefix() . 'notes_types');
        } elseif ($row) {
            $this->db->where('id', (int) $id)->update(db_prefix() . 'notes_types', ['active' => 0]);
        }
        redirect(admin_url('settings?group=notes_settings'));
    }

    public function save_preferences()
    {
        if (!is_admin()) { access_denied('notes'); }
        $color = $this->normalize_hex($this->input->post('notes_default_color'));
        update_option('notes_default_color', $color);
        return $this->settings_response(true, _l('notes_settings_saved'));
    }

    public function share_link($noteId)
    {
        if (!$this->can_manage_notes()) { ajax_access_denied(); }
        $noteId = (int) $noteId;
        $note = $this->db->where('id', $noteId)->get($this->table_note)->row();
        if (!$note) {
            return $this->json_response(['success' => false, 'message' => _l('notes_note_not_found')]);
        }

        $token = !empty($note->share_token) ? (string) $note->share_token : bin2hex(random_bytes(24));
        $this->db->where('id', $noteId)->update($this->table_note, [
            'share_token'   => $token,
            'share_enabled' => 1,
        ]);

        return $this->json_response([
            'success' => true,
            'url'     => site_url('notes/share/' . $token),
            'message' => _l('notes_share_link_ready'),
        ]);
    }

    private function settings_response($success, $message)
    {
        if ($this->input->is_ajax_request()) {
            return $this->json_response(['success' => (bool) $success, 'message' => $message]);
        }
        set_alert($success ? 'success' : 'danger', $message);
        redirect(admin_url('settings?group=notes_settings'));
    }

    private function json_response(array $payload)
    {
        $this->output->set_content_type('application/json')->set_output(json_encode($payload));
        return null;
    }

    public function delete_note($note_id)
    {
        if (!(is_admin() || staff_can('delete', 'note_manage'))) {
            ajax_access_denied();
        }

        $note_id = (int) $note_id;
        $note = $this->db->where('id', $note_id)->get($this->table_note)->row();
        if (!$note) {
            echo json_encode(['success' => false, 'message' => _l('notes_note_not_found')]);
            return;
        }

        $this->delete_attachment_folder($note_id);
        $this->db->where('id', $note_id)->delete($this->table_note);
        echo json_encode(['success' => $this->db->affected_rows() > 0]);
    }

    public function bulk_delete()
    {
        if (!$this->can_manage_notes()) {
            ajax_access_denied();
        }

        $ids = $this->input->post('ids');
        $ids = is_array($ids) ? array_map('intval', $ids) : [];

        if (empty($ids)) {
            echo json_encode(['success' => false]);
            die;
        }

        foreach ($ids as $id) {
            $this->delete_attachment_folder($id);
        }

        $this->db->where_in('id', $ids)->delete($this->table_note);
        echo json_encode(['success' => true]);
        die;
    }

    public function download_attachment($note_id)
    {
        if (!$this->can_manage_notes()) {
            access_denied('notes');
        }

        $note = $this->db->where('id', (int) $note_id)->get($this->table_note)->row();
        if (empty($note) || empty($note->attachment)) {
            show_404();
        }

        $path = FCPATH . 'modules/notes/uploads/' . (int) $note_id . '/' . $note->attachment;
        if (!file_exists($path)) {
            show_404();
        }

        $this->load->helper('download');
        force_download($note->attachment_original_name ?: $note->attachment, file_get_contents($path));
    }

    public function note_relation_detail_add($rel_type)
    {
        $rel_type_link = [
            'lead'     => '?tab=note',
            'contract' => '?tab=note',
            'customer' => '?group=notes',
            'proposal' => '?tab=note',
            'ticket'   => '?tab=note',
            'invoice'  => '?tab=note',
            'estimate' => '?tab=note',
        ];

        return !empty($rel_type_link[$rel_type]) ? $rel_type_link[$rel_type] : '';
    }


    private function can_manage_notes()
    {
        if (function_exists('is_admin') && is_admin()) {
            return true;
        }

        if (function_exists('staff_can')) {
            foreach (['view', 'view_global', 'create', 'edit', 'delete'] as $capability) {
                if (staff_can($capability, 'note_manage')) {
                    return true;
                }
            }
        }

        if (function_exists('has_permission')) {
            foreach (['view', 'view_global', 'create', 'edit', 'delete'] as $capability) {
                if (has_permission('note_manage', '', $capability)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function ensure_schema()
    {
        if (!$this->db->table_exists($this->table_note)) {
            return;
        }

        $fields = $this->db->list_fields($this->table_note);
        $columns = [
            'title'                     => "ALTER TABLE `{$this->table_note}` ADD `title` VARCHAR(191) NULL AFTER `id`",
            'note_color'               => "ALTER TABLE `{$this->table_note}` ADD `note_color` VARCHAR(20) NULL DEFAULT '#00A651'",
            'priority'                 => "ALTER TABLE `{$this->table_note}` ADD `priority` VARCHAR(20) NULL DEFAULT 'medium'",
            'note_type'                => "ALTER TABLE `{$this->table_note}` ADD `note_type` VARCHAR(64) NULL DEFAULT 'general'",
            'note_visibility'          => "ALTER TABLE `{$this->table_note}` ADD `note_visibility` VARCHAR(20) NULL DEFAULT 'normal'",
            'assigned_staff_id'        => "ALTER TABLE `{$this->table_note}` ADD `assigned_staff_id` INT(11) NULL DEFAULT NULL",
            'attachment'               => "ALTER TABLE `{$this->table_note}` ADD `attachment` VARCHAR(255) NULL DEFAULT NULL",
            'attachment_original_name' => "ALTER TABLE `{$this->table_note}` ADD `attachment_original_name` VARCHAR(255) NULL DEFAULT NULL",
            'legacy_source_table'      => "ALTER TABLE `{$this->table_note}` ADD `legacy_source_table` VARCHAR(64) NULL DEFAULT NULL",
            'legacy_source_id'         => "ALTER TABLE `{$this->table_note}` ADD `legacy_source_id` INT(11) NULL DEFAULT NULL",
            'share_token'              => "ALTER TABLE `{$this->table_note}` ADD `share_token` VARCHAR(64) NULL DEFAULT NULL",
            'share_enabled'            => "ALTER TABLE `{$this->table_note}` ADD `share_enabled` TINYINT(1) NOT NULL DEFAULT 0",
        ];

        foreach ($columns as $column => $sql) {
            if (!in_array($column, $fields)) {
                $this->db->query($sql);
            }
        }
    }

    private function build_relation_output_safe($relType, $relId)
    {
        $relType = trim((string) $relType);
        $relId = (int) $relId;

        if ($relType === '' || $relId <= 0) {
            return '-';
        }

        if ($relType === 'personal_note' || $relType === 'staff') {
            $name = get_staff_full_name($relId);
            return $name !== '' ? html_escape($name) : _l('staff') . ' #' . $relId;
        }

        try {
            $relData = get_relation_data($relType, $relId);
            $relValues = !empty($relData) ? get_relation_values($relData, $relType) : [];
            $name = !empty($relValues['name']) ? $relValues['name'] : ucwords(str_replace('_', ' ', $relType)) . ' #' . $relId;
            $link = !empty($relValues['link']) ? $relValues['link'] . $this->note_relation_detail_add($relType) : '';
            return $link !== ''
                ? '<a href="' . html_escape($link) . '" target="_blank">' . html_escape($name) . '</a>'
                : html_escape($name);
        } catch (Throwable $exception) {
            return html_escape(ucwords(str_replace('_', ' ', $relType)) . ' #' . $relId);
        }
    }

    private function build_relation_output($aRow)
    {
        $rel_type = $aRow['rel_type'];
        $rel_id = (int) $aRow['rel_id'];

        if ($rel_type === 'personal_note') {
            $staff_id = !empty($aRow['assigned_staff_id']) ? (int) $aRow['assigned_staff_id'] : $rel_id;
            return $this->render_staff_avatar($staff_id) . ' ' . html_escape(get_staff_full_name($staff_id));
        }

        if ($rel_type === 'staff') {
            return $this->render_staff_avatar($rel_id) . ' ' . html_escape(get_staff_full_name($rel_id));
        }

        $rel_data = get_relation_data($rel_type, $rel_id);
        $rel_values = !empty($rel_data) ? get_relation_values($rel_data, $rel_type) : [];
        $rel_link = !empty($rel_values['link']) ? $rel_values['link'] . $this->note_relation_detail_add($rel_type) : '#';
        $rel_name = !empty($rel_values['name']) ? $rel_values['name'] : ucfirst(str_replace('_', ' ', $rel_type)) . ' #' . $rel_id;

        if ($rel_type === 'project' && $rel_link !== '#') {
            $rel_link .= '?group=note_project_notes';
        }

        return '<a href="' . html_escape($rel_link) . '" target="_blank">' . $this->render_relation_avatar($rel_type, $rel_id) . ' ' . html_escape($rel_name) . '</a>';
    }

    private function source_label($rel_type)
    {
        if (!empty($this->rel_type_name[$rel_type])) {
            return _l($this->rel_type_name[$rel_type]);
        }

        return ucwords(str_replace('_', ' ', $rel_type));
    }

    private function get_client_name($client_id)
    {
        if ($client_id <= 0) {
            return '';
        }

        $client = get_client($client_id);
        return !empty($client->company) ? $client->company : '';
    }

    private function render_description($description, $note_id)
    {
        $plain = strip_tags($description);
        if (mb_strlen($plain, 'UTF-8') > 75) {
            return '<a onclick="note_module_model_get_detail(' . (int) $note_id . ')" style="text-decoration:none;cursor:pointer;">' . html_escape(mb_substr($plain, 0, 75, 'UTF-8')) . '...</a>';
        }

        return html_escape($plain);
    }

    private function priority_badge($priority)
    {
        $priority = $this->safe_priority($priority);
        $labels = [
            'low'    => _l('notes_priority_low'),
            'medium' => _l('notes_priority_medium'),
            'high'   => _l('notes_priority_high'),
            'urgent' => _l('notes_priority_urgent'),
        ];

        $classes = [
            'low'    => 'default',
            'medium' => 'info',
            'high'   => 'warning',
            'urgent' => 'danger',
        ];

        return '<span class="label label-' . $classes[$priority] . '">' . $labels[$priority] . '</span>';
    }

    private function render_staff_avatar($staff_id)
    {
        if ($staff_id <= 0) {
            return '';
        }

        return staff_profile_image($staff_id, ['staff-profile-image-small', 'img-circle']);
    }

    private function render_relation_avatar($rel_type, $rel_id)
    {
        if ($rel_type === 'staff') {
            return $this->render_staff_avatar($rel_id);
        }

        if ($rel_type === 'customer') {
            $primary_contact_id = get_primary_contact_user_id($rel_id);
            if ($primary_contact_id) {
                return contact_profile_image($primary_contact_id, ['staff-profile-image-small', 'img-circle']);
            }
        }

        return '';
    }

    private function attachment_link($note_id, $attachment, $original_name)
    {
        if (empty($attachment)) {
            return '-';
        }

        return '<a href="' . admin_url('notes/notes/download_attachment/' . (int) $note_id) . '"><i class="fa fa-paperclip"></i> ' . html_escape($original_name ?: $attachment) . '</a>';
    }

    private function handle_note_attachment($note_id)
    {
        if (empty($_FILES['attachment']['name'])) {
            return [];
        }

        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'csv', 'txt', 'webp'];
        $original = $_FILES['attachment']['name'];
        $extension = strtolower(pathinfo($original, PATHINFO_EXTENSION));

        if (!in_array($extension, $allowed)) {
            return [];
        }

        $folder = FCPATH . 'modules/notes/uploads/' . (int) $note_id . '/';
        if (!is_dir($folder)) {
            @mkdir($folder, 0755, true);
        }

        if (!file_exists($folder . 'index.html')) {
            @file_put_contents($folder . 'index.html', '');
        }

        $file_name = unique_filename($folder, app_generate_hash() . '.' . $extension);
        if (move_uploaded_file($_FILES['attachment']['tmp_name'], $folder . $file_name)) {
            return [
                'file_name'     => $file_name,
                'original_name' => $original,
            ];
        }

        return [];
    }

    private function delete_attachment_folder($note_id)
    {
        $folder = FCPATH . 'modules/notes/uploads/' . (int) $note_id;
        if (!is_dir($folder)) {
            return;
        }

        $files = glob($folder . '/*');
        if ($files) {
            foreach ($files as $file) {
                if (is_file($file)) {
                    @unlink($file);
                }
            }
        }
        @rmdir($folder);
    }

    private function get_export_notes()
    {
        $ids = $this->input->post('ids');
        $ids = is_array($ids) ? array_map('intval', $ids) : [];
        $fields = $this->db->list_fields($this->table_note);
        $select = ['id'];
        foreach (['title', 'rel_type', 'rel_id', 'description', 'note_type', 'priority', 'note_color', 'note_visibility', 'assigned_staff_id', 'attachment_original_name', 'addedfrom', 'dateadded'] as $field) {
            if (in_array($field, $fields)) {
                $select[] = $field;
            }
        }
        $this->db->select(implode(',', $select));
        if (!empty($ids)) {
            $this->db->where_in('id', $ids);
        }
        return $this->db->order_by('id', 'DESC')->get($this->table_note)->result_array();
    }

    private function output_csv($filename, $rows)
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);

        $output = fopen('php://output', 'w');
        foreach ($rows as $row) {
            fputcsv($output, $row);
        }
        fclose($output);
        exit;
    }

    private function build_health_summary()
    {
        $modulePath = module_dir_path('notes');
        $moduleCandidates = [];
        $modulesRoot = FCPATH . 'modules/';
        if (is_dir($modulesRoot)) {
            foreach ((array) glob($modulesRoot . '*notes*', GLOB_ONLYDIR) as $folder) {
                $moduleCandidates[] = str_replace(FCPATH, '', $folder);
            }
        }

        $tables = [];
        foreach ([db_prefix() . 'notes', db_prefix() . 'project_notes'] as $table) {
            $tables[$table] = [
                'exists' => $this->db->table_exists($table),
                'rows'   => $this->db->table_exists($table) ? (int) $this->db->count_all($table) : 0,
            ];
        }

        return [
            'module_path'       => str_replace(FCPATH, '', $modulePath),
            'main_file'         => str_replace(FCPATH, '', module_dir_path('notes', 'notes.php')),
            'upload_path'       => str_replace(FCPATH, '', NOTES_UPLOAD_FOLDER),
            'module_candidates' => $moduleCandidates,
            'tables'            => $tables,
            'active_table'      => db_prefix() . 'notes',
            'native_note_count' => $this->db->table_exists(db_prefix() . 'notes') ? (int) $this->db->count_all(db_prefix() . 'notes') : 0,
            'project_note_count'=> $this->db->table_exists(db_prefix() . 'project_notes') ? (int) $this->db->count_all(db_prefix() . 'project_notes') : 0,
        ];
    }

    private function get_sources($activeOnly = true)
    {
        $table=db_prefix().'notes_sources';
        if (!$this->db->table_exists($table)) {
            $out=[]; foreach ($this->rel_type_name as $key=>$lang) { $out[$key]=['key'=>$key,'name'=>_l($lang),'color'=>'#3598DB']; } return $out;
        }
        if ($activeOnly) $this->db->where('active',1);
        $rows=$this->db->order_by('sort_order','ASC')->order_by('id','ASC')->get($table)->result_array();
        $out=[]; foreach ($rows as $row) { $out[$row['source_key']]=['id'=>(int)$row['id'],'key'=>$row['source_key'],'name'=>$row['name'],'color'=>$row['color'],'active'=>(int)$row['active'],'is_system'=>(int)$row['is_system'],'sort_order'=>(int)$row['sort_order']]; } return $out;
    }

    private function get_source_config($key)
    {
        $sources=$this->get_sources(false); return $sources[$key] ?? ['key'=>$key,'name'=>$this->source_label($key),'color'=>'#3598DB'];
    }

    private function get_note_types($activeOnly = true)
    {
        $table=db_prefix().'notes_types';
        if (!$this->db->table_exists($table)) return ['general'=>['key'=>'general','name'=>_l('notes_type_general'),'color'=>'#169179']];
        if ($activeOnly) $this->db->where('active',1);
        $rows=$this->db->order_by('sort_order','ASC')->order_by('id','ASC')->get($table)->result_array();
        $out=[]; foreach ($rows as $row) { $out[$row['type_key']]=['id'=>(int)$row['id'],'key'=>$row['type_key'],'name'=>$row['name'],'color'=>$row['color'],'active'=>(int)$row['active'],'is_system'=>(int)$row['is_system'],'sort_order'=>(int)$row['sort_order']]; } return $out;
    }

    private function get_note_type_config($key)
    {
        $types=$this->get_note_types(false); return $types[$key] ?? ['key'=>$key,'name'=>ucwords(str_replace('_',' ',$key)),'color'=>'#169179'];
    }

    private function type_badge($key)
    {
        $type=$this->get_note_type_config($key); return '<span class="label" style="background:'.html_escape($type['color']).';">'.html_escape($type['name']).'</span>';
    }

    private function safe_source($key)
    {
        $key=trim((string)$key); $sources=$this->get_sources(false); return isset($sources[$key]) ? $key : 'personal_note';
    }

    private function safe_note_type($key)
    {
        $key=trim((string)$key); $types=$this->get_note_types(false); return isset($types[$key]) ? $key : 'general';
    }

    private function normalize_hex($color)
    {
        $color=strtoupper(trim((string)$color)); return preg_match('/^#[0-9A-F]{6}$/',$color) ? $color : '#3598DB';
    }

    private function safe_brand_color($color)
    {
        $color = strtoupper(trim((string) $color));
        foreach ($this->brand_colors as $hex => $label) {
            if (strtoupper($hex) === $color) {
                return $hex;
            }
        }

        $defaultColor = $this->normalize_hex(get_option('notes_default_color'));
        return $color === strtoupper($defaultColor) ? $defaultColor : $defaultColor;
    }

    private function safe_priority($priority)
    {
        $priority = strtolower(trim((string) $priority));
        return in_array($priority, ['low', 'medium', 'high', 'urgent']) ? $priority : 'medium';
    }

    private function safe_visibility($visibility)
    {
        $visibility = strtolower(trim((string) $visibility));
        return in_array($visibility, ['normal', 'personal', 'custom', 'special']) ? $visibility : 'normal';
    }
}

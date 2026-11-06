<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Scaf_permit_controls extends AdminController
{
    public function index($id)
    {
        require_once __DIR__.'/../includes/permit_controls.php';
        $id = (int) $id;
        if (!scaf_permit_controls_allowed($id)) {
            access_denied('projects');
            return;
        }
        $project = $this->projects_model->get($id);
        $records = scaf_permit_records($id);
        $fields = array_filter(scaf_permit_custom_fields($id), static function ($field) {
            return preg_match('/permit|inspector|inspection|warranty|commencement/i', $field['name']);
        });
        $data = ['title' => 'Permits & Change Orders', 'project' => $project, 'records' => $records, 'fields' => $fields];
        if ($this->input->get('download') === 'closeout') {
            $html = $this->load->view('sc_admin_fixes/projects/permit_closeout', $data, true);
            $pdf = new TCPDF();
            $pdf->SetTitle('Project '.$id.' closeout summary');
            $pdf->AddPage();
            $pdf->writeHTML($html);
            $pdf->Output('project-'.$id.'-closeout.pdf', 'D');
            return;
        }
        $this->load->view('sc_admin_fixes/projects/permit_controls', $data);
    }

    public function save($id)
    {
        require_once __DIR__.'/../includes/permit_controls.php';
        $id = (int) $id;
        if ($this->input->method() !== 'post') {
            show_404();
            return;
        }
        if (!scaf_permit_controls_allowed($id, true)) {
            access_denied('projects');
            return;
        }
        $read = fn ($key) => trim((string) $this->input->post($key, true));
        $kind = $read('kind');
        $saved = false;
        if ($kind === 'permit' && isset(scaf_permit_stages()[$read('status')]) && $read('permit_type') !== '') {
            $stage = $read('status');
            $values = [
                'permit_type' => mb_substr($read('permit_type'), 0, 100),
                'permit_number' => mb_substr($read('permit_number'), 0, 100),
                'jurisdiction' => mb_substr($read('jurisdiction'), 0, 191),
                'status' => $stage,
                'inspection_status' => $stage === 'final_passed' ? 'final_passed' : null,
            ];
            $table = db_prefix().'sce_permits';
            $permitId = (int) $read('record_id');
            if ($this->db->table_exists($table)) {
                if ($permitId) {
                    $scope = ['id' => $permitId, 'project_id' => $id];
                    if ($this->db->where($scope)->count_all_results($table)) {
                        $saved = $this->db->where($scope)->update($table, $values);
                    }
                } else {
                    $saved = $this->db->insert($table, $values + ['project_id' => $id, 'created_at' => date('Y-m-d H:i:s')]);
                }
            }
        } elseif ($kind === 'change' && $read('title') !== '' && $read('change_number') !== ''
            && preg_match('/^-?\d{1,13}(?:\.\d{1,2})?$/D', $read('amount'))
            && preg_match('/^\d{1,4}$/D', $read('days_impact'))) {
            $table = db_prefix().'sce_change_orders';
            $number = mb_substr($read('change_number'), 0, 100);
            if ($this->db->table_exists($table)) {
                $this->db->query("INSERT IGNORE INTO `$table`
                    (project_id, change_number, title, amount, days_impact, status, created_by, created_at)
                    VALUES (?, ?, ?, ?, ?, 'draft', ?, ?)", [
                    $id, $number, mb_substr($read('title'), 0, 191), $read('amount'),
                    (int) $read('days_impact'), get_staff_user_id(), date('Y-m-d H:i:s'),
                ]);
                $saved = $this->db->affected_rows() === 1;
            }
        } elseif ($kind === 'approve' && $read('approval_reference') !== '') {
            $table = db_prefix().'sce_change_orders';
            if ($this->db->table_exists($table)) {
                $this->db->where(['id' => (int) $read('record_id'), 'project_id' => $id, 'status' => 'draft'])
                    ->set('description', "CONCAT_WS(CHAR(10), NULLIF(description, ''), ".$this->db->escape('Customer approval reference: '.mb_substr($read('approval_reference'), 0, 1000)).')', false)
                    ->update($table, ['status' => 'approved', 'approved_at' => date('Y-m-d H:i:s')]);
                $saved = $this->db->affected_rows() === 1;
            }
        }
        if ($saved) {
            $this->projects_model->log_activity($id, 'project_activity_updated_project', '', 0);
        }
        set_alert($saved ? 'success' : 'warning', $saved ? 'Project controls saved.' : 'No changes saved. Check the inputs and record status.');
        redirect(admin_url('sc_admin_fixes/scaf_permit_controls/index/'.$id));
    }
}

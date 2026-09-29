<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Cabinet_maker_model extends App_Model
{
    private function table($name)
    {
        return cabinet_maker_table($name);
    }

    private function safe_rows($table, $order = '')
    {
        if (!$this->db->table_exists($table)) {
            return [];
        }
        try {
            if ($order !== '') {
                $this->db->order_by($order);
            }
            return $this->db->get($table)->result();
        } catch (Throwable $e) {
            log_message('error', 'Cabinet Maker query failed for ' . $table . ': ' . $e->getMessage());
            return [];
        }
    }

    private function value($row, $field, $default = null)
    {
        return isset($row->{$field}) ? $row->{$field} : $default;
    }

    public function get_design($id)
    {
        $table = $this->table('designs');
        if (!$this->db->table_exists($table)) return null;
        return $this->db->where('id', (int) $id)->get($table)->row();
    }

    public function get_designs($project_id = null)
    {
        $table = $this->table('designs');
        if (!$this->db->table_exists($table)) return [];
        if ($project_id) $this->db->where('project_id', (int) $project_id);
        return $this->db->order_by('id', 'DESC')->get($table)->result();
    }

    public function create_design($data)
    {
        $table = $this->table('designs');
        if (!$this->db->table_exists($table)) return false;
        $row = [
            'name' => trim($data['name'] ?? 'Untitled Kitchen'),
            'project_id' => !empty($data['project_id']) ? (int) $data['project_id'] : null,
            'client_id' => !empty($data['client_id']) ? (int) $data['client_id'] : null,
            'vendor_id' => !empty($data['vendor_id']) ? (int) $data['vendor_id'] : null,
            'status' => 'draft',
            'room_width' => (float) ($data['room_width'] ?? 120),
            'room_length' => (float) ($data['room_length'] ?? 120),
            'room_height' => (float) ($data['room_height'] ?? 96),
            'units' => $data['units'] ?? get_option('cabinet_maker_units'),
            'export_version' => CABINET_MAKER_VERSION,
            'design_json' => json_encode(['version'=>3,'cabinets'=>[],'appliances'=>[],'countertops'=>[]]),
            'created_by' => get_staff_user_id(),
            'date_created' => date('Y-m-d H:i:s'),
        ];
        $fields = array_flip($this->db->list_fields($table));
        $row = array_intersect_key($row, $fields);
        if (!$this->db->insert($table, $row)) return false;
        $id = (int) $this->db->insert_id();
        if ($id > 0 && isset($fields['slug'])) {
            $this->db->where('id', $id)->update($table, ['slug' => cabinet_maker_slug($row['name'], $id)]);
        }
        return $id ?: false;
    }

    public function save_design($id, $payload)
    {
        $design = $this->get_design($id);
        if (!$design) return false;
        $table = $this->table('designs');
        $data = [
            'name' => trim($payload['name'] ?? $design->name),
            'status' => $payload['status'] ?? $this->value($design, 'status', 'draft'),
            'vendor_id' => !empty($payload['vendor_id']) ? (int) $payload['vendor_id'] : null,
            'room_width' => (float) ($payload['room_width'] ?? $this->value($design, 'room_width', 120)),
            'room_length' => (float) ($payload['room_length'] ?? $this->value($design, 'room_length', 120)),
            'room_height' => (float) ($payload['room_height'] ?? $this->value($design, 'room_height', 96)),
            'design_json' => is_string($payload['design_json'] ?? null) ? $payload['design_json'] : json_encode($payload['design_json'] ?? []),
            'date_updated' => date('Y-m-d H:i:s'),
            'export_version' => CABINET_MAKER_VERSION,
        ];
        $fields = array_flip($this->db->list_fields($table));
        $data = array_intersect_key($data, $fields);
        return (bool) $this->db->where('id', (int) $id)->update($table, $data);
    }

    public function replace_parts($id, $parts)
    {
        $table = $this->table('parts');
        if (!$this->db->table_exists($table)) return false;
        $this->db->where('design_id', (int) $id)->delete($table);
        $allowed = array_flip($this->db->list_fields($table));
        foreach ($parts as $part) {
            $part['design_id'] = (int) $id;
            $row = array_intersect_key($part, $allowed);
            $this->db->insert($table, $row);
        }
        return true;
    }

    public function get_parts($id)
    {
        $table = $this->table('parts');
        if (!$this->db->table_exists($table)) return [];
        return $this->db->where('design_id', (int) $id)->order_by('cabinet_uid,part_name')->get($table)->result();
    }

    public function materials()
    {
        $rows = $this->safe_rows($this->table('materials'), 'category ASC, name ASC');
        foreach ($rows as $row) {
            foreach (['name'=>'','sku'=>'','category'=>'sheet','width'=>null,'length'=>null,'thickness'=>null,'unit'=>'sheet','unit_cost'=>0,'active'=>1] as $field=>$default) {
                if (!isset($row->{$field})) $row->{$field} = $default;
            }
        }
        return $rows;
    }

    public function add_material($post)
    {
        $table = $this->table('materials');
        if (!$this->db->table_exists($table)) return false;
        $row = [
            'name' => trim((string) ($post['name'] ?? '')),
            'sku' => trim((string) ($post['sku'] ?? '')),
            'category' => trim((string) ($post['category'] ?? 'sheet')),
            'width' => ($post['width'] ?? '') !== '' ? (float) $post['width'] : null,
            'length' => ($post['length'] ?? '') !== '' ? (float) $post['length'] : null,
            'thickness' => ($post['thickness'] ?? '') !== '' ? (float) $post['thickness'] : null,
            'unit' => trim((string) ($post['unit'] ?? 'sheet')),
            'unit_cost' => (float) ($post['unit_cost'] ?? 0),
            'active' => 1,
            'date_created' => date('Y-m-d H:i:s'),
        ];
        $row = array_intersect_key($row, array_flip($this->db->list_fields($table)));
        return $this->db->insert($table, $row) ? (int) $this->db->insert_id() : false;
    }

    public function vendors()
    {
        $table = $this->table('vendors');
        if (!$this->db->table_exists($table)) return [];
        $rows = $this->db->where('active', 1)->order_by('name')->get($table)->result();
        foreach ($rows as $row) {
            foreach (['name'=>'','contact'=>'','email'=>'','phone'=>'','active'=>1] as $field=>$default) {
                if (!isset($row->{$field})) $row->{$field} = $default;
            }
        }
        return $rows;
    }

    public function vendor($id)
    {
        $table = $this->table('vendors');
        if (!$this->db->table_exists($table)) return null;
        return $this->db->where('id', (int) $id)->get($table)->row();
    }

    public function vendor_items($vendorId)
    {
        $table = $this->table('vendor_items');
        if (!$vendorId || !$this->db->table_exists($table)) return [];
        return $this->db->where('vendor_id', (int) $vendorId)->where('active', 1)->order_by('sku')->get($table)->result();
    }

    public function add_vendor($post)
    {
        $table = $this->table('vendors');
        if (!$this->db->table_exists($table)) return false;
        $row = ['name'=>trim((string)($post['name']??'')),'contact'=>trim((string)($post['contact']??'')),'email'=>trim((string)($post['email']??'')),'phone'=>trim((string)($post['phone']??'')),'active'=>1,'date_created'=>date('Y-m-d H:i:s')];
        $row = array_intersect_key($row, array_flip($this->db->list_fields($table)));
        return $this->db->insert($table, $row) ? (int) $this->db->insert_id() : false;
    }

    public function save_vendor_item($vendorId, $post)
    {
        $table = $this->table('vendor_items');
        if (!$this->db->table_exists($table)) return false;
        $row = ['vendor_id'=>(int)$vendorId,'sku'=>trim((string)($post['sku']??'')),'name'=>trim((string)($post['name']??'')),'category'=>trim((string)($post['category']??'cabinet')),'price_core'=>(float)($post['price_core']??0),'price_slim'=>(float)($post['price_slim']??0),'price_luxe'=>(float)($post['price_luxe']??0),'active'=>1,'date_created'=>date('Y-m-d H:i:s')];
        $row = array_intersect_key($row, array_flip($this->db->list_fields($table)));
        $old = $this->db->where('vendor_id', (int)$vendorId)->where('sku', $row['sku'])->get($table)->row();
        if ($old) return $this->db->where('id', $old->id)->update($table, $row) ? $old->id : false;
        return $this->db->insert($table, $row) ? (int)$this->db->insert_id() : false;
    }

    public function offcuts()
    {
        return $this->safe_rows($this->table('offcuts'), 'id DESC');
    }

    public function create_share($design, $days = 30)
    {
        $table = $this->table('shares');
        if (!$this->db->table_exists($table)) return false;
        $row = ['design_id'=>$design->id,'token'=>hash('sha256',random_bytes(32)),'friendly_slug'=>cabinet_maker_slug($design->name,$design->id).'-'.substr(bin2hex(random_bytes(4)),0,6),'expires_at'=>$days?date('Y-m-d H:i:s',strtotime('+'.(int)$days.' days')):null,'created_by'=>get_staff_user_id(),'date_created'=>date('Y-m-d H:i:s')];
        $row = array_intersect_key($row, array_flip($this->db->list_fields($table)));
        if (!$this->db->insert($table, $row)) return false;
        return $this->db->where('id', $this->db->insert_id())->get($table)->row();
    }

    public function delete_design($id)
    {
        foreach (['parts','shares'] as $name) {
            $table=$this->table($name); if ($this->db->table_exists($table)) $this->db->where('design_id',(int)$id)->delete($table);
        }
        $table=$this->table('designs'); if ($this->db->table_exists($table)) $this->db->where('id',(int)$id)->delete($table);
    }

    public function expire_old_share_links()
    {
        $table=$this->table('shares'); if (!$this->db->table_exists($table)) return;
        $this->db->where('expires_at IS NOT NULL', null, false)->where('expires_at <', date('Y-m-d H:i:s'))->delete($table);
    }
}

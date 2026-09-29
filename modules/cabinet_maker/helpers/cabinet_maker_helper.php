<?php
defined('BASEPATH') or exit('No direct script access allowed');
function cabinet_maker_table($name){ return db_prefix().'cabinet_maker_'.$name; }
function cabinet_maker_slug($name,$id){ $s=slug_it($name); return trim($s,'-').'-'.(int)$id; }
function cabinet_maker_can($cap){ return is_admin() || has_permission('cabinet_maker','',$cap); }
function cabinet_maker_run_schema(){
 $CI=&get_instance();
 $charset=preg_replace('/[^a-zA-Z0-9_]/','',(string)($CI->db->char_set ?: 'utf8mb4'));
 $collate=preg_replace('/[^a-zA-Z0-9_]/','',(string)($CI->db->dbcollat ?: 'utf8mb4_unicode_ci'));
 if($charset===''){ $charset='utf8mb4'; }
 if($collate===''){ $collate='utf8mb4_unicode_ci'; }
 $q=[];
 $q[]="CREATE TABLE IF NOT EXISTS `".cabinet_maker_table('designs')."` (`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,`name` VARCHAR(191) NOT NULL,`slug` VARCHAR(160) DEFAULT NULL,`project_id` INT DEFAULT NULL,`client_id` INT DEFAULT NULL,`status` VARCHAR(30) NOT NULL DEFAULT 'draft',`room_width` DECIMAL(10,3) NOT NULL DEFAULT 120,`room_length` DECIMAL(10,3) NOT NULL DEFAULT 120,`room_height` DECIMAL(10,3) NOT NULL DEFAULT 96,`units` VARCHAR(10) NOT NULL DEFAULT 'in',`design_json` LONGTEXT NULL,`thumbnail` LONGTEXT NULL,`subtotal` DECIMAL(15,2) NOT NULL DEFAULT 0,`tax` DECIMAL(15,2) NOT NULL DEFAULT 0,`total` DECIMAL(15,2) NOT NULL DEFAULT 0,`created_by` INT NOT NULL,`date_created` DATETIME NOT NULL,`date_updated` DATETIME NULL,PRIMARY KEY (`id`),KEY `project_id` (`project_id`),KEY `client_id` (`client_id`),KEY `status` (`status`),UNIQUE KEY `slug` (`slug`)) ENGINE=InnoDB DEFAULT CHARSET=$charset COLLATE=$collate";
 $q[]="CREATE TABLE IF NOT EXISTS `".cabinet_maker_table('materials')."` (`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,`name` VARCHAR(191) NOT NULL,`sku` VARCHAR(100) NULL,`category` VARCHAR(80) NOT NULL DEFAULT 'sheet',`width` DECIMAL(10,3) DEFAULT NULL,`length` DECIMAL(10,3) DEFAULT NULL,`thickness` DECIMAL(10,3) DEFAULT NULL,`unit` VARCHAR(20) NOT NULL DEFAULT 'sheet',`unit_cost` DECIMAL(15,4) NOT NULL DEFAULT 0,`color` VARCHAR(20) NULL,`texture` VARCHAR(191) NULL,`active` TINYINT(1) NOT NULL DEFAULT 1,`date_created` DATETIME NOT NULL,PRIMARY KEY (`id`),UNIQUE KEY `sku` (`sku`)) ENGINE=InnoDB DEFAULT CHARSET=$charset COLLATE=$collate";
 $q[]="CREATE TABLE IF NOT EXISTS `".cabinet_maker_table('parts')."` (`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,`design_id` INT UNSIGNED NOT NULL,`cabinet_uid` VARCHAR(80) NOT NULL,`part_name` VARCHAR(191) NOT NULL,`part_type` VARCHAR(40) NOT NULL DEFAULT 'sheet',`material_id` INT UNSIGNED DEFAULT NULL,`qty` INT NOT NULL DEFAULT 1,`width` DECIMAL(10,3) NOT NULL,`length` DECIMAL(10,3) NOT NULL,`thickness` DECIMAL(10,3) NOT NULL,`grain` VARCHAR(20) NOT NULL DEFAULT 'none',`edge_top` TINYINT(1) DEFAULT 0,`edge_bottom` TINYINT(1) DEFAULT 0,`edge_left` TINYINT(1) DEFAULT 0,`edge_right` TINYINT(1) DEFAULT 0,`unit_cost` DECIMAL(15,4) NOT NULL DEFAULT 0,`total_cost` DECIMAL(15,4) NOT NULL DEFAULT 0,PRIMARY KEY (`id`),KEY `design_id` (`design_id`),KEY `material_id` (`material_id`)) ENGINE=InnoDB DEFAULT CHARSET=$charset COLLATE=$collate";
 $q[]="CREATE TABLE IF NOT EXISTS `".cabinet_maker_table('offcuts')."` (`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,`material_id` INT UNSIGNED DEFAULT NULL,`source_design_id` INT UNSIGNED DEFAULT NULL,`width` DECIMAL(10,3) NOT NULL,`length` DECIMAL(10,3) NOT NULL,`thickness` DECIMAL(10,3) NOT NULL,`location` VARCHAR(191) NULL,`status` VARCHAR(20) NOT NULL DEFAULT 'available',`notes` TEXT NULL,`date_created` DATETIME NOT NULL,`date_used` DATETIME NULL,PRIMARY KEY (`id`),KEY `material_id` (`material_id`),KEY `status` (`status`)) ENGINE=InnoDB DEFAULT CHARSET=$charset COLLATE=$collate";
 $q[]="CREATE TABLE IF NOT EXISTS `".cabinet_maker_table('shares')."` (`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,`design_id` INT UNSIGNED NOT NULL,`token` CHAR(64) NOT NULL,`friendly_slug` VARCHAR(160) NOT NULL,`password_hash` VARCHAR(255) NULL,`expires_at` DATETIME NULL,`allow_download` TINYINT(1) NOT NULL DEFAULT 1,`allow_comment` TINYINT(1) NOT NULL DEFAULT 1,`views` INT NOT NULL DEFAULT 0,`created_by` INT NOT NULL,`date_created` DATETIME NOT NULL,PRIMARY KEY (`id`),UNIQUE KEY `token` (`token`),UNIQUE KEY `friendly_slug` (`friendly_slug`),KEY `design_id` (`design_id`)) ENGINE=InnoDB DEFAULT CHARSET=$charset COLLATE=$collate";
 $q[]="CREATE TABLE IF NOT EXISTS `".cabinet_maker_table('comments')."` (`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,`design_id` INT UNSIGNED NOT NULL,`share_id` INT UNSIGNED DEFAULT NULL,`staff_id` INT DEFAULT NULL,`contact_id` INT DEFAULT NULL,`author_name` VARCHAR(191) NULL,`comment` TEXT NOT NULL,`date_created` DATETIME NOT NULL,PRIMARY KEY (`id`),KEY `design_id` (`design_id`)) ENGINE=InnoDB DEFAULT CHARSET=$charset COLLATE=$collate";

 $q[]="CREATE TABLE IF NOT EXISTS `".cabinet_maker_table('vendors')."` (`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,`name` VARCHAR(191) NOT NULL,`contact` VARCHAR(191) NULL,`email` VARCHAR(191) NULL,`phone` VARCHAR(60) NULL,`active` TINYINT(1) NOT NULL DEFAULT 1,`date_created` DATETIME NOT NULL,PRIMARY KEY (`id`),UNIQUE KEY `name` (`name`)) ENGINE=InnoDB DEFAULT CHARSET=$charset COLLATE=$collate";
 $q[]="CREATE TABLE IF NOT EXISTS `".cabinet_maker_table('vendor_items')."` (`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,`vendor_id` INT UNSIGNED NOT NULL,`sku` VARCHAR(160) NOT NULL,`name` VARCHAR(191) NOT NULL,`category` VARCHAR(80) NOT NULL DEFAULT 'cabinet',`price_core` DECIMAL(15,2) NOT NULL DEFAULT 0,`price_slim` DECIMAL(15,2) NOT NULL DEFAULT 0,`price_luxe` DECIMAL(15,2) NOT NULL DEFAULT 0,`active` TINYINT(1) NOT NULL DEFAULT 1,`date_created` DATETIME NOT NULL,PRIMARY KEY (`id`),UNIQUE KEY `vendor_sku` (`vendor_id`,`sku`),KEY `vendor_id` (`vendor_id`)) ENGINE=InnoDB DEFAULT CHARSET=$charset COLLATE=$collate";
 foreach($q as $sql) $CI->db->query($sql);
 if($CI->db->table_exists(cabinet_maker_table('designs')) && !$CI->db->field_exists('vendor_id',cabinet_maker_table('designs'))) $CI->db->query('ALTER TABLE `'.cabinet_maker_table('designs').'` ADD `vendor_id` INT DEFAULT NULL AFTER `client_id`, ADD KEY `vendor_id` (`vendor_id`)');
 if($CI->db->table_exists(cabinet_maker_table('parts')) && !$CI->db->field_exists('part_type',cabinet_maker_table('parts'))) $CI->db->query("ALTER TABLE `".cabinet_maker_table('parts')."` ADD `part_type` VARCHAR(40) NOT NULL DEFAULT 'sheet' AFTER `part_name`");
}
function cabinet_maker_seed_defaults(){
 $CI=&get_instance(); $t=cabinet_maker_table('materials'); if(!$CI->db->table_exists($t) || $CI->db->count_all($t)>0) return;
 $now=date('Y-m-d H:i:s'); $rows=[
 ['name'=>'3/4 in White Birch Plywood','sku'=>'PLY-BIRCH-075','category'=>'sheet','width'=>48,'length'=>96,'thickness'=>.75,'unit'=>'sheet','unit_cost'=>85,'color'=>'#e8d5aa','texture'=>'birch','active'=>1,'date_created'=>$now],
 ['name'=>'3/4 in White Melamine','sku'=>'MEL-WHT-075','category'=>'sheet','width'=>48,'length'=>96,'thickness'=>.75,'unit'=>'sheet','unit_cost'=>58,'color'=>'#f7f7f7','texture'=>'matte','active'=>1,'date_created'=>$now],
 ['name'=>'1/4 in Plywood Back','sku'=>'PLY-BACK-025','category'=>'sheet','width'=>48,'length'=>96,'thickness'=>.25,'unit'=>'sheet','unit_cost'=>32,'color'=>'#d9c59b','texture'=>'wood','active'=>1,'date_created'=>$now],
 ['name'=>'Soft Close Hinge','sku'=>'HW-HINGE-SC','category'=>'hardware','unit'=>'each','unit_cost'=>4.5,'active'=>1,'date_created'=>$now],
 ['name'=>'Soft Close Drawer Slide Pair','sku'=>'HW-SLIDE-SC','category'=>'hardware','unit'=>'pair','unit_cost'=>28,'active'=>1,'date_created'=>$now]];
 $CI->db->insert_batch($t,$rows);
}

function cabinet_maker_runtime_repair()
{
    static $checked = false;
    if ($checked) {
        return;
    }
    $checked = true;

    $CI = &get_instance();
    $required = ['designs', 'materials', 'parts', 'offcuts', 'shares', 'comments', 'vendors', 'vendor_items'];
    $repair = false;
    foreach ($required as $name) {
        if (!$CI->db->table_exists(cabinet_maker_table($name))) {
            $repair = true;
            break;
        }
    }
    if (!$repair && !$CI->db->field_exists('vendor_id', cabinet_maker_table('designs'))) {
        $repair = true;
    }
    if ($repair) {
        cabinet_maker_run_schema();
    }
    cabinet_maker_seed_defaults();
}

function cabinet_maker_register_email_templates()
{
    $CI = &get_instance();
    $table = db_prefix() . 'emailtemplates';
    if (!$CI->db->table_exists($table)) {
        return;
    }

    $templates = [
        [
            'slug'    => 'cabinet-maker-design-shared',
            'name'    => 'Cabinet Maker Design Shared',
            'subject' => 'Your kitchen design: {design_name}',
            'message' => '<p>Hello {contact_firstname},</p><p>Your Smart Choice kitchen design <strong>{design_name}</strong> is ready.</p><p><a href="{design_share_url}" style="background:#137333;color:#fff;padding:12px 18px;text-decoration:none;border-radius:4px">View Kitchen Design</a></p><p>Smart Choice Contractors USA</p>',
        ],
        [
            'slug'    => 'cabinet-maker-design-approved',
            'name'    => 'Cabinet Maker Design Approved',
            'subject' => 'Kitchen design approved: {design_name}',
            'message' => '<p>The design <strong>{design_name}</strong> has been approved.</p><p><a href="{design_admin_url}">Open design</a></p>',
        ],
    ];

    $fields = $CI->db->list_fields($table);
    foreach ($templates as $template) {
        $query = $CI->db->where('slug', $template['slug']);
        if (in_array('language', $fields, true)) {
            $query = $query->where('language', 'english');
        }
        if ($query->get($table)->row()) {
            continue;
        }

        $row = [
            'type'      => 'cabinet_maker',
            'slug'      => $template['slug'],
            'name'      => $template['name'],
            'subject'   => $template['subject'],
            'message'   => $template['message'],
            'fromname'  => '',
            'fromemail' => '',
            'plaintext' => 0,
            'active'    => 1,
            'order'     => 1,
        ];
        if (in_array('language', $fields, true)) {
            $row['language'] = 'english';
        }
        $row = array_intersect_key($row, array_flip($fields));
        $CI->db->insert($table, $row);
    }
}
function cabinet_maker_public_url($share){ return site_url('cabinet-maker/'.$share->friendly_slug); }

function cabinet_maker_seed_innovation_catalog(){
 $CI=&get_instance(); $vt=cabinet_maker_table('vendors'); $it=cabinet_maker_table('vendor_items');
 if(!$CI->db->table_exists($vt)||!$CI->db->table_exists($it)) return;
 $vendor=$CI->db->where('name','Innovation')->get($vt)->row();
 if(!$vendor){$CI->db->insert($vt,['name'=>'Innovation','active'=>1,'date_created'=>date('Y-m-d H:i:s')]);$vendor=(object)['id'=>$CI->db->insert_id()];}
 if($CI->db->where('vendor_id',$vendor->id)->count_all_results($it)>0) return;
 $file=module_dir_path('cabinet_maker','vendor/innovation_p1.json'); if(!is_file($file)) return;
 $data=json_decode(file_get_contents($file),true); if(!is_array($data)) return;
 $batch=[]; foreach($data as $r){$batch[]=['vendor_id'=>$vendor->id,'sku'=>$r['sku'],'name'=>$r['name'],'category'=>'cabinet','price_core'=>$r['core'],'price_slim'=>$r['slim'],'price_luxe'=>$r['luxe'],'active'=>1,'date_created'=>date('Y-m-d H:i:s')]; if(count($batch)>=100){$CI->db->insert_batch($it,$batch);$batch=[];}}
 if($batch) $CI->db->insert_batch($it,$batch);
}

function cabinet_maker_openai_api_key()
{
    $candidates = [
        'openai_api_key',
        'open_ai_api_key',
        'ai_openai_api_key',
        'chatgpt_api_key',
    ];
    foreach ($candidates as $name) {
        $value = trim((string) get_option($name));
        if ($value !== '') {
            return $value;
        }
    }
    $env = getenv('OPENAI_API_KEY');
    return is_string($env) ? trim($env) : '';
}

function cabinet_maker_openai_image_render($dataUrl, $prompt)
{
    $key = cabinet_maker_openai_api_key();
    if ($key === '') {
        return ['success' => false, 'message' => 'The CRM OpenAI API key was not found.'];
    }
    if (!preg_match('#^data:image/(png|jpeg);base64,(.+)$#s', (string) $dataUrl, $m)) {
        return ['success' => false, 'message' => 'The design preview image is invalid.'];
    }
    $binary = base64_decode($m[2], true);
    if ($binary === false || strlen($binary) > 12 * 1024 * 1024) {
        return ['success' => false, 'message' => 'The design preview is too large or invalid.'];
    }
    $tmp = tempnam(sys_get_temp_dir(), 'cm_ai_');
    file_put_contents($tmp, $binary);
    $mime = $m[1] === 'png' ? 'image/png' : 'image/jpeg';
    $post = [
        'model' => (string) (get_option('cabinet_maker_ai_image_model') ?: 'gpt-image-1'),
        'prompt' => trim($prompt) !== '' ? trim($prompt) : 'Create a photorealistic kitchen rendering that preserves the cabinet count, placement, dimensions, finishes, appliances, camera angle, and room geometry shown in the supplied design. Do not add or overlap cabinets.',
        'size' => '1536x1024',
        'quality' => 'high',
        'image[]' => new CURLFile($tmp, $mime, 'cabinet-design.' . ($m[1] === 'png' ? 'png' : 'jpg')),
    ];
    $ch = curl_init('https://api.openai.com/v1/images/edits');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 180,
        CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $key],
        CURLOPT_POSTFIELDS => $post,
    ]);
    $body = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    @unlink($tmp);
    if ($body === false || $status < 200 || $status >= 300) {
        log_message('error', 'Cabinet Maker OpenAI render failed: HTTP ' . $status . ' ' . $error . ' ' . (string) $body);
        return ['success' => false, 'message' => 'AI rendering failed. Check the CRM OpenAI configuration and server log.'];
    }
    $json = json_decode($body, true);
    $image = $json['data'][0]['b64_json'] ?? '';
    if ($image === '') {
        return ['success' => false, 'message' => 'OpenAI returned no rendered image.'];
    }
    return ['success' => true, 'image' => 'data:image/png;base64,' . $image];
}


/**
 * Adds missing legacy columns without deleting or recreating existing tables.
 */
function cabinet_maker_sync_legacy_schema()
{
    $CI = &get_instance();
    cabinet_maker_run_schema();

    $definitions = [
        'designs' => [
            'name' => "VARCHAR(191) NOT NULL DEFAULT 'Untitled Kitchen'",
            'slug' => "VARCHAR(160) NULL",
            'project_id' => "INT NULL",
            'client_id' => "INT NULL",
            'vendor_id' => "INT NULL",
            'status' => "VARCHAR(30) NOT NULL DEFAULT 'draft'",
            'room_width' => "DECIMAL(10,3) NOT NULL DEFAULT 120",
            'room_length' => "DECIMAL(10,3) NOT NULL DEFAULT 120",
            'room_height' => "DECIMAL(10,3) NOT NULL DEFAULT 96",
            'units' => "VARCHAR(10) NOT NULL DEFAULT 'in'",
            'design_json' => "LONGTEXT NULL",
            'thumbnail' => "LONGTEXT NULL",
            'subtotal' => "DECIMAL(15,2) NOT NULL DEFAULT 0",
            'tax' => "DECIMAL(15,2) NOT NULL DEFAULT 0",
            'total' => "DECIMAL(15,2) NOT NULL DEFAULT 0",
            'created_by' => "INT NOT NULL DEFAULT 0",
            'date_created' => "DATETIME NULL",
            'date_updated' => "DATETIME NULL",
            'export_version' => "VARCHAR(30) NULL",
        ],
        'materials' => [
            'name' => "VARCHAR(191) NOT NULL DEFAULT ''",
            'sku' => "VARCHAR(100) NULL",
            'category' => "VARCHAR(80) NOT NULL DEFAULT 'sheet'",
            'width' => "DECIMAL(10,3) NULL",
            'length' => "DECIMAL(10,3) NULL",
            'thickness' => "DECIMAL(10,3) NULL",
            'unit' => "VARCHAR(20) NOT NULL DEFAULT 'sheet'",
            'unit_cost' => "DECIMAL(15,4) NOT NULL DEFAULT 0",
            'color' => "VARCHAR(20) NULL",
            'texture' => "VARCHAR(191) NULL",
            'active' => "TINYINT(1) NOT NULL DEFAULT 1",
            'date_created' => "DATETIME NULL",
        ],
        'vendors' => [
            'name' => "VARCHAR(191) NOT NULL DEFAULT ''",
            'contact' => "VARCHAR(191) NULL",
            'email' => "VARCHAR(191) NULL",
            'phone' => "VARCHAR(60) NULL",
            'active' => "TINYINT(1) NOT NULL DEFAULT 1",
            'date_created' => "DATETIME NULL",
        ],
        'vendor_items' => [
            'vendor_id' => "INT UNSIGNED NOT NULL DEFAULT 0",
            'sku' => "VARCHAR(160) NOT NULL DEFAULT ''",
            'name' => "VARCHAR(191) NOT NULL DEFAULT ''",
            'category' => "VARCHAR(80) NOT NULL DEFAULT 'cabinet'",
            'price_core' => "DECIMAL(15,2) NOT NULL DEFAULT 0",
            'price_slim' => "DECIMAL(15,2) NOT NULL DEFAULT 0",
            'price_luxe' => "DECIMAL(15,2) NOT NULL DEFAULT 0",
            'active' => "TINYINT(1) NOT NULL DEFAULT 1",
            'date_created' => "DATETIME NULL",
        ],
        'offcuts' => [
            'material_id' => "INT UNSIGNED NULL",
            'source_design_id' => "INT UNSIGNED NULL",
            'width' => "DECIMAL(10,3) NOT NULL DEFAULT 0",
            'length' => "DECIMAL(10,3) NOT NULL DEFAULT 0",
            'thickness' => "DECIMAL(10,3) NOT NULL DEFAULT 0",
            'location' => "VARCHAR(191) NULL",
            'status' => "VARCHAR(20) NOT NULL DEFAULT 'available'",
            'notes' => "TEXT NULL",
            'date_created' => "DATETIME NULL",
            'date_used' => "DATETIME NULL",
        ],
    ];

    foreach ($definitions as $short => $fields) {
        $table = cabinet_maker_table($short);
        if (!$CI->db->table_exists($table)) {
            continue;
        }
        foreach ($fields as $field => $definition) {
            if (!$CI->db->field_exists($field, $table)) {
                $CI->db->query('ALTER TABLE `' . $table . '` ADD `' . $field . '` ' . $definition);
            }
        }
    }
}

function cabinet_maker_repair_database_safely()
{
    $CI = &get_instance();
    $oldDebug = $CI->db->db_debug;
    $CI->db->db_debug = false;
    $errors = [];
    try { cabinet_maker_run_schema(); } catch (Throwable $e) { $errors[] = $e->getMessage(); }
    $required = ['designs','materials','parts','offcuts','shares','comments','vendors','vendor_items'];
    foreach ($required as $short) {
        $table = cabinet_maker_table($short);
        if (!$CI->db->table_exists($table)) { $errors[] = 'Missing table: ' . $table; }
    }
    try { cabinet_maker_sync_legacy_schema(); } catch (Throwable $e) { $errors[] = $e->getMessage(); }
    $CI->db->db_debug = $oldDebug;
    foreach ($errors as $error) { log_message('error', 'Cabinet Maker repair: ' . $error); }
    return ['success'=>empty($errors),'message'=>empty($errors)?'Cabinet Maker database repaired successfully.':'Database repair completed with '.count($errors).' warning(s). Check the Health Check and application log.','errors'=>$errors];
}

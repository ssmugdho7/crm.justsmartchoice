<?php defined('BASEPATH') or exit('No direct script access allowed');
function estimating_hub_ai_install($seed = true){
    $CI=&get_instance(); $db=$CI->db; $prefix=db_prefix();
    add_option('estimating_hub_ai_enabled','1');
    update_option('estimating_hub_ai_version','1.0.8');
    add_option('estimating_hub_ai_ai_provider','openai');
    add_option('estimating_hub_ai_use_existing_openai_key','1');
    add_option('estimating_hub_ai_default_margin','25');
    add_option('estimating_hub_ai_default_overhead','12');
    add_option('estimating_hub_ai_default_tax_state','FL');
    add_option('estimating_hub_ai_region_adjustment','Tampa Bay');
    add_option('estimating_hub_ai_home_depot_enabled','1');
    add_option('estimating_hub_ai_home_depot_mode','pro_desk_export');
    add_option('estimating_hub_ai_home_depot_store_zip','34606');
    add_option('estimating_hub_ai_training_store_path','modules/estimating_hub_ai/uploads/training/');
    add_option('estimating_hub_ai_photo_store_path','modules/estimating_hub_ai/uploads/photos/');
    add_option('estimating_hub_ai_company_logo','');
    add_option('estimating_hub_ai_admin_email', get_option('smtp_email') ?: get_option('companyemail') ?: 'Sales@justsmartchoice.com');
    add_option('estimating_hub_ai_company_phone', get_option('company_phonenumber') ?: '(727) 755-3786');
    add_option('estimating_hub_ai_sync_estimates','1');
    add_option('estimating_hub_ai_sync_invoices','1');
    add_option('estimating_hub_ai_sync_proposals','1');

    add_option('estimating_hub_ai_collector_enabled','1');
    add_option('estimating_hub_ai_collector_token', substr(hash('sha256', APPPATH . time() . rand()), 0, 48));
    add_option('estimating_hub_ai_collector_default_zip','34606');
    add_option('estimating_hub_ai_collector_rate_limit_seconds','8');
    add_option('estimating_hub_ai_homewyse_enabled','1');
    add_option('estimating_hub_ai_home_depot_public_enabled','1');
    add_option('estimating_hub_ai_low_mid_high_mode','typical');

    foreach(['uploads/training','uploads/photos','uploads/documents','uploads/exports'] as $d){
        $path=__DIR__.'/'.$d;
        if(!is_dir($path)){ @mkdir($path,0755,true); }
        if(!file_exists($path.'/index.html')){ @file_put_contents($path.'/index.html',''); }
    }

    $tables=[];
    $tables['est_ai_estimates']="CREATE TABLE IF NOT EXISTS `{$prefix}est_ai_estimates` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `name` varchar(191) NOT NULL,
      `clientid` int(11) DEFAULT NULL,
      `project_id` int(11) DEFAULT NULL,
      `lead_id` int(11) DEFAULT NULL,
      `status` varchar(50) NOT NULL DEFAULT 'draft',
      `project_address` varchar(255) DEFAULT NULL,
      `scope_summary` mediumtext NULL,
      `ai_questions` mediumtext NULL,
      `subtotal` decimal(15,2) NOT NULL DEFAULT 0,
      `overhead` decimal(15,2) NOT NULL DEFAULT 0,
      `profit` decimal(15,2) NOT NULL DEFAULT 0,
      `tax` decimal(15,2) NOT NULL DEFAULT 0,
      `total` decimal(15,2) NOT NULL DEFAULT 0,
      `confidence_score` decimal(5,2) NOT NULL DEFAULT 0,
      `created_by` int(11) DEFAULT NULL,
      `datecreated` datetime NOT NULL,
      `dateupdated` datetime NULL,
      PRIMARY KEY(`id`),KEY `clientid` (`clientid`),KEY `project_id` (`project_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    $tables['est_ai_items']="CREATE TABLE IF NOT EXISTS `{$prefix}est_ai_items` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `estimate_id` int(11) DEFAULT NULL,
      `cost_item_id` int(11) DEFAULT NULL,
      `division` varchar(50) DEFAULT NULL,
      `trade` varchar(100) DEFAULT NULL,
      `category` varchar(150) DEFAULT NULL,
      `description` mediumtext NOT NULL,
      `unit` varchar(30) NOT NULL DEFAULT 'each',
      `qty` decimal(15,4) NOT NULL DEFAULT 0,
      `material_cost` decimal(15,2) NOT NULL DEFAULT 0,
      `labor_hours` decimal(15,4) NOT NULL DEFAULT 0,
      `labor_rate` decimal(15,2) NOT NULL DEFAULT 0,
      `equipment_cost` decimal(15,2) NOT NULL DEFAULT 0,
      `waste_factor` decimal(8,4) NOT NULL DEFAULT 0,
      `markup_percent` decimal(8,4) NOT NULL DEFAULT 0,
      `total` decimal(15,2) NOT NULL DEFAULT 0,
      `source` varchar(100) DEFAULT NULL,
      PRIMARY KEY(`id`),KEY `estimate_id` (`estimate_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    $tables['est_ai_cost_database']="CREATE TABLE IF NOT EXISTS `{$prefix}est_ai_cost_database` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `division` varchar(50) DEFAULT NULL,
      `trade` varchar(100) NOT NULL,
      `category` varchar(150) NOT NULL,
      `item_name` varchar(191) NOT NULL,
      `description` mediumtext NULL,
      `unit` varchar(30) NOT NULL DEFAULT 'each',
      `material_cost_low` decimal(15,2) NOT NULL DEFAULT 0,
      `material_cost_typical` decimal(15,2) NOT NULL DEFAULT 0,
      `material_cost_high` decimal(15,2) NOT NULL DEFAULT 0,
      `labor_hours` decimal(15,4) NOT NULL DEFAULT 0,
      `labor_rate` decimal(15,2) NOT NULL DEFAULT 0,
      `waste_factor` decimal(8,4) NOT NULL DEFAULT 0,
      `production_rate` varchar(100) DEFAULT NULL,
      `permit_required` tinyint(1) NOT NULL DEFAULT 0,
      `inspection_required` tinyint(1) NOT NULL DEFAULT 0,
      `region` varchar(100) NOT NULL DEFAULT 'Tampa Bay',
      `active` tinyint(1) NOT NULL DEFAULT 1,
      `datecreated` datetime NOT NULL,
      PRIMARY KEY(`id`),KEY `trade` (`trade`),KEY `category` (`category`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    $tables['est_ai_training_documents']="CREATE TABLE IF NOT EXISTS `{$prefix}est_ai_training_documents` (`id` int(11) NOT NULL AUTO_INCREMENT,`title` varchar(191) NOT NULL,`type` varchar(50) NOT NULL DEFAULT 'document',`file_name` varchar(191) DEFAULT NULL,`file_path` varchar(255) DEFAULT NULL,`content` longtext NULL,`status` varchar(50) NOT NULL DEFAULT 'stored',`processed` tinyint(1) NOT NULL DEFAULT 0,`created_by` int(11) DEFAULT NULL,`datecreated` datetime NOT NULL,PRIMARY KEY(`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    $tables['est_ai_photo_intake']="CREATE TABLE IF NOT EXISTS `{$prefix}est_ai_photo_intake` (`id` int(11) NOT NULL AUTO_INCREMENT,`estimate_id` int(11) DEFAULT NULL,`project_id` int(11) DEFAULT NULL,`clientid` int(11) DEFAULT NULL,`file_name` varchar(191) NOT NULL,`file_path` varchar(255) NOT NULL,`notes` mediumtext NULL,`ai_observations` mediumtext NULL,`created_by` int(11) DEFAULT NULL,`datecreated` datetime NOT NULL,PRIMARY KEY(`id`),KEY `estimate_id` (`estimate_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    $tables['est_ai_vendor_price_links']="CREATE TABLE IF NOT EXISTS `{$prefix}est_ai_vendor_price_links` (`id` int(11) NOT NULL AUTO_INCREMENT,`vendor` varchar(100) NOT NULL,`sku` varchar(100) DEFAULT NULL,`item_name` varchar(191) NOT NULL,`url` text NULL,`unit` varchar(30) DEFAULT NULL,`last_price` decimal(15,2) DEFAULT 0,`dateupdated` datetime NULL,`source` varchar(100) DEFAULT 'Manual',`source_id` int(11) DEFAULT NULL,PRIMARY KEY(`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

    $tables['est_ai_external_price_sources']="CREATE TABLE IF NOT EXISTS `{$prefix}est_ai_external_price_sources` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `source_type` varchar(50) NOT NULL DEFAULT 'Public Page',
      `vendor` varchar(100) NOT NULL,
      `sku` varchar(100) DEFAULT NULL,
      `model_number` varchar(100) DEFAULT NULL,
      `upc` varchar(100) DEFAULT NULL,
      `brand` varchar(191) DEFAULT NULL,
      `item_name` varchar(255) NOT NULL,
      `description` mediumtext NULL,
      `category` varchar(191) DEFAULT NULL,
      `unit` varchar(50) DEFAULT 'each',
      `price_low` decimal(15,2) NOT NULL DEFAULT 0,
      `price_mid` decimal(15,2) NOT NULL DEFAULT 0,
      `price_high` decimal(15,2) NOT NULL DEFAULT 0,
      `last_price` decimal(15,2) NOT NULL DEFAULT 0,
      `zip_code` varchar(20) DEFAULT NULL,
      `store_id` varchar(50) DEFAULT NULL,
      `source_url` text NULL,
      `image_url` text NULL,
      `raw_json` longtext NULL,
      `status` varchar(50) DEFAULT 'active',
      `last_checked` datetime NULL,
      `datecreated` datetime NOT NULL,
      PRIMARY KEY(`id`), KEY `vendor` (`vendor`), KEY `sku` (`sku`), KEY `brand` (`brand`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    $tables['est_ai_price_history']="CREATE TABLE IF NOT EXISTS `{$prefix}est_ai_price_history` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `source_id` int(11) DEFAULT NULL,
      `vendor` varchar(100) NOT NULL,
      `sku` varchar(100) DEFAULT NULL,
      `item_name` varchar(255) DEFAULT NULL,
      `price_low` decimal(15,2) NOT NULL DEFAULT 0,
      `price_mid` decimal(15,2) NOT NULL DEFAULT 0,
      `price_high` decimal(15,2) NOT NULL DEFAULT 0,
      `last_price` decimal(15,2) NOT NULL DEFAULT 0,
      `zip_code` varchar(20) DEFAULT NULL,
      `source_url` text NULL,
      `datecreated` datetime NOT NULL,
      PRIMARY KEY(`id`), KEY `source_id` (`source_id`), KEY `vendor` (`vendor`), KEY `sku` (`sku`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

    $tables['est_ai_activity_log']="CREATE TABLE IF NOT EXISTS `{$prefix}est_ai_activity_log` (`id` int(11) NOT NULL AUTO_INCREMENT,`action` varchar(191) NOT NULL,`description` mediumtext NULL,`staff_id` int(11) DEFAULT NULL,`datecreated` datetime NOT NULL,PRIMARY KEY(`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    foreach($tables as $sql){ $db->query($sql); }
    estimating_hub_ai_add_missing_columns();
    if ($seed && $db->count_all_results(db_prefix().'est_ai_cost_database') == 0) { estimating_hub_ai_seed_cost_items(); }
    if ($seed && function_exists('estimating_hub_ai_seed_expanded_cost_items')) { estimating_hub_ai_seed_expanded_cost_items(); }
    if ($seed && function_exists('estimating_hub_ai_seed_labor_templates_v104')) { estimating_hub_ai_seed_labor_templates_v104(); }
}
function estimating_hub_ai_add_missing_columns(){
    $CI=&get_instance(); $p=db_prefix();
    if($CI->db->table_exists($p.'est_ai_estimates')){
        if(!$CI->db->field_exists('project_address',$p.'est_ai_estimates')) $CI->db->query("ALTER TABLE `{$p}est_ai_estimates` ADD `project_address` varchar(255) NULL AFTER `status`");
        if(!$CI->db->field_exists('ai_questions',$p.'est_ai_estimates')) $CI->db->query("ALTER TABLE `{$p}est_ai_estimates` ADD `ai_questions` mediumtext NULL AFTER `scope_summary`");
    }
    if($CI->db->table_exists($p.'est_ai_items')){
        $cols = [
            'cost_item_id' => "int(11) DEFAULT NULL",
            'division' => "varchar(50) DEFAULT NULL",
            'trade' => "varchar(100) DEFAULT NULL",
            'category' => "varchar(150) DEFAULT NULL",
            'unit' => "varchar(30) NOT NULL DEFAULT 'each'",
            'qty' => "decimal(15,4) NOT NULL DEFAULT 0",
            'material_cost' => "decimal(15,2) NOT NULL DEFAULT 0",
            'labor_hours' => "decimal(15,4) NOT NULL DEFAULT 0",
            'labor_rate' => "decimal(15,2) NOT NULL DEFAULT 0",
            'equipment_cost' => "decimal(15,2) NOT NULL DEFAULT 0",
            'waste_factor' => "decimal(8,4) NOT NULL DEFAULT 0",
            'markup_percent' => "decimal(8,4) NOT NULL DEFAULT 0",
            'total' => "decimal(15,2) NOT NULL DEFAULT 0",
            'source' => "varchar(100) DEFAULT NULL"
        ];
        foreach($cols as $col=>$def){ if(!$CI->db->field_exists($col,$p.'est_ai_items')) $CI->db->query("ALTER TABLE `{$p}est_ai_items` ADD `{$col}` {$def}"); }
    }
    if($CI->db->table_exists($p.'est_ai_cost_database')){
        $cols = [
            'subcategory' => "varchar(150) DEFAULT NULL",
            'labor_cost_low' => "decimal(15,2) NOT NULL DEFAULT 0",
            'labor_cost_typical' => "decimal(15,2) NOT NULL DEFAULT 0",
            'labor_cost_high' => "decimal(15,2) NOT NULL DEFAULT 0",
            'equipment_cost_low' => "decimal(15,2) NOT NULL DEFAULT 0",
            'equipment_cost_typical' => "decimal(15,2) NOT NULL DEFAULT 0",
            'equipment_cost_high' => "decimal(15,2) NOT NULL DEFAULT 0",
            'installed_cost_low' => "decimal(15,2) NOT NULL DEFAULT 0",
            'installed_cost_typical' => "decimal(15,2) NOT NULL DEFAULT 0",
            'installed_cost_high' => "decimal(15,2) NOT NULL DEFAULT 0",
            'crew_size' => "int(11) DEFAULT NULL",
            'margin_low' => "decimal(8,4) NOT NULL DEFAULT 0",
            'margin_typical' => "decimal(8,4) NOT NULL DEFAULT 0",
            'margin_high' => "decimal(8,4) NOT NULL DEFAULT 0",
            'retail_low' => "decimal(15,2) NOT NULL DEFAULT 0",
            'retail_typical' => "decimal(15,2) NOT NULL DEFAULT 0",
            'retail_high' => "decimal(15,2) NOT NULL DEFAULT 0",
            'zip_basis' => "varchar(100) DEFAULT NULL",
            'source' => "varchar(100) DEFAULT 'Manual'",
            'source_type' => "varchar(100) DEFAULT 'Manual'",
            'source_id' => "int(11) DEFAULT NULL",
            'pricing_basis' => "varchar(191) DEFAULT NULL",
            'confidence' => "varchar(50) DEFAULT 'Medium'",
            'notes' => "mediumtext NULL"
        ];
        foreach($cols as $col=>$def){ if(!$CI->db->field_exists($col,$p.'est_ai_cost_database')) $CI->db->query("ALTER TABLE `{$p}est_ai_cost_database` ADD `{$col}` {$def}"); }
    }
    if($CI->db->table_exists($p.'est_ai_vendor_price_links')){
        if(!$CI->db->field_exists('source',$p.'est_ai_vendor_price_links')) $CI->db->query("ALTER TABLE `{$p}est_ai_vendor_price_links` ADD `source` varchar(100) DEFAULT 'Manual' AFTER `dateupdated`");
        if(!$CI->db->field_exists('source_id',$p.'est_ai_vendor_price_links')) $CI->db->query("ALTER TABLE `{$p}est_ai_vendor_price_links` ADD `source_id` int(11) DEFAULT NULL AFTER `source`");
    }

    if($CI->db->table_exists($p.'est_ai_external_price_sources')){
        if(!$CI->db->field_exists('raw_json',$p.'est_ai_external_price_sources')) $CI->db->query("ALTER TABLE `{$p}est_ai_external_price_sources` ADD `raw_json` longtext NULL");
    }
}
function estimating_hub_ai_seed_cost_items(){
    $CI=&get_instance(); $now=date('Y-m-d H:i:s');
    $items=[
      ['03','Concrete','Slabs','4 Inch Concrete Slab','Concrete slab labor and material allowance for Tampa Bay estimating','sf',5.50,7.25,10.50,0.08,75,0.08,'300-500 sf/day',1,1],
      ['06','Framing','Wood Framing','Interior Non Structural 2x4 Wall','Studs, plates, fasteners, labor, layout, and basic blocking','lf',38,52,75,0.65,75,0.10,'80-140 lf/day',0,0],
      ['07','Roofing','Shingles','Architectural Shingle Roof Replacement','Tear off excluded unless added separately','sf',4.50,6.75,9.50,0.06,85,0.12,'8-15 squares/day',1,1],
      ['09','Drywall','Board And Finish','1/2 Inch Drywall Hang Finish Texture','Standard residential drywall install and finish','sf',2.25,3.85,5.75,0.045,65,0.08,'500-900 sf/day',0,0],
      ['09','Painting','Interior Paint','Interior Wall Paint Two Coats','Labor/material allowance for walls only','sf',1.45,2.25,3.50,0.025,55,0.05,'800-1500 sf/day',0,0],
      ['09','Flooring','LVP Flooring','Luxury Vinyl Plank Install','Floor prep excluded unless added separately','sf',3.25,5.50,8.75,0.055,65,0.07,'400-800 sf/day',0,0],
      ['09','Tile','Shower Tile','Shower Wall Tile Install','Waterproofing and substrate may be separate','sf',18,28,45,0.22,75,0.12,'80-160 sf/day',0,0],
      ['22','Plumbing','Water Heater','Standard Electric Water Heater Replacement','Basic replacement allowance, code upgrades separate','each',950,1450,2300,4,85,0.05,'1 unit/day',1,1],
      ['23','HVAC','Heat Pump','2.5 Ton Split Heat Pump Replacement','Equipment selection and duct changes separate','each',6500,9500,14500,16,95,0.03,'1 system/day',1,1],
      ['26','Electrical','Panel Upgrade','Residential 200 Amp Panel Upgrade','Utility and service conditions vary','each',2500,4200,6800,16,95,0.05,'1 panel/day',1,1],
      ['26','Electrical','EV Charger','Level 2 EV Charger Circuit','Distance and panel capacity affect cost','each',750,1450,2800,6,95,0.05,'1 circuit/day',1,1],
      ['32','Fencing','Vinyl Fence','6 Foot Vinyl Privacy Fence','Posts, panels, concrete, and labor allowance','lf',45,68,95,0.10,65,0.07,'80-150 lf/day',1,0],
    ];
    foreach($items as $r){
      $CI->db->insert(db_prefix().'est_ai_cost_database',['division'=>$r[0],'trade'=>$r[1],'category'=>$r[2],'item_name'=>$r[3],'description'=>$r[4],'unit'=>$r[5],'material_cost_low'=>$r[6],'material_cost_typical'=>$r[7],'material_cost_high'=>$r[8],'labor_hours'=>$r[9],'labor_rate'=>$r[10],'waste_factor'=>$r[11],'production_rate'=>$r[12],'permit_required'=>$r[13],'inspection_required'=>$r[14],'region'=>'Tampa Bay','active'=>1,'datecreated'=>$now]);
    }
}

function estimating_hub_ai_seed_expanded_cost_items(){
    $CI=&get_instance(); $p=db_prefix(); if(!$CI->db->table_exists($p.'est_ai_cost_database')) return;
    $now=date('Y-m-d H:i:s');
    $items=[
      ['02','Demolition','Interior Demo','Remove Interior Drywall','Selective demolition and debris handling','sf',1.25,2.10,3.25,0.035,65,0.05,'700-1200 sf/day',0,0],
      ['02','Demolition','Kitchen Demo','Remove Kitchen Cabinets','Remove cabinets and basic haul out','lf',18,32,55,0.35,65,0.03,'25-50 lf/day',0,0],
      ['03','Concrete','Driveway','Concrete Driveway 4 Inch','Form, pour, finish allowance','sf',6.25,8.75,13.50,0.09,75,0.08,'300-600 sf/day',1,1],
      ['06','Framing','Structural','Install LVL Beam Allowance','Beam labor allowance, engineering separate','lf',95,155,260,1.25,85,0.05,'20-40 lf/day',1,1],
      ['06','Framing','Exterior Wall','2x6 Exterior Framed Wall','Wood framing, plates, studs, blocking','lf',58,82,125,0.85,75,0.10,'60-110 lf/day',1,1],
      ['07','Roofing','Decking','Replace Roof Plywood Decking','Per sheet decking replacement allowance','sheet',75,125,190,0.75,75,0.08,'15-30 sheets/day',1,1],
      ['07','Roofing','Flashing','Replace Pipe Boot Flashing','Roof pipe boot replacement allowance','each',95,185,325,1.25,85,0.05,'4-8/day',0,0],
      ['08','Doors','Interior Door','Install Prehung Interior Door','Standard prehung install and trim touchup','each',185,325,525,2.5,65,0.03,'3-5/day',0,0],
      ['08','Windows','Impact Window','Install Impact Window','Labor allowance, product separate','each',450,750,1250,4.5,75,0.05,'2-4/day',1,1],
      ['09','Drywall','Repair','Small Drywall Patch','Patch, tape, mud, texture allowance','each',175,325,650,3.5,65,0.08,'2-4/day',0,0],
      ['09','Painting','Exterior','Exterior Paint Stucco','Prep, prime as needed, two coats','sf',1.75,2.95,4.85,0.035,65,0.08,'800-1400 sf/day',0,0],
      ['09','Flooring','Baseboard','Install 5 1/4 Baseboard','Material and labor allowance','lf',3.50,5.75,8.50,0.06,65,0.08,'300-600 lf/day',0,0],
      ['09','Tile','Floor Tile','Install Floor Tile','Thinset, grout, setting labor, tile separate','sf',9.50,15.50,26.00,0.16,75,0.12,'120-250 sf/day',0,0],
      ['10','Specialties','Shower Glass','Install Shower Glass Door','Standard glass door allowance','each',850,1450,2600,4,75,0.03,'1/day',0,0],
      ['12','Cabinets','Kitchen Cabinets','Install Stock Cabinets','Cabinet install labor allowance','lf',85,145,240,1.2,75,0.05,'20-40 lf/day',0,0],
      ['12','Countertops','Quartz','Install Quartz Countertop','Template and install allowance, product varies','sf',65,95,145,0.12,85,0.06,'80-150 sf/day',0,0],
      ['21','Fire','Smoke Detector','Install Smoke Detector','Device and labor allowance','each',85,145,250,0.75,85,0.02,'6-12/day',0,1],
      ['22','Plumbing','Fixture','Install Toilet','Standard toilet replacement','each',225,385,650,2,85,0.03,'3-5/day',0,0],
      ['22','Plumbing','Fixture','Install Bathroom Vanity Faucet','Faucet install allowance','each',150,275,475,1.5,85,0.03,'3-6/day',0,0],
      ['22','Plumbing','Repiping','PEX Water Line','PEX branch line allowance','lf',9.50,16.50,28.00,0.12,85,0.08,'100-200 lf/day',1,1],
      ['23','HVAC','Ductwork','Install 6 Inch Flex Duct','Flex duct labor and material allowance','lf',11.50,18.75,32.00,0.09,85,0.08,'150-300 lf/day',1,1],
      ['23','HVAC','Mini Split','Install 18k Mini Split','Equipment, line set and labor allowance varies','each',3500,5200,7800,12,95,0.03,'1/day',1,1],
      ['26','Electrical','Lighting','Install Recessed Light','Fixture, wiring allowance varies by access','each',125,225,385,1.5,95,0.03,'4-8/day',0,1],
      ['26','Electrical','Outlet','Add Standard Outlet','Wiring distance and wall access vary','each',165,285,525,2,95,0.03,'3-6/day',0,1],
      ['26','Electrical','Generator','Generator Interlock Setup','Panel condition and utility rules vary','each',750,1450,2600,8,95,0.05,'1/day',1,1],
      ['31','Sitework','Dumpster','15 Yard Dumpster Allowance','Haul and disposal allowance','each',425,625,850,0,0,0,'1 unit',0,0],
      ['32','Fence','Wood Fence','6 Foot Wood Privacy Fence','Posts, rails, pickets, concrete, labor','lf',32,48,75,0.12,65,0.10,'80-160 lf/day',1,0],
      ['32','Concrete','Patio','Concrete Patio 4 Inch','Form, pour, finish patio allowance','sf',6.00,8.25,12.50,0.08,75,0.08,'300-600 sf/day',1,1],
    ];
    foreach($items as $r){
        $exists=$CI->db->where('item_name',$r[3])->get($p.'est_ai_cost_database')->row();
        if($exists) continue;
        $CI->db->insert($p.'est_ai_cost_database',['division'=>$r[0],'trade'=>$r[1],'category'=>$r[2],'item_name'=>$r[3],'description'=>$r[4],'unit'=>$r[5],'material_cost_low'=>$r[6],'material_cost_typical'=>$r[7],'material_cost_high'=>$r[8],'labor_hours'=>$r[9],'labor_rate'=>$r[10],'waste_factor'=>$r[11],'production_rate'=>$r[12],'permit_required'=>$r[13],'inspection_required'=>$r[14],'region'=>'Tampa Bay','active'=>1,'source'=>'Smart Choice Seed','datecreated'=>$now]);
    }
}


function estimating_hub_ai_seed_labor_templates_v104(){
    $CI=&get_instance(); $p=db_prefix(); if(!$CI->db->table_exists($p.'est_ai_cost_database')) return;
    $now=date('Y-m-d H:i:s');
    $items=[
      ['00','Template','Garage Conversion','Garage Conversion To Mini Apartment','Base estimating template for garage conversion to mini apartment. Includes framing, insulation, drywall, electrical allowance, plumbing allowance, bathroom allowance, kitchenette allowance, flooring, paint, trim, permits, and contingency. Verify scope and dimensions before sending.','project',18500,32500,58000,180,75,0.12,'1 project / 3-8 weeks',1,1],
      ['09','Bathroom','Bathroom Remodel','8x5 Bathroom Full Gut Remodel','Full gut bathroom remodel to studs. Includes demolition, waterproofing allowance, tile allowance, fixtures allowance, drywall, paint, plumbing/electrical allowance, and finish labor. Verify tile, vanity, glass, and plumbing relocation.','project',8500,14500,26000,95,75,0.12,'1 bathroom / 2-4 weeks',1,1],
      ['12','Cabinets','Labor','Build Kitchen Cabinet Labor','Smart Choice cabinet assembly/build labor allowance per cabinet box. User standard: 60 dollars to build.','each',60,60,60,0.75,80,0.03,'8-14 cabinets/day','0','0'],
      ['12','Cabinets','Labor','Install Kitchen Cabinet Labor','Smart Choice cabinet installation labor allowance per cabinet. User standard: 80 dollars to install.','each',80,80,80,1.0,80,0.03,'6-10 cabinets/day','0','0'],
      ['26','Electrical','Panel','Change Electrical Panel','Smart Choice standard panel change starting allowance. Verify service size, meter, utility, grounding, permits, and inspection.','each',2500,2500,4200,16,95,0.05,'1 panel/day','1','1'],
      ['26','Electrical','Lighting','Replace LED Lamp With Fixture Provided','Replace LED lamp or light fixture with new provided fixture. Includes basic labor allowance and standard replacement conditions.','each',125,125,225,1.0,95,0.02,'6-10 lights/day','0','0'],
      ['03','Concrete','Small Slab','Small Concrete Slab 4 Inch','Small slab template for patios, shed pads, and small additions. Verify access, forming, reinforcement, demo, and permit.','sf',7.50,10.50,16.50,0.1,75,0.1,'250-500 sf/day','1','1'],
      ['06','Framing','Interior Buildout','Build Interior Partition Wall','2x4 wall labor/material allowance for interior layout changes. Verify height, doors, headers, electrical, and drywall finish.','lf',45,68,110,0.75,75,0.1,'60-120 lf/day','0','0'],
      ['09','Drywall','Finish','Level 4 Drywall Finish','Tape, mud, sand, texture-ready drywall finish allowance.','sf',1.45,2.35,3.75,0.035,65,0.05,'700-1200 sf/day','0','0'],
      ['09','Paint','Interior','Interior Paint Walls And Trim','Interior paint package allowance for walls and trim. Verify colors, repairs, primer, and occupied conditions.','sf',1.85,2.95,4.75,0.03,65,0.06,'800-1400 sf/day','0','0']
    ];
    foreach($items as $r){
        $exists=$CI->db->where('item_name',$r[3])->get($p.'est_ai_cost_database')->row();
        if($exists) continue;
        $CI->db->insert($p.'est_ai_cost_database',['division'=>$r[0],'trade'=>$r[1],'category'=>$r[2],'item_name'=>$r[3],'description'=>$r[4],'unit'=>$r[5],'material_cost_low'=>$r[6],'material_cost_typical'=>$r[7],'material_cost_high'=>$r[8],'labor_hours'=>$r[9],'labor_rate'=>$r[10],'waste_factor'=>$r[11],'production_rate'=>$r[12],'permit_required'=>(int)$r[13],'inspection_required'=>(int)$r[14],'region'=>'Tampa Bay','active'=>1,'source'=>'Smart Choice Labor Template','datecreated'=>$now]);
    }
}

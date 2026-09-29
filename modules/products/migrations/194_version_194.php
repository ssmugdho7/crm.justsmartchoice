<?php

defined('BASEPATH') or exit('No direct script access allowed');

if (!function_exists('products_smartchoice_catalog_seed_194')) {
    function products_smartchoice_catalog_seed_194()
    {
        $CI = &get_instance();
        add_option('products_appointment_booking_url', site_url('appointments'));

        if (!$CI->db->table_exists(db_prefix() . 'product_master') || !$CI->db->table_exists(db_prefix() . 'product_categories')) {
            return;
        }

        $CI->db->query('SET foreign_key_checks = 0');

        // Remove messy/duplicate homeowner-facing category names by moving related services into clean categories.
        $cleanCategories = [
            'Flooring', 'Doors', 'Windows', 'Bathrooms', 'Kitchen', 'Painting', 'Drywall', 'Electrical', 'Plumbing', 'HVAC', 'Roofing', 'Concrete', 'Fencing', 'Engineering & Permits', 'Design Services', 'Cleaning', 'Miscellaneous'
        ];
        $categoryIds = [];
        foreach ($cleanCategories as $name) {
            $CI->db->where('p_category_name', $name);
            $row = $CI->db->get(db_prefix() . 'product_categories')->row();
            if ($row) {
                $categoryIds[$name] = (int) $row->p_category_id;
                $CI->db->where('p_category_id', $row->p_category_id)->update(db_prefix() . 'product_categories', ['p_category_description' => $name . ' services']);
            } else {
                $CI->db->insert(db_prefix() . 'product_categories', ['p_category_name' => $name, 'p_category_description' => $name . ' services']);
                $categoryIds[$name] = (int) $CI->db->insert_id();
            }
        }

        $products = $CI->db->get(db_prefix() . 'product_master')->result();
        foreach ($products as $product) {
            $name = strtolower($product->product_name . ' ' . $product->product_description);
            $category = 'Miscellaneous';
            if (preg_match('/floor|vinyl|laminate|tile floor|hardwood|baseboard|stair tread|moisture/', $name)) { $category = 'Flooring'; }
            elseif (preg_match('/door|french|sliding|garage door|pocket|barn/', $name)) { $category = 'Doors'; }
            elseif (preg_match('/window|screen|caulk/', $name)) { $category = 'Windows'; }
            elseif (preg_match('/bath|shower|toilet|vanity|tub|waterproof/', $name)) { $category = 'Bathrooms'; }
            elseif (preg_match('/kitchen|cabinet|countertop|backsplash|pantry|garbage disposal/', $name)) { $category = 'Kitchen'; }
            elseif (preg_match('/paint|primer|accent wall|pressure washing before paint/', $name)) { $category = 'Painting'; }
            elseif (preg_match('/drywall|texture|corner bead|sanding/', $name)) { $category = 'Drywall'; }
            elseif (preg_match('/electrical|panel|ev charger|fixture|outlet|switch|circuit|generator|smoke detector/', $name)) { $category = 'Electrical'; }
            elseif (preg_match('/plumb|water heater|tankless|leak|faucet|drain|shower valve|camera inspection/', $name)) { $category = 'Plumbing'; }
            elseif (preg_match('/hvac|mini split|thermostat|duct|air handler|return air|supply vent|dryer vent|condensate|attic duct/', $name)) { $category = 'HVAC'; }
            elseif (preg_match('/roof|shingle|fascia|soffit|gutter/', $name)) { $category = 'Roofing'; }
            elseif (preg_match('/concrete|slab|driveway|sidewalk|footer|paver|curb/', $name)) { $category = 'Concrete'; }
            elseif (preg_match('/fence|gate|post replacement|hoa fence/', $name)) { $category = 'Fencing'; }
            elseif (preg_match('/engineer|engineering|permit|wind load|energy calculation|as built|site plan|revision response|seal/', $name)) { $category = 'Engineering & Permits'; }
            elseif (preg_match('/design|render|selection board|layout|color consultation|lighting design|scope package/', $name)) { $category = 'Design Services'; }
            elseif (preg_match('/clean|hauling|garage cleanout|debris|dust|appliance cleaning/', $name)) { $category = 'Cleaning'; }
            $CI->db->where('id', $product->id)->update(db_prefix() . 'product_master', [
                'product_category_id' => $categoryIds[$category],
                'product_description' => trim(str_replace(['&nbsp;', html_entity_decode('&nbsp;')], ' ', strip_tags($product->product_description)))
            ]);
        }

        $marketRates = [
            'Interior Door Installation' => 450, 'Exterior Door Installation' => 650, 'French Door Installation' => 1850, 'Sliding Door Installation' => 950,
            'Storm Door Installation' => 375, 'Door Trim Installation' => 225, 'Door Painting' => 150, 'Pocket Door Installation' => 850,
            'Barn Door Installation' => 425, 'Fire Rated Door Installation' => 675, 'Door Hardware Installation' => 125,
            'Garage Door Replacement' => 1250, 'Garage Door Installation' => 1250,
            'Window Installation' => 575, 'Window Replacement' => 625, 'Impact Window Upgrade' => 875, 'Window Cleaning' => 185,
            'Window Cleaning Package' => 185, 'Window Trim Repair' => 225, 'Window Screen Replacement' => 85, 'Window Caulking' => 125,
            'Window Disposal Service' => 95, 'Second Floor Window Installation' => 775, 'Window Permit Assistance' => 250,
            'Fence Installation' => 1850, 'Wood Fence Installation' => 1850, 'Vinyl Fence Installation' => 2400, 'Chain Link Fence Installation' => 1600,
            'Fence Repair' => 350, 'Gate Installation' => 425, 'Double Gate Installation' => 750, 'Fence Removal' => 450, 'Fence Permit Help' => 225,
            'Engineering Digital Signature' => 350, 'Digital Engineer Seal' => 350, 'Structural Engineering Letter' => 475, 'MEP Engineering Package' => 950,
            'Energy Calculations' => 450, 'Wind Load Report' => 450, 'Permit Drawing Package' => 850, 'As Built Drawing' => 650,
            'Site Plan Drafting' => 450, 'Revision Response' => 250, 'Commercial Permit Package' => 1500,
            'Luxury Vinyl Plank Installation' => 450, 'Laminate Flooring Installation' => 425, 'Tile Flooring Installation' => 650,
            'Engineered Hardwood Installation' => 650, 'Floor Removal' => 350, 'Floor Leveling' => 450, 'Baseboard Installation' => 300,
            'Stair Tread Installation' => 650, 'Moisture Barrier Installation' => 250, 'Glue Down Vinyl Installation' => 500,
            'Interior Painting' => 650, 'Exterior Painting' => 1200, 'Trim Painting' => 350, 'Ceiling Painting' => 450,
            'Drywall Patch Repair' => 225, 'Drywall Hanging' => 650, 'Drywall Finishing' => 550, 'Texture Matching' => 275,
            'Panel Upgrade' => 2400, 'EV Charger Installation' => 750, 'Ceiling Fan Installation' => 175, 'Light Fixture Installation' => 150,
            'Outlet Installation' => 175, 'Switch Replacement' => 125, 'Dedicated Circuit' => 450, 'Generator Inlet' => 750,
            'Water Heater Installation' => 1100, 'Tankless Water Heater' => 2200, 'Toilet Replacement' => 250, 'Sink Installation' => 325,
            'Leak Repair' => 225, 'Faucet Installation' => 225, 'Drain Cleaning' => 185,
            'Mini Split Installation' => 2500, 'Thermostat Installation' => 185, 'Duct Repair' => 350, 'HVAC Maintenance Visit' => 150,
            'Shingle Roof Repair' => 450, 'Flat Roof Repair' => 550, 'Roof Inspection' => 185, 'Fascia Repair' => 350, 'Soffit Installation' => 650,
            'Concrete Slab' => 1800, 'Driveway Concrete' => 3500, 'Sidewalk Concrete' => 950, 'Concrete Demo' => 750,
            'Post Construction Cleaning' => 350, 'Move In Cleaning' => 275, 'Pressure Washing' => 250, 'Debris Hauling' => 250,
        ];

        $products = $CI->db->get(db_prefix() . 'product_master')->result();
        foreach ($products as $product) {
            $base = null;
            foreach ($marketRates as $needle => $rate) {
                if (stripos($product->product_name, $needle) !== false) { $base = $rate; break; }
            }
            if ($base === null) {
                $categoryName = '';
                foreach ($categoryIds as $cat => $id) { if ((int) $product->product_category_id === (int) $id) { $categoryName = $cat; break; } }
                $defaults = ['Flooring'=>450,'Doors'=>425,'Windows'=>425,'Bathrooms'=>650,'Kitchen'=>550,'Painting'=>450,'Drywall'=>350,'Electrical'=>225,'Plumbing'=>225,'HVAC'=>350,'Roofing'=>450,'Concrete'=>750,'Fencing'=>650,'Engineering & Permits'=>350,'Design Services'=>250,'Cleaning'=>185,'Miscellaneous'=>175];
                $base = $defaults[$categoryName] ?? 175;
            }
            $CI->db->where('id', $product->id)->update(db_prefix() . 'product_master', ['rate' => $base, 'quantity_number' => 999, 'is_variation' => 1]);
        }

        $getVariation = function($name) use ($CI) {
            $CI->db->where('name', $name); $row = $CI->db->get(db_prefix().'variations')->row();
            if ($row) { return (int) $row->id; }
            $CI->db->insert(db_prefix().'variations', ['name'=>$name, 'description'=>$name]); return (int) $CI->db->insert_id();
        };
        $getValue = function($variationId, $value, $order) use ($CI) {
            $CI->db->where('variation_id', $variationId)->where('value', $value); $row = $CI->db->get(db_prefix().'variation_values')->row();
            if ($row) { return (int) $row->id; }
            $CI->db->insert(db_prefix().'variation_values', ['variation_id'=>$variationId, 'value'=>$value, 'value_order'=>$order, 'description'=>$value]); return (int) $CI->db->insert_id();
        };
        $addVariation = function($productId, $group, $value, $rate, $order) use ($CI, $getVariation, $getValue) {
            $variationId = $getVariation($group);
            $valueId = $getValue($variationId, $value, $order);
            $CI->db->where(['product_id'=>$productId, 'variation_id'=>$variationId, 'variation_value_id'=>$valueId]);
            $exists = $CI->db->get(db_prefix().'product_variations')->row();
            $data = ['product_id'=>$productId, 'variation_id'=>$variationId, 'variation_value_id'=>$valueId, 'rate'=>$rate, 'quantity_number'=>999];
            if ($exists) { $CI->db->where('id', $exists->id)->update(db_prefix().'product_variations', $data); }
            else { $CI->db->insert(db_prefix().'product_variations', $data); }
        };

        $products = $CI->db->get(db_prefix() . 'product_master')->result();
        foreach ($products as $product) {
            $base = (float) $product->rate;
            $lower = strtolower($product->product_name);
            $CI->db->where('product_id', $product->id);
            $currentCount = $CI->db->count_all_results(db_prefix().'product_variations');
            if ($currentCount < 1 || preg_match('/garage door|interior door|window|fence|engineering|permit/', $lower)) {
                if (strpos($lower, 'garage door') !== false) {
                    $groups = ['Door Size'=>['No Selection'=>0,'8 ft x 7 ft'=>0,'9 ft x 7 ft'=>150,'16 ft x 7 ft'=>650,'16 ft x 8 ft'=>850], 'Old Door Disposal'=>['No Selection'=>0,'Leave Old Door On Site'=>0,'Haul Away Old Door'=>125], 'Opener'=>['No Selection'=>0,'No Opener'=>0,'Standard Opener'=>450,'Smart Opener'=>575]];
                } elseif (strpos($lower, 'interior door') !== false || strpos($lower, 'door') !== false) {
                    $groups = ['Door Width'=>['No Selection'=>0,'24 in'=>0,'28 in'=>25,'30 in'=>35,'32 in'=>45,'36 in'=>75], 'Door Height'=>['No Selection'=>0,'80 in'=>0,'84 in'=>95,'96 in'=>175], 'Door Type'=>['No Selection'=>0,'Hollow Core'=>0,'Solid Core'=>125,'Fire Rated'=>225], 'Floor Level'=>['No Selection'=>0,'First Floor'=>0,'Second Floor'=>125,'Third Floor'=>225], 'Old Door Removal'=>['No Selection'=>0,'No'=>0,'Yes'=>75], 'Trim Option'=>['No Selection'=>0,'Existing Trim'=>0,'New Trim'=>125], 'Paint Option'=>['No Selection'=>0,'Unpainted'=>0,'Paint Door'=>125]];
                } elseif (strpos($lower, 'window clean') !== false) {
                    $groups = ['Window Count'=>['No Selection'=>0,'1-10 Windows'=>0,'11-20 Windows'=>115,'21-30 Windows'=>225,'31-40 Windows'=>340,'40+ Windows'=>475], 'Cleaning Scope'=>['No Selection'=>0,'Outside Only'=>0,'Inside Only'=>35,'Inside and Outside'=>85], 'Screen Cleaning'=>['No Selection'=>0,'No'=>0,'Screens Included'=>65], 'Track Cleaning'=>['No Selection'=>0,'No'=>0,'Tracks Included'=>75], 'Hard Water Removal'=>['No Selection'=>0,'No'=>0,'Hard Water Treatment'=>150]];
                } elseif (strpos($lower, 'window') !== false) {
                    $groups = ['Window Type'=>['No Selection'=>0,'Single Hung'=>0,'Double Hung'=>85,'Slider'=>125,'Impact'=>275], 'Window Size'=>['No Selection'=>0,'Small'=>0,'Medium'=>95,'Large'=>185], 'Old Window Disposal'=>['No Selection'=>0,'Leave On Site'=>0,'Haul Away Old Window'=>95], 'Story Level'=>['No Selection'=>0,'First Floor'=>0,'Second Floor'=>175], 'Permit Required'=>['No Selection'=>0,'No'=>0,'Yes'=>250]];
                } elseif (strpos($lower, 'fence') !== false || strpos($lower, 'gate') !== false) {
                    $groups = ['Fence Height'=>['No Selection'=>0,'4 ft'=>0,'6 ft'=>350,'8 ft'=>750], 'Fence Material'=>['No Selection'=>0,'Wood'=>0,'Vinyl'=>650,'Aluminum'=>850,'Chain Link'=>-150], 'Gate Option'=>['No Selection'=>0,'No Gate'=>0,'48 in Gate'=>425,'60 in Gate'=>525,'72 in Gate'=>625], 'Double Gate Option'=>['No Selection'=>0,'None'=>0,'8 ft Double Gate'=>750,'10 ft Double Gate'=>950,'12 ft Double Gate'=>1150], 'Remove Existing Fence'=>['No Selection'=>0,'No'=>0,'Yes'=>450], 'Permit Help'=>['No Selection'=>0,'No'=>0,'Yes'=>225], 'HOA Help'=>['No Selection'=>0,'No'=>0,'Yes'=>125]];
                } elseif (strpos($lower, 'engineer') !== false || strpos($lower, 'permit') !== false || strpos($lower, 'drawing') !== false || strpos($lower, 'wind') !== false) {
                    $groups = ['Engineering Tier'=>['No Selection'=>0,'Up to $2,500'=>0,'$2,500-$5,000'=>250,'$5,000-$10,000'=>600,'$10,000+'=>1100], 'Project Type'=>['No Selection'=>0,'Residential'=>0,'Commercial'=>650], 'Plan Type'=>['No Selection'=>0,'Structural'=>250,'MEP'=>450,'Energy Calculation'=>125,'Wind Load'=>150], 'Delivery'=>['No Selection'=>0,'Digital Signature'=>0,'Permit Package'=>250]];
                } else {
                    $groups = ['Service Level'=>['No Selection'=>0,'Basic'=>0,'Standard Plus'=>150,'Premium'=>350], 'Floor Level'=>['No Selection'=>0,'First Floor'=>0,'Second Floor'=>125], 'Removal / Disposal'=>['No Selection'=>0,'No'=>0,'Yes'=>150], 'Permit Required'=>['No Selection'=>0,'No'=>0,'Yes'=>250]];
                }
                $order = 1;
                foreach ($groups as $group => $values) {
                    $inner = 1;
                    foreach ($values as $value => $add) {
                        $addVariation($product->id, $group, $value, max(0, $base + $add), $inner);
                        $inner++;
                    }
                    $order++;
                }
            }
        }

        $CI->db->query('SET foreign_key_checks = 1');
    }
}

class Migration_Version_194 extends App_module_migration
{
    public function up()
    {
        products_smartchoice_catalog_seed_194();
    }

    public function down()
    {
        // Keep final Smart Choice catalog polish in place on rollback.
    }
}

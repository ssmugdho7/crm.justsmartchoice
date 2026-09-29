<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_192 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();

        if (!$CI->db->table_exists(db_prefix().'product_images')) {
            $CI->db->query('CREATE TABLE `'.db_prefix().'product_images` (
                `id` INT NOT NULL AUTO_INCREMENT,
                `product_id` INT NOT NULL DEFAULT 0,
                `image` VARCHAR(255) NOT NULL,
                `is_primary` TINYINT(1) NOT NULL DEFAULT 0,
                `datecreated` DATETIME NULL,
                PRIMARY KEY (`id`),
                KEY `product_id` (`product_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET='.$CI->db->char_set.';');
        }

        if ($CI->db->table_exists(db_prefix().'product_master') && !$CI->db->field_exists('smart_choice_product_type', db_prefix().'product_master')) {
            $CI->db->query('ALTER TABLE `'.db_prefix().'product_master` ADD `smart_choice_product_type` VARCHAR(30) NULL DEFAULT "product" AFTER `product_category_id`');
        }

        update_option('coupons_disabled', 0);
        $this->seed_categories($CI);
        $this->seed_coupons($CI);
        $this->seed_variations($CI);
        $this->seed_sample_products($CI);
    }

    private function seed_categories($CI)
    {
        if (!$CI->db->table_exists(db_prefix().'product_categories')) { return; }
        $cats = [
            ['Decking Services','Deck repairs, railings, stairs, framing, and deck finishes.'],
            ['Roofing Services','Roof repairs, roof replacement, leak diagnosis, shingles, flat roof, and flashing.'],
            ['Electrical Services','Panel changes, lighting, outlets, EV chargers, troubleshooting, and service calls.'],
            ['Plumbing Services','Water heaters, fixtures, drains, faucets, toilets, and rough-in work.'],
            ['Garage Doors','Garage doors, openers, remotes, tracks, springs, and service calls.'],
            ['Doors And Windows','Interior doors, exterior doors, impact windows, and sliding doors.'],
            ['Painting Services','Interior painting, exterior painting, trim, ceilings, and prep.'],
            ['Flooring And Tile','LVP, tile, laminate, floor preparation, baseboards, and transitions.'],
            ['Bathroom Remodeling','Bathroom remodel packages, shower systems, vanities, and toilets.'],
            ['Kitchen Remodeling','Cabinets, counters, backsplash, sinks, faucets, and kitchen labor.'],
        ];
        foreach ($cats as $cat) {
            $exists = $CI->db->where('p_category_name', $cat[0])->get(db_prefix().'product_categories')->row();
            if (!$exists) {
                $CI->db->insert(db_prefix().'product_categories', ['p_category_name'=>$cat[0], 'p_category_description'=>$cat[1]]);
            }
        }
    }

    private function seed_coupons($CI)
    {
        if (!$CI->db->table_exists(db_prefix().'coupons')) { return; }
        $coupons = [
            ['DECK250','fixed',250,'Decking service discount'],
            ['ROOF500','fixed',500,'Roof replacement project discount'],
            ['ELECTRIC125','fixed',125,'Electrical service discount'],
            ['PLUMBING100','fixed',100,'Plumbing service discount'],
            ['BATH5','percent',5,'Bathroom remodel percentage discount'],
            ['KITCHEN750','fixed',750,'Kitchen remodel discount'],
            ['FLOOR10','percent',10,'Flooring installation discount'],
            ['PAINT300','fixed',300,'Painting project discount'],
            ['GARAGE150','fixed',150,'Garage door service discount'],
            ['WINDOW5','percent',5,'Window and door project discount'],
        ];
        foreach ($coupons as $c) {
            $exists = $CI->db->where('code',$c[0])->get(db_prefix().'coupons')->row();
            if (!$exists) {
                $CI->db->insert(db_prefix().'coupons', [
                    'code'=>$c[0], 'type'=>$c[1], 'amount'=>$c[2],
                    'max_uses'=>0, 'max_uses_per_client'=>1,
                    'start_date'=>date('Y-m-d'), 'end_date'=>date('Y-m-d', strtotime('+2 years')),
                ]);
            }
        }
    }

    private function seed_variations($CI)
    {
        if (!$CI->db->table_exists(db_prefix().'variations') || !$CI->db->table_exists(db_prefix().'variation_values')) { return; }
        $vars = [
            'Garage Door Quantity'=>['One Garage Door','Two Garage Doors','Three Garage Doors'],
            'Garage Opener Type'=>['Chain Drive','Belt Drive','Smart WiFi Opener','Heavy Duty Opener'],
            'Door Width'=>['30 Inch','32 Inch','36 Inch','Custom Width'],
            'Door Type'=>['Interior Door','Exterior Door','Impact Exterior Door','Fire Rated Door'],
            'Painting Height'=>['8 Foot Ceiling','9 To 10 Foot Ceiling','Over 10 Foot Ceiling','Two Story Area'],
            'Painting Colors'=>['One Color','Two Colors','Three Colors','Custom Color Package'],
            'Painting Surface'=>['Walls Only','Walls And Ceilings','Walls Trim And Doors','Exterior Stucco'],
            'Tile Site Condition'=>['Empty House','Furnished House','Occupied Home','Commercial Area'],
            'Tile Size'=>['12x24 Tile','24x24 Tile','Mosaic Tile','Large Format Tile'],
            'Fence Material'=>['Pressure Treated Wood','Vinyl Fence','Aluminum Fence','Chain Link Fence'],
            'Fence Height'=>['4 Foot','6 Foot','8 Foot','Custom Height'],
            'Water Heater Size'=>['30 Gallon','40 Gallon','50 Gallon','Tankless'],
            'Water Heater Disposal'=>['Customer Keeps Old Unit','Contractor Hauls Away','Disposal Included'],
            'Electrical Panel Size'=>['100 Amp','150 Amp','200 Amp','400 Amp'],
            'LED Fixture Type'=>['Basic LED','Recessed LED','Smart LED','Commercial LED'],
            'Roof Material'=>['Architectural Shingle','Metal Roof','TPO Flat Roof','Tile Roof'],
            'Drywall Finish'=>['Level 3','Level 4','Level 5','Texture Match'],
            'Bathroom Scope'=>['Toilet Only','Vanity And Toilet','Shower Conversion','Full Gut Remodel'],
            'Kitchen Cabinet Labor'=>['Assemble Only','Install Only','Assemble And Install','Custom Modification'],
            'Site Access'=>['Easy Access','Second Floor','Tight Access','Occupied Home'],
        ];
        $order=1;
        foreach ($vars as $name=>$values) {
            $row = $CI->db->where('name',$name)->get(db_prefix().'variations')->row();
            if (!$row) {
                $CI->db->insert(db_prefix().'variations', ['name'=>$name, 'description'=>'Smart Choice construction variation for pricing and checkout.']);
                $variation_id = $CI->db->insert_id();
            } else { $variation_id = $row->id; }
            $idx=1;
            foreach ($values as $value) {
                $exists = $CI->db->where('variation_id',$variation_id)->where('value',$value)->get(db_prefix().'variation_values')->row();
                if (!$exists) {
                    $CI->db->insert(db_prefix().'variation_values', [
                        'variation_id'=>$variation_id,'value'=>$value,'value_order'=>$idx,'description'=>$value,
                    ]);
                }
                $idx++;
            }
            $order++;
        }
    }

    private function seed_sample_products($CI)
    {
        if (!$CI->db->table_exists(db_prefix().'product_master')) { return; }
        $category = $CI->db->where('p_category_name','Electrical Services')->get(db_prefix().'product_categories')->row();
        $catid = $category ? $category->p_category_id : 0;
        $samples = [
            ['Electrical Panel Change','Replace electrical panel service allowance. Final price depends on existing conditions, utility requirements, grounding, permits, and inspections.',2500,10],
            ['LED Fixture Replacement','Replace existing light fixture with new LED fixture, including standard fixture allowance and basic labor.',125,50],
            ['Water Heater Replacement','Standard water heater replacement allowance. Disposal, code upgrades, permits, and access may change final price.',1450,10],
            ['Garage Door Opener Install','Garage door opener installation allowance with common residential opener options.',650,10],
        ];
        foreach ($samples as $s) {
            $exists = $CI->db->where('product_name',$s[0])->get(db_prefix().'product_master')->row();
            if (!$exists) {
                $CI->db->insert(db_prefix().'product_master', [
                    'product_name'=>$s[0], 'product_description'=>$s[1], 'product_category_id'=>$catid,
                    'smart_choice_product_type'=>'product', 'rate'=>$s[2], 'quantity_number'=>$s[3], 'is_digital'=>0,
                    'taxes'=>'', 'recurring'=>0, 'recurring_type'=>'', 'custom_recurring'=>0, 'cycles'=>0, 'is_variation'=>0,
                ]);
            }
        }
    }


    public function down()
    {
        // Safe no-op rollback for Perfex module upgrader compatibility.
    }
}

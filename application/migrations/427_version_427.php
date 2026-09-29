<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_427 extends CI_Migration
{
    public function up()
    {
        update_option('sc_crm_build_version', '4.2.7');
        update_option('smart_choice_crm_build', '4.2.7');
        update_option('smart_choice_crm_current_version', '4.2.7');

        // Add missing construction-company ticket services without deleting or renaming existing services.
        $services = [
            'Plumbing Service and Repair',
            'Electrical Service and Repair',
            'Engineering Drawings',
            'Architectural Drafting',
            'Permit Drawings and Coordination',
            'Roofing Repair',
            'Roof Replacement',
            'Shingle Roofing',
            'Metal Roofing',
            'TPO and Flat Roofing',
            'Tile Roofing',
            'Windows and Doors',
            'Garage Door Repair',
            'Fencing Installation',
            'Fence Repair',
            'Interior Painting',
            'Exterior Painting',
            'Drywall Repair and Installation',
            'Texture and Finishing',
            'Stucco Repair and Installation',
            'HVAC Service and Installation',
            'Kitchen Remodeling',
            'Bathroom Remodeling',
            'Flooring and Tile',
            'Deck Construction and Repair',
            'Screen Enclosures and Pool Cages',
            'Concrete and Masonry',
            'Framing and Carpentry',
            'Soffit and Fascia',
            'Gutters and Drainage',
            'General Home Repairs',
            'New Construction',
            'Commercial Construction and Repairs',
            'Property Maintenance',
            'Site Inspection and Consultation',
            'Solar Consultation and Installation',
            'Energy Calculations',
            'NOC and Permit Documentation',
            'Demolition and Haul Away',
            'Emergency Repair Service',
        ];
        foreach ($services as $service) {
            $exists = (int) $this->db->query('SELECT COUNT(*) AS total FROM `' . db_prefix() . 'services` WHERE LOWER(`name`) = ?', [strtolower($service)])->row()->total;
            if (!$exists) {
                $this->db->insert(db_prefix() . 'services', ['name' => $service]);
            }
        }
    }

    public function down()
    {
        // Non-destructive rollback: keep services and data created by this release.
        update_option('sc_crm_build_version', '4.2.6');
        update_option('smart_choice_crm_build', '4.2.6');
        update_option('smart_choice_crm_current_version', '4.2.6');
    }
}

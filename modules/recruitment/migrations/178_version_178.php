<?php defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_178 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();

        if (function_exists('add_option')) {
            add_option('smart_choice_recruitment_upgrade_178_portal_layout_fix', '1');
        }

        $position_table = db_prefix() . 'rec_job_position';
        $campaign_table = db_prefix() . 'rec_campaign';

        if ($CI->db->table_exists($position_table) && $CI->db->table_exists($campaign_table)) {
            $position_fields = $CI->db->list_fields($position_table);
            $campaigns = $CI->db->select('cp_position, campaign_name, cp_job_description')
                ->from($campaign_table)
                ->where('cp_position IS NOT NULL', null, false)
                ->get()->result();

            foreach ($campaigns as $campaign) {
                if (!empty($campaign->cp_position) && is_numeric($campaign->cp_position)) {
                    $exists = $CI->db->where('position_id', (int) $campaign->cp_position)->get($position_table)->row();
                    if (!$exists && !empty($campaign->campaign_name)) {
                        $insert = ['position_name' => $campaign->campaign_name];
                        if (in_array('position_description', $position_fields, true)) {
                            $insert['position_description'] = !empty($campaign->cp_job_description) ? $campaign->cp_job_description : 'Imported from existing recruitment campaign.';
                        }
                        if (in_array('description', $position_fields, true)) {
                            $insert['description'] = !empty($campaign->cp_job_description) ? $campaign->cp_job_description : 'Imported from existing recruitment campaign.';
                        }
                        $insert = array_intersect_key($insert, array_flip($position_fields));
                        if (!empty($insert)) {
                            $CI->db->insert($position_table, $insert);
                            $new_id = $CI->db->insert_id();
                            if ($new_id) {
                                $CI->db->where('cp_position', $campaign->cp_position)->update($campaign_table, ['cp_position' => $new_id]);
                            }
                        }
                    }
                }
            }
        }
    }


    public function down()
    {
        // Safe non-destructive rollback. This method is required by Perfex module migrations.
    }
}

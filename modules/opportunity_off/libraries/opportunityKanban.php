<?php

namespace modules\opportunity\libraries;

use app\services\AbstractKanban;

class opportunityKanban extends AbstractKanban
{
    protected function table(): string
    {
        return 'opportunity';
    }

    public function defaultSortDirection()
    {
        return (!empty(get_option('default_opportunity_kanban_sort_by')) ? get_option('default_opportunity_kanban_sort_by') : 'opportunityorder');

    }

    public function defaultSortColumn()
    {
        return (!empty(get_option('default_opportunity_kanban_sort_type')) ? get_option('default_opportunity_kanban_sort_type') : 'asc');
    }

    public function limit()
    {
        return (!empty(get_option('opportunity_kanban_limit')) ? get_option('opportunity_kanban_limit') : 20);
    }

    protected function applySearchQuery($q): self
    {
        if (!startsWith($q, '#')) {
            $q = $this->ci->db->escape_like_str($this->q);
            $this->ci->db->where('(' . db_prefix() . 'opportunity.title LIKE "%' . $q . '%" ESCAPE \'!\' OR ' . db_prefix() . 'opportunity_stages.stage_name LIKE "%' . $q . '%" ESCAPE \'!\' OR ' . db_prefix() . 'opportunity_source.source_name LIKE "%' . $q . '%" ESCAPE \'!\' OR ' . db_prefix() . 'opportunity_pipelines.pipeline_name LIKE "%' . $q . '%" ESCAPE \'!\' OR opportunity_value LIKE "%' . $q . '%" ESCAPE \'!\' OR CONCAT(' . db_prefix() . 'staff.firstname, \' \', ' . db_prefix() . 'staff.lastname) LIKE "%' . $q . '%" ESCAPE \'!\')');
        } else {
            $this->ci->db->where(db_prefix() . 'opportunity.id IN
                (SELECT rel_id FROM ' . db_prefix() . 'taggables WHERE tag_id IN
                (SELECT id FROM ' . db_prefix() . 'tags WHERE name="' . $this->ci->db->escape_str(strafter($q, '#')) . '")
                AND ' . db_prefix() . 'taggables.rel_type=\'opportunity\' GROUP BY rel_id HAVING COUNT(tag_id) = 1)
                ');
        }

        return $this;
    }

    protected function initiateQuery(): self
    {
        $this->ci->db->select(db_prefix() . 'opportunity.*,' . db_prefix() . 'opportunity_stages.stage_name,' . db_prefix() . 'opportunity_source.source_name,' . db_prefix() . 'opportunity_pipelines.pipeline_name,(SELECT GROUP_CONCAT(name SEPARATOR ",") FROM ' . db_prefix() . 'taggables JOIN ' . db_prefix() . 'tags ON ' . db_prefix() . 'taggables.tag_id = ' . db_prefix() . 'tags.id WHERE rel_id = ' . db_prefix() . 'opportunity.id and rel_type="opportunity" ORDER by tag_order ASC) as tags,
        (SELECT COUNT(id) FROM ' . db_prefix() . 'files WHERE rel_id=' . db_prefix() . 'opportunity.id AND rel_type="opportunity") as total_files,
        (SELECT COUNT(id) FROM ' . db_prefix() . 'tasks WHERE rel_id=' . db_prefix() . 'opportunity.id AND rel_type="opportunity") as total_tasks,
        (SELECT COUNT(calls_id) FROM ' . db_prefix() . 'opportunity_calls WHERE module_field_id=' . db_prefix() . 'opportunity.id) as total_calls,
        (SELECT COUNT(meetings_id) FROM ' . db_prefix() . 'opportunity_meetings WHERE module_field_id=' . db_prefix() . 'opportunity.id) as total_mettings,
        (SELECT COUNT(id) FROM ' . db_prefix() . 'opportunity_comments WHERE opportunity_id=' . db_prefix() . 'opportunity.id) as total_comments,
        (SELECT COUNT(id) FROM ' . db_prefix() . 'opportunity_email WHERE opportunity_id=' . db_prefix() . 'opportunity.id) as total_emails,
        (SELECT COUNT(items_id) FROM ' . db_prefix() . 'opportunity_items WHERE opportunity_id=' . db_prefix() . 'opportunity.id) as total_items,
        (SELECT COUNT(id) FROM ' . db_prefix() . 'opportunity_activity_log WHERE opportunity_id=' . db_prefix() . 'opportunity.id) as total_activity_log');
        $this->ci->db->from(db_prefix() . 'opportunity');
        $this->ci->db->join(db_prefix() . 'opportunity_stages', db_prefix() . 'opportunity_stages.stage_id=' . db_prefix() . 'opportunity.stage_id', 'left');
        $this->ci->db->join(db_prefix() . 'opportunity_source', db_prefix() . 'opportunity_source.source_id=' . db_prefix() . 'opportunity.source_id', 'left');
        $this->ci->db->join(db_prefix() . 'opportunity_pipelines', db_prefix() . 'opportunity_pipelines.pipeline_id=' . db_prefix() . 'opportunity.pipeline_id', 'left');
        $this->ci->db->join(db_prefix() . 'staff', db_prefix() . 'staff.staffid=' . db_prefix() . 'opportunity.default_opportunity_owner', 'left');
        $this->ci->db->where(db_prefix() . 'opportunity.stage_id', $this->status);

        if (!has_permission('opportunity', '', 'view')) {
            $this->ci->db->where('('.db_prefix() .'default_opportunity_owner = ' . get_staff_user_id() . ')');
        }

        return $this;
    }
}

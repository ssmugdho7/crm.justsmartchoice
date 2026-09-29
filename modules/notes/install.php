<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();
$table = db_prefix() . 'notes';

try {
    if ($CI->db->table_exists($table)) {
        $columns = [
            'title'                     => "ALTER TABLE `{$table}` ADD `title` VARCHAR(191) NULL AFTER `id`",
            'note_color'                => "ALTER TABLE `{$table}` ADD `note_color` VARCHAR(20) NULL DEFAULT '#00A651'",
            'priority'                  => "ALTER TABLE `{$table}` ADD `priority` VARCHAR(20) NULL DEFAULT 'medium'",
            'note_type'                 => "ALTER TABLE `{$table}` ADD `note_type` VARCHAR(64) NULL DEFAULT 'general'",
            'note_visibility'           => "ALTER TABLE `{$table}` ADD `note_visibility` VARCHAR(20) NULL DEFAULT 'normal'",
            'assigned_staff_id'         => "ALTER TABLE `{$table}` ADD `assigned_staff_id` INT(11) NULL DEFAULT NULL",
            'attachment'                => "ALTER TABLE `{$table}` ADD `attachment` VARCHAR(255) NULL DEFAULT NULL",
            'attachment_original_name'  => "ALTER TABLE `{$table}` ADD `attachment_original_name` VARCHAR(255) NULL DEFAULT NULL",
            'legacy_source_table'      => "ALTER TABLE `{$table}` ADD `legacy_source_table` VARCHAR(64) NULL DEFAULT NULL",
            'legacy_source_id'         => "ALTER TABLE `{$table}` ADD `legacy_source_id` INT(11) NULL DEFAULT NULL",
            'share_token'              => "ALTER TABLE `{$table}` ADD `share_token` VARCHAR(64) NULL DEFAULT NULL",
            'share_enabled'            => "ALTER TABLE `{$table}` ADD `share_enabled` TINYINT(1) NOT NULL DEFAULT 0",
        ];

        foreach ($columns as $column => $sql) {
            if (!$CI->db->field_exists($column, $table)) {
                $CI->db->query($sql);
            }
        }
    } else {
        log_message('error', 'Notes module: native Perfex notes table is missing: ' . $table);
    }

    $projectNotesTable = db_prefix() . 'project_notes';
    if ($CI->db->table_exists($table) && $CI->db->table_exists($projectNotesTable)) {
        $projectFields = $CI->db->list_fields($projectNotesTable);
        $projectNotes = $CI->db->get($projectNotesTable)->result_array();
        foreach ($projectNotes as $projectNote) {
            $legacyId = (int) ($projectNote['id'] ?? 0);
            if ($legacyId <= 0) {
                continue;
            }

            $exists = $CI->db->where('legacy_source_table', 'project_notes')
                ->where('legacy_source_id', $legacyId)
                ->count_all_results($table);
            if ($exists > 0) {
                continue;
            }

            $title = trim((string) ($projectNote['title'] ?? ''));
            if ($title === '') {
                $title = 'Project Note #' . $legacyId;
            }

            $description = (string) ($projectNote['content'] ?? $projectNote['description'] ?? '');
            $projectId = (int) ($projectNote['project_id'] ?? 0);
            $staffId = (int) ($projectNote['staff_id'] ?? 0);
            $dateAdded = !empty($projectNote['dateadded']) ? $projectNote['dateadded'] : date('Y-m-d H:i:s');

            $CI->db->insert($table, [
                'title'               => $title,
                'description'         => $description,
                'rel_type'            => 'project',
                'rel_id'              => $projectId,
                'addedfrom'           => $staffId,
                'dateadded'           => $dateAdded,
                'note_color'          => '#0077CC',
                'priority'            => 'medium',
                'note_visibility'     => 'normal',
                'assigned_staff_id'   => $staffId ?: null,
                'legacy_source_table' => 'project_notes',
                'legacy_source_id'    => $legacyId,
            ]);
        }
    }

    if (!is_dir(NOTES_UPLOAD_FOLDER)) {
        @mkdir(NOTES_UPLOAD_FOLDER, 0755, true);
    }

    if (is_dir(NOTES_UPLOAD_FOLDER) && !file_exists(NOTES_UPLOAD_FOLDER . 'index.html')) {
        @file_put_contents(NOTES_UPLOAD_FOLDER . 'index.html', '');
    }




    if (function_exists('add_option')) {
        add_option('notes_default_color', '#00A651');
    }

    $sourcesTable = db_prefix() . 'notes_sources';
    if (!$CI->db->table_exists($sourcesTable)) {
        $CI->db->query("CREATE TABLE `{$sourcesTable}` (`id` INT UNSIGNED NOT NULL AUTO_INCREMENT, `source_key` VARCHAR(64) NOT NULL, `name` VARCHAR(120) NOT NULL, `color` VARCHAR(20) NOT NULL DEFAULT '#3598DB', `active` TINYINT(1) NOT NULL DEFAULT 1, `is_system` TINYINT(1) NOT NULL DEFAULT 0, `sort_order` INT NOT NULL DEFAULT 0, PRIMARY KEY (`id`), UNIQUE KEY `source_key` (`source_key`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }
    $sourceSeeds = [
        ['customer','Customer','#3598DB'],['lead','Lead','#F28C28'],['contract','Contract','#169179'],['proposal','Proposal','#0E6F5B'],['invoice','Invoice','#3598DB'],['estimate','Estimate','#F28C28'],['staff','Staff','#555555'],['ticket','Ticket','#E59A00'],['project','Project','#169179'],['personal_note','Personal Note','#7C3AED']
    ];
    foreach ($sourceSeeds as $i=>$seed) {
        if ($CI->db->where('source_key',$seed[0])->count_all_results($sourcesTable)===0) {
            $CI->db->insert($sourcesTable,['source_key'=>$seed[0],'name'=>$seed[1],'color'=>$seed[2],'active'=>1,'is_system'=>1,'sort_order'=>($i+1)*10]);
        }
    }

    $typesTable = db_prefix() . 'notes_types';
    if (!$CI->db->table_exists($typesTable)) {
        $CI->db->query("CREATE TABLE `{$typesTable}` (`id` INT UNSIGNED NOT NULL AUTO_INCREMENT, `type_key` VARCHAR(64) NOT NULL, `name` VARCHAR(120) NOT NULL, `color` VARCHAR(20) NOT NULL DEFAULT '#169179', `active` TINYINT(1) NOT NULL DEFAULT 1, `is_system` TINYINT(1) NOT NULL DEFAULT 0, `sort_order` INT NOT NULL DEFAULT 0, PRIMARY KEY (`id`), UNIQUE KEY `type_key` (`type_key`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }
    $typeSeeds = [['general','General','#169179'],['follow_up','Follow Up','#3598DB'],['internal','Internal','#555555'],['important','Important','#F28C28']];
    foreach ($typeSeeds as $i=>$seed) {
        if ($CI->db->where('type_key',$seed[0])->count_all_results($typesTable)===0) {
            $CI->db->insert($typesTable,['type_key'=>$seed[0],'name'=>$seed[1],'color'=>$seed[2],'active'=>1,'is_system'=>1,'sort_order'=>($i+1)*10]);
        }
    }
} catch (Throwable $exception) {
    log_message('error', 'Notes install/activation schema repair failed: ' . $exception->getMessage());
}

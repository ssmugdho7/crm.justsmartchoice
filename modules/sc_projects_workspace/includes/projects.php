<?php

defined('BASEPATH') or exit('No direct script access allowed');

hooks()->add_filter('app_view_data', 'scpr_view');
hooks()->add_filter('admin_body_class', 'scpr_body');
hooks()->add_filter('projects_table_columns', 'scpr_table_headers');
hooks()->add_filter('projects_table_sql_columns', 'scpr_table_columns');
hooks()->add_filter('projects_table_row_data', 'scpr_table_row', 10, 2);
hooks()->add_action('app_admin_head', 'scpr_head');
hooks()->add_action('app_admin_footer', 'scpr_footer');

function scpr_context()
{
    return strtolower(get_instance()->router->fetch_class()) === 'projects'
        && function_exists('scl_licensed') && scl_licensed('sc_projects_workspace');
}

function scpr_body($classes)
{
    if (scpr_context()) {
        $classes[] = 'scpr-projects';
        $method = strtolower(get_instance()->router->fetch_method());
        if (in_array($method, ['index', 'project', 'view', 'gantt'], true)) {
            $classes[] = 'scpr-page-' . $method;
        }
    }
    return $classes;
}

function scpr_head()
{
    if (scpr_context()) {
        $file = 'assets/css/projects.css';
        echo '<link rel="stylesheet" href="' . e(module_dir_url('sc_projects_workspace', $file) . '?v=' . filemtime(__DIR__ . '/../' . $file)) . '">';
    }
}

function scpr_footer()
{
    if (scpr_context()) {
        $file = 'assets/js/projects.js';
        echo '<script src="' . e(module_dir_url('sc_projects_workspace', $file) . '?v=' . filemtime(__DIR__ . '/../' . $file)) . '"></script>';
    }
}

function scpr_view($view)
{
    if (!scpr_context()) {
        return $view;
    }
    $views = ['manage', 'stats', 'project', 'view', 'gantt', 'project_tabs', 'project_tasks', 'project_overview'];
    foreach ($views as $name) {
        if ($view['path'] !== 'admin/projects/' . $name) {
            continue;
        }
        $view['path'] = '../../modules/sc_projects_workspace/views/projects/' . $name;
        if ($name === 'manage') {
            $view['data']['scpr_portfolio'] = scpr_portfolio();
        }
        if (in_array($name, ['project_tasks', 'project_overview'], true)) {
            $project = $view['data']['project'] ?? get_instance()->load->get_var('project');
            if ($project) {
                $CI = &get_instance();
                $features = $project->settings->available_features;
                // Same native task visibility rules, capped before fetching; full native table remains below.
                $view['data']['scpr_tasks'] = isset($features['project_tasks']) && !$features['project_tasks'] ? [] : $CI->projects_model->get_tasks($project->id, [], true, false, function () use ($CI) {
                    $CI->db->limit(12);
                });
                if ($name === 'project_overview') {
                    $view['data']['scpr_milestones'] = isset($features['project_milestones']) && !$features['project_milestones'] ? [] : scpr_milestones($project->id);
                }
            }
        }
        break;
    }
    return $view;
}

// Native Projects uses membership for own visibility, including users with no global View grant.
function scpr_scope_sql($alias)
{
    if (staff_can('view', 'projects')) {
        return '1=1';
    }
    return $alias . '.id IN (SELECT project_id FROM ' . db_prefix() . 'project_members WHERE staff_id=' . (int) get_staff_user_id() . ')';
}

function scpr_progress_sql($alias)
{
    $tasks = db_prefix() . 'tasks';
    // Match Projects_model::calc_progress: Finished=4; no tasks counts as complete.
    return 'LEAST(100,GREATEST(0,CASE WHEN ' . $alias . '.status=4 THEN 100 WHEN ' . $alias . '.progress_from_tasks=1 THEN '
        . '(SELECT COALESCE(ROUND(SUM(CASE WHEN status=5 THEN 1 ELSE 0 END)*100/NULLIF(COUNT(*),0),2),100) FROM '
        . $tasks . ' WHERE rel_type="project" AND rel_id=' . $alias . '.id) ELSE COALESCE(' . $alias . '.progress,0) END))';
}

function scpr_portfolio()
{
    $CI = &get_instance();
    $projects = db_prefix() . 'projects';
    $scope = scpr_scope_sql('p');
    $rows = $CI->db->query('SELECT p.status, COUNT(*) AS count FROM ' . $projects . ' p WHERE ' . $scope . ' GROUP BY p.status')->result_array();
    $counts = [];
    foreach ($rows as $row) {
        $counts[(int) $row['status']] = (int) $row['count'];
    }
    $progress = $CI->db->query('SELECT COALESCE(ROUND(AVG(' . scpr_progress_sql('p') . '),1),0) AS average FROM '
        . $projects . ' p WHERE p.status=2 AND ' . $scope)->row();
    $seconds = $CI->db->query('SELECT COALESCE(SUM(GREATEST(0,COALESCE(tt.end_time,' . time() . ')-tt.start_time)),0) AS seconds FROM '
        . db_prefix() . 'taskstimers tt JOIN ' . db_prefix() . 'tasks t ON t.id=tt.task_id AND t.rel_type="project" JOIN '
        . $projects . ' p ON p.id=t.rel_id WHERE p.status=2 AND ' . $scope)->row();
    $values = [];
    // Native project overview reserves financial information for the Projects Create capability.
    if (staff_can('create', 'projects')) {
        $base = (int) $CI->currencies_model->get_base_currency()->id;
        $values = $CI->db->query('SELECT COALESCE(NULLIF(c.default_currency,0),' . $base . ') AS currency_id, '
            . 'SUM(CAST(COALESCE(p.project_cost,0) AS DECIMAL(24,8))) AS amount FROM ' . $projects . ' p JOIN '
            . db_prefix() . 'clients c ON c.userid=p.clientid WHERE p.status=2 AND p.billing_type=1 AND '
            . $scope . ' GROUP BY currency_id')->result_array();
    }
    return ['counts' => $counts, 'active' => $counts[2] ?? 0, 'average' => $progress->average,
        'seconds' => (int) $seconds->seconds, 'values' => $values];
}

function scpr_table_headers($columns)
{
    if (scpr_context()) {
        $columns[] = _l('project_progress');
    }
    return $columns;
}

function scpr_table_columns($columns)
{
    if (scpr_context()) {
        $columns[] = scpr_progress_sql(db_prefix() . 'projects') . ' AS scpr_progress';
    }
    return $columns;
}

function scpr_table_row($row, $project)
{
    if (!scpr_context()) {
        return $row;
    }
    $id = (int) $project['id'];
    $row[1] = str_replace('<div class="row-options">', '<div class="prj-row-actions">', $row[1]);
    $row[1] = str_replace(' | ', '', $row[1]);
    $row[1] = str_replace('<a href="' . admin_url('projects/view/' . $id) . '">' , '<a class="prj-btn-view-pill" href="' . admin_url('projects/view/' . $id) . '">', $row[1]);
    $row[1] = str_replace('<a href="' . admin_url('projects/project/' . $id) . '">', '<a class="prj-btn-edit-pill" href="' . admin_url('projects/project/' . $id) . '">', $row[1]);
    $progress = max(0, min(100, (float) ($project['scpr_progress'] ?? 0)));
    $html = '<div class="prj-progress-container"><div class="prj-progress-track" role="progressbar" aria-label="' . e(_l('project_progress'))
        . '" aria-valuemin="0" aria-valuemax="100" aria-valuenow="' . e($progress) . '"><div class="prj-progress-bar" style="width:'
        . e($progress) . '%"></div></div><span class="prj-progress-text">' . e($progress) . '%</span></div>';
    $row[] = $html;
    return $row;
}

function scpr_milestones($projectId)
{
    $CI = &get_instance();
    $CI->db->select('*');
    if (staff_can('view', 'tasks') || get_option('show_all_tasks_for_project_member') == 1) {
        $tasks = db_prefix() . 'tasks';
        $relation = 'rel_type="project" AND rel_id=' . (int) $projectId . ' AND milestone=' . db_prefix() . 'milestones.id';
        $CI->db->select('(SELECT COUNT(*) FROM ' . $tasks . ' WHERE ' . $relation . ') AS scpr_total, '
            . '(SELECT COUNT(*) FROM ' . $tasks . ' WHERE ' . $relation . ' AND status=5) AS scpr_done', false);
    }
    return $CI->db->where('project_id', (int) $projectId)->order_by('milestone_order', 'asc')->limit(8)
        ->get(db_prefix() . 'milestones')->result_array();
}

function scpr_can_change_task($task)
{
    return staff_can('edit', 'tasks') || !empty($task['current_user_is_assigned'])
        || ((int) $task['addedfrom'] === (int) get_staff_user_id() && empty($task['is_added_from_contact']));
}

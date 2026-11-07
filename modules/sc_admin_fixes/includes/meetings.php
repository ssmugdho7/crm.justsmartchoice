<?php
defined('BASEPATH') or exit('No direct script access allowed');
hooks()->add_action('admin_init', 'scaf_seed_meeting_admin_links');
function scaf_seed_meeting_admin_links()
{
    if (get_option('scaf_meeting_admin_links_seeded') === '1' || !defined('SMART_CHOICE_LINKS_TABLE')) { return; }
    $CI = &get_instance();
    if (!$CI->db->table_exists(SMART_CHOICE_LINKS_TABLE)) { return; }
    foreach ([['SmartMeet', 'https://meet.justsmartchoice.com/', 'fa-solid fa-video', 2], ['ScreenRec', 'https://rec.justsmartchoice.com/', 'fa-solid fa-record-vinyl', 3]] as [$title, $url, $icon, $position]) {
        if ($CI->db->where('url', $url)->count_all_results(SMART_CHOICE_LINKS_TABLE) > 0) { continue; }
        $CI->db->insert(SMART_CHOICE_LINKS_TABLE, ['title' => $title, 'url' => $url, 'category' => 'Meetings', 'placement' => 'left', 'icon' => $icon, 'target' => '_blank', 'rel' => 'noopener noreferrer', 'position' => $position, 'is_active' => 1, 'is_internal' => 0, 'visible_to_roles' => '', 'visible_to_staff' => '', 'created_by' => get_staff_user_id() ?: null, 'datecreated' => date('Y-m-d H:i:s')]);
    }
    update_option('scaf_meeting_admin_links_seeded', '1');
}
hooks()->add_action('before_start_render_dashboard_content', 'scaf_render_live_meetings');
function scaf_live_meetings()
{
    $path = get_option('scaf_meet_config_path') ?: dirname(FCPATH) . '/meet.justsmartchoice.com/config.php';
    try {
        if (!is_file($path) || !is_readable($path)) { throw new RuntimeException('SmartMeet config not found: ' . $path); }
        $c = require $path;
        if (!is_array($c) || empty($c['db_name'])) { throw new RuntimeException('SmartMeet config invalid'); }
        $db = new PDO('mysql:host=' . ($c['db_host'] ?? 'localhost') . ';dbname=' . $c['db_name'] . ';charset=utf8mb4', $c['db_user'] ?? '', $c['db_pass'] ?? '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 2, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
        $st = $db->prepare("SELECT r.id, r.code, r.title, p.name FROM meet_rooms r JOIN meet_participants p ON p.room_id = r.id WHERE r.ended_at IS NULL AND r.deleted_at IS NULL AND p.status = 'joined' AND p.last_seen >= UTC_TIMESTAMP() - INTERVAL ? SECOND ORDER BY r.created_at DESC, r.id DESC, p.joined_at, p.id");
        $st->execute([20]);
        $base = (isset($c['base_url']) && stripos($c['base_url'], 'https://') === 0) ? rtrim($c['base_url'], '/') : 'https://meet.justsmartchoice.com';
        $rooms = [];
        foreach ($st->fetchAll() as $row) {
            if (!preg_match('/^[a-z]{3}-[a-z]{4}-[a-z]{3}$/', $row['code'])) { continue; }
            if (!isset($rooms[$row['id']])) { if (count($rooms) >= 5) { break; } $rooms[$row['id']] = ['title' => (string) $row['title'], 'url' => $base . '/' . $row['code'], 'names' => []]; }
            $rooms[$row['id']]['names'][] = (string) $row['name'];
        }
        return array_values($rooms);
    } catch (Throwable $e) {
        log_message('error', 'SmartMeet live meetings unavailable: ' . $e->getMessage());
        return [];
    }
}
function scaf_render_live_meetings()
{
    if (!is_staff_logged_in()) { return; }
    foreach (scaf_live_meetings() as $room) {
        $names = array_slice($room['names'], 0, 8); $more = count($room['names']) - count($names);
        $who = implode(', ', array_map('e', $names)) . ($more > 0 ? ' ' . e(_l('scaf_live_meeting_more', $more)) : '');
        echo '<div class="col-md-12"><div class="alert alert-info tw-flex tw-flex-wrap tw-items-center tw-justify-between tw-gap-3"><div><h4 class="tw-mt-0 tw-mb-1 tw-font-semibold"><i class="fa-solid fa-video"></i> ' . e(_l('scaf_live_meeting_title')) . ($room['title'] !== '' ? ': ' . e($room['title']) : '') . '</h4><p class="tw-mb-1">' . e(_l('scaf_live_meeting_join_text')) . '</p><p class="tw-mb-0"><strong>' . e(_l('scaf_live_meeting_people')) . '</strong> ' . $who . '</p></div><a href="' . e($room['url']) . '" target="_blank" rel="noopener noreferrer" class="btn btn-primary">' . e(_l('scaf_live_meeting_join')) . '</a></div></div>';
    }
}

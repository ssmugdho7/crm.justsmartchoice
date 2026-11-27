<?php
defined('BASEPATH') or exit('No direct script access allowed');

/** Native project permissions apply equally to chat-origin documents. */
function prchat_can_save_project_document($projectId)
{
    if ($projectId <= 0) { return false; }
    $CI = &get_instance();
    $CI->load->model('projects_model');
    if (!$CI->projects_model->get($projectId)) { return false; }
    $canView = is_admin() || staff_can('view', 'projects') || $CI->projects_model->is_member($projectId, get_staff_user_id());
    if (!$canView) { return false; }
    if (is_admin() || staff_can('create', 'projects') || staff_can('edit', 'projects')) { return true; }
    $settings = $CI->projects_model->get_project_settings($projectId);
    return $CI->projects_model->is_member($projectId, get_staff_user_id())
        && (!is_array($settings) || !array_key_exists('upload_files', $settings) || (bool) $settings['upload_files']);
}

/** Copies bytes into independent company storage and registers a native project file. */
function prchat_store_project_document($projectId, $source, $originalName, $note = null)
{
    if (!prchat_can_save_project_document($projectId)) { throw new RuntimeException('Project access denied.', 403); }
    $CI = &get_instance();
    $originalName = basename(str_replace('\\', '/', $originalName));
    if ($originalName === '' || preg_match('/[\x00-\x1f]/', $originalName)) { throw new RuntimeException('Invalid file name.', 422); }
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $allowed = array_filter(preg_split('/[|,\s]+/', strtolower(pr_chat_client_crm_attachment_types_string())));
    if (!in_array($ext, $allowed, true) || in_array($ext, ['php','phtml','phar','html','htm','svg','js','exe','sh'], true)) {
        throw new RuntimeException('This file type cannot be saved to a project.', 422);
    }
    if ($note === null && (!is_file($source) || !is_readable($source))) { throw new RuntimeException('The original file is unavailable.', 404); }
    $dir = get_upload_path_by_type('project') . (int) $projectId . '/';
    if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) { throw new RuntimeException('Project storage is unavailable.', 500); }
    if (!file_exists($dir . 'index.html') && file_put_contents($dir . 'index.html', '') === false) { throw new RuntimeException('Project storage is unavailable.', 500); }
    $name = bin2hex(random_bytes(16)) . '_chat.' . $ext;
    $dest = $dir . $name;
    $saved = $note === null ? copy($source, $dest) : file_put_contents($dest, $note) !== false;
    if (!$saved) { throw new RuntimeException('Unable to save the project document.', 500); }
    chmod($dest, 0644);
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($dest) ?: 'application/octet-stream';
    try {
        $ok = $CI->db->insert(db_prefix() . 'project_files', [
            'project_id' => (int) $projectId, 'file_name' => $name, 'original_file_name' => $originalName,
            'filetype' => $mime, 'dateadded' => date('Y-m-d H:i:s'), 'staffid' => get_staff_user_id(),
            'contact_id' => 0, 'subject' => $originalName, 'visible_to_customer' => 0,
        ]);
        $id = $ok ? (int) $CI->db->insert_id() : 0;
        if (!$id) { throw new RuntimeException('Unable to register the project document.', 500); }
    } catch (Throwable $error) {
        unlink($dest);
        throw new RuntimeException('Unable to register the project document.', 500);
    }
    log_activity('Chat document saved to project #' . (int) $projectId . ' file #' . $id . ' by staff #' . (int) get_staff_user_id());
    return ['file_id' => $id, 'file' => $name, 'project_url' => admin_url('projects/view/' . (int) $projectId . '?group=project_files')];
}

/** Never accept an arbitrary on-disk filename as evidence of conversation access. */
function prchat_resolve_project_source($messageId, $type, $groupId, $filename)
{
    $CI = &get_instance();
    $tables = ['staff' => 'chatmessages', 'group' => 'chatgroupmessages', 'client' => 'chatclientmessages'];
    if (!isset($tables[$type])) { throw new RuntimeException('Invalid message type.', 422); }
    $message = null;
    if ($messageId > 0) {
        if (!prchat_staff_can_message($type, $messageId)) { throw new RuntimeException('Message access denied.', 403); }
        $message = $CI->db->where('id', $messageId)->get(db_prefix() . $tables[$type])->row();
        if (!$message) { throw new RuntimeException('Message not found.', 404); }
        if ($type === 'group') {
            if ($groupId > 0 && $groupId !== (int) $message->group_id) { throw new RuntimeException('Group does not match the message.', 403); }
            $groupId = (int) $message->group_id;
        }
    }
    if ($type === 'group' && !prchat_staff_can_group($groupId)) { throw new RuntimeException('Group access denied.', 403); }
    if (!$message && !$filename) { throw new RuntimeException('Choose a message or file to save.', 422); }
    if ($filename === '') { return ['note' => trim(strip_tags(html_entity_decode((string) $message->message, ENT_QUOTES, 'UTF-8')))]; }
    if ($filename !== basename($filename) || strpos($filename, '\\') !== false || preg_match('/[\x00-\x1f]/', $filename)) { throw new RuntimeException('Invalid file name.', 422); }
    $folders = [];
    if ($type === 'group') {
        $row = $CI->db->where('group_id', $groupId)->where('file_name', $filename)->get(db_prefix() . 'chatgroupsharedfiles')->row();
        if (!$row) { throw new RuntimeException('File is not part of this group.', 403); }
        $folders = [PR_CHAT_MODULE_GROUPS_UPLOAD_FOLDER, PR_CHAT_MODULE_AUDIO_UPLOAD_FOLDER . '/groups'];
    } else {
        if (!$message) { throw new RuntimeException('Select the message containing this file.', 422); }
        // Resolve only exact local upload URLs carried by the authorized message.
        $content = html_entity_decode((string) $message->message, ENT_QUOTES, 'UTF-8');
        preg_match_all('~(?:https?://[^\s<>"\']+|/?modules/prchat/uploads/[^\s<>"\']+)~i', $content, $urls);
        foreach ($urls[0] as $url) {
            $path = rawurldecode((string) parse_url($url, PHP_URL_PATH));
            if (basename($path) !== $filename) { continue; }
            foreach ([PR_CHAT_MODULE_UPLOAD_FOLDER, PR_CHAT_MODULE_AUDIO_UPLOAD_FOLDER . '/staff'] as $folder) {
                $relative = trim(str_replace(rtrim(FCPATH, '/'), '', rtrim($folder, '/')), '/');
                if (substr($path, -strlen($relative . '/' . $filename)) === $relative . '/' . $filename) { $folders[] = $folder; }
            }
        }
    }
    foreach ($folders as $folder) {
        $base = realpath($folder); $source = realpath($folder . '/' . $filename);
        if ($base && $source && dirname($source) === $base && !is_link($folder . '/' . $filename) && is_file($source)) { return ['source' => $source, 'name' => $filename]; }
    }
    throw new RuntimeException('The original file is unavailable.', 404);
}

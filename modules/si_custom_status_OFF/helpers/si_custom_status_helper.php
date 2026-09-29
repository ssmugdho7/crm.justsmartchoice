<?php
defined('BASEPATH') or exit('No direct script access allowed');

function si_cs_is_client_area()
{
    if (function_exists('is_client_logged_in') && is_client_logged_in() && (!function_exists('is_staff_logged_in') || !is_staff_logged_in())) {
        return true;
    }

    $CI = &get_instance();
    $uri = isset($CI->uri) ? (string)$CI->uri->uri_string() : '';

    return $uri !== '' && strpos($uri, 'admin/') !== 0;
}

function si_cs_visibility_allows($scope, $client_area = null)
{
    $scope = $scope ?: 'admin';
    if ($client_area === null) {
        $client_area = si_cs_is_client_area();
    }

    if ($scope === 'all') {
        return true;
    }

    if ($client_area) {
        return $scope === 'client';
    }

    return $scope === 'admin';
}

function si_cs_get_custom_statuses($relto)
{
    $CI = &get_instance();
    if ($relto != '') {
        $CI->db->order_by('order', 'asc');
        $CI->db->where('relto', $relto);
        if (si_cs_is_client_area()) {
            $CI->db->where_in('visibility_scope', ['all', 'client']);
        } else {
            $CI->db->where_in('visibility_scope', ['all', 'admin']);
        }
        return $CI->db->get(db_prefix() . 'si_custom_status')->result_array();
    }
    return [];
}

function si_cs_get_custom_default_statuses($relto)
{
    $CI = &get_instance();
    if ($relto != '') {
        $CI->db->select('*,status_id as id');
        $CI->db->order_by('order', 'asc');
        $CI->db->where('relto', $relto);
        $CI->db->where('active', 1);
        if (si_cs_is_client_area()) {
            $CI->db->where_in('visibility_scope', ['all', 'client']);
        } else {
            $CI->db->where_in('visibility_scope', ['all', 'admin']);
        }
        return $CI->db->get(db_prefix() . 'si_custom_status_default')->result_array();
    }
    return [];
}

function si_cs_filter_statuses_for_current_area($statuses, $relto, $core_statuses = false)
{
    if (!is_array($statuses) || empty($statuses)) {
        return [];
    }

    $client_area = si_cs_is_client_area();
    $CI = &get_instance();
    $visibility_map = [];

    if ($CI->db->table_exists(db_prefix() . 'si_custom_status_default')) {
        $CI->db->select('status_id, visibility_scope');
        $CI->db->where('relto', $relto);
        $rows = $CI->db->get(db_prefix() . 'si_custom_status_default')->result_array();
        foreach ($rows as $row) {
            $visibility_map[(int)$row['status_id']] = $row['visibility_scope'] ?: 'all';
        }
    }

    $filtered = [];
    foreach ($statuses as $status) {
        $id = isset($status['status_id']) ? (int)$status['status_id'] : (isset($status['id']) ? (int)$status['id'] : 0);
        $scope = isset($status['visibility_scope']) ? $status['visibility_scope'] : ($visibility_map[$id] ?? ($core_statuses ? 'all' : 'admin'));
        if (si_cs_visibility_allows($scope, $client_area)) {
            $filtered[] = $status;
        }
    }

    return $filtered;
}

function si_cs_format_statuses($status, $text = false, $clean = false)
{
    if (!is_array($status)) {
        return '';
    }

    $status_name = htmlspecialchars($status['name'] ?? '', ENT_QUOTES, 'UTF-8');
    if ($clean == true) {
        return $status_name;
    }

    $color = htmlspecialchars($status['color'] ?? '#757575', ENT_QUOTES, 'UTF-8');
    $style_ = '';
    $class = '';
    if ($text == false) {
        $style_ = 'border: 1px solid ' . $color . ';color:' . $color . ';background:#fff;';
        $class = 'label si-custom-status-badge';
    } else {
        $style_ = 'color:' . $color . ';';
    }

    return '<span class="' . $class . '" style="' . $style_ . '">' . $status_name . '</span>';
}

function si_cs_visibility_label($scope)
{
    $scope = $scope ?: 'admin';
    if ($scope === 'all') {
        return _l('si_custom_status_visibility_all');
    }
    if ($scope === 'client') {
        return _l('si_custom_status_visibility_client');
    }
    return _l('si_custom_status_visibility_admin');
}

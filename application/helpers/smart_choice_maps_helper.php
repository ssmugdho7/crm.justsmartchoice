<?php

defined('BASEPATH') or exit('No direct script access allowed');

function sc_google_map_normalize($value)
{
    $value = trim((string) $value);
    if ($value === '') {
        return '';
    }
    if (preg_match('#^https?://#i', $value)) {
        return $value;
    }
    if (preg_match('#^(www\.)?(google\.|maps\.google\.)#i', $value)) {
        return 'https://' . ltrim($value, '/');
    }
    return 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($value);
}

function sc_google_map_entity_meta($entity, $id)
{
    $CI = &get_instance();
    $map = [
        'client'      => ['table' => 'clients',      'pk' => 'userid'],
        'lead'        => ['table' => 'leads',        'pk' => 'id'],
        'invoice'     => ['table' => 'invoices',     'pk' => 'id'],
        'estimate'    => ['table' => 'estimates',    'pk' => 'id'],
        'proposal'    => ['table' => 'proposals',    'pk' => 'id'],
        'contract'    => ['table' => 'contracts',    'pk' => 'id'],
        'project'     => ['table' => 'projects',     'pk' => 'id'],
        'credit_note' => ['table' => 'creditnotes',  'pk' => 'id'],
    ];
    if (!isset($map[$entity])) return null;
    $meta = $map[$entity];
    if (!$CI->db->table_exists(db_prefix() . $meta['table'])) return null;
    return $CI->db->where($meta['pk'], (int) $id)->get(db_prefix() . $meta['table'])->row_array();
}

function sc_google_map_update_where($table, $where, $link)
{
    $CI = &get_instance();
    $full = db_prefix() . $table;
    if (!$CI->db->table_exists($full) || !$CI->db->field_exists('google_map_link', $full)) return;
    foreach ($where as $k => $v) $CI->db->where($k, $v);
    $CI->db->update($full, ['google_map_link' => $link]);
}

function sc_google_map_sync($entity, $id)
{
    $CI = &get_instance();
    $id = (int) $id;
    if ($id <= 0) return;
    $row = sc_google_map_entity_meta($entity, $id);
    if (!$row) return;

    $posted = $CI->input->post('google_map_link', false);
    $link = $posted !== null ? sc_google_map_normalize($posted) : sc_google_map_normalize($row['google_map_link'] ?? '');
    $clientId = 0; $leadId = 0;
    if ($entity === 'client') { $clientId = $id; $leadId = (int)($row['leadid'] ?? 0); }
    elseif ($entity === 'lead') { $leadId = $id; }
    elseif (in_array($entity, ['invoice','estimate','project','credit_note'], true)) { $clientId = (int)($row['clientid'] ?? 0); }
    elseif ($entity === 'contract') { $clientId = (int)($row['client'] ?? 0); }
    elseif ($entity === 'proposal') {
        if (($row['rel_type'] ?? '') === 'customer') $clientId = (int)($row['rel_id'] ?? 0);
        if (($row['rel_type'] ?? '') === 'lead') $leadId = (int)($row['rel_id'] ?? 0);
        if (!$clientId && !empty($row['project_id'])) {
            $pr = $CI->db->where('id', (int)$row['project_id'])->get(db_prefix().'projects')->row_array();
            $clientId = (int)($pr['clientid'] ?? 0);
        }
    }
    if (!$clientId && $leadId && $CI->db->field_exists('leadid', db_prefix().'clients')) {
        $c = $CI->db->where('leadid', $leadId)->get(db_prefix().'clients')->row_array();
        $clientId = (int)($c['userid'] ?? 0);
    }
    if ($clientId && !$leadId) {
        $c = $CI->db->where('userid', $clientId)->get(db_prefix().'clients')->row_array();
        $leadId = (int)($c['leadid'] ?? 0);
    }
    if ($link === '' && $clientId && $CI->db->field_exists('google_map_link', db_prefix().'clients')) {
        $c = $CI->db->select('google_map_link')->where('userid',$clientId)->get(db_prefix().'clients')->row_array();
        $link = sc_google_map_normalize($c['google_map_link'] ?? '');
    }
    if ($link === '' && $leadId && $CI->db->field_exists('google_map_link', db_prefix().'leads')) {
        $l = $CI->db->select('google_map_link')->where('id',$leadId)->get(db_prefix().'leads')->row_array();
        $link = sc_google_map_normalize($l['google_map_link'] ?? '');
    }
    if ($link === '') return;

    if ($clientId) {
        sc_google_map_update_where('clients',['userid'=>$clientId],$link);
        sc_google_map_update_where('invoices',['clientid'=>$clientId],$link);
        sc_google_map_update_where('estimates',['clientid'=>$clientId],$link);
        sc_google_map_update_where('projects',['clientid'=>$clientId],$link);
        sc_google_map_update_where('creditnotes',['clientid'=>$clientId],$link);
        sc_google_map_update_where('contracts',['client'=>$clientId],$link);
        sc_google_map_update_where('proposals',['rel_type'=>'customer','rel_id'=>$clientId],$link);
    }
    if ($leadId) {
        sc_google_map_update_where('leads',['id'=>$leadId],$link);
        sc_google_map_update_where('proposals',['rel_type'=>'lead','rel_id'=>$leadId],$link);
    }
    $meta = ['client'=>['clients','userid'],'lead'=>['leads','id'],'invoice'=>['invoices','id'],'estimate'=>['estimates','id'],'proposal'=>['proposals','id'],'contract'=>['contracts','id'],'project'=>['projects','id'],'credit_note'=>['creditnotes','id']][$entity];
    sc_google_map_update_where($meta[0],[$meta[1]=>$id],$link);
}

function sc_google_map_field($entity, $record = null)
{
    $value = '';
    if (is_object($record)) $value = (string)($record->google_map_link ?? '');
    if (is_array($record)) $value = (string)($record['google_map_link'] ?? '');
    $html = render_input('google_map_link', 'sc_google_map_link', $value, 'text', ['placeholder' => _l('sc_google_map_link_placeholder')]);
    if ($value !== '') {
        $html .= '<p class="tw-mt-1"><a href="' . e(sc_google_map_normalize($value)) . '" target="_blank" rel="noopener"><i class="fa-solid fa-map-location-dot"></i> ' . _l('sc_open_google_map') . '</a></p>';
    }
    return '<div class="sc-google-map-field" data-sc-entity="'.e($entity).'">'.$html.'</div>';
}

hooks()->add_action('after_client_created', function($payload){ sc_google_map_sync('client', is_array($payload) ? ($payload['id'] ?? 0) : $payload); });
hooks()->add_action('client_updated', function($payload){ sc_google_map_sync('client', is_array($payload) ? ($payload['id'] ?? 0) : $payload); });
hooks()->add_action('lead_created', function($id){ sc_google_map_sync('lead',$id); });
hooks()->add_action('after_lead_updated', function($id){ sc_google_map_sync('lead',$id); });
hooks()->add_action('after_invoice_added', function($id){ sc_google_map_sync('invoice',$id); });
hooks()->add_action('invoice_updated', function($payload){ sc_google_map_sync('invoice', is_array($payload) ? ($payload['id'] ?? 0) : $payload); });
hooks()->add_action('after_estimate_added', function($id){ sc_google_map_sync('estimate',$id); });
hooks()->add_action('after_estimate_updated', function($id){ sc_google_map_sync('estimate',$id); });
hooks()->add_action('proposal_created', function($id){ sc_google_map_sync('proposal',$id); });
hooks()->add_action('after_proposal_updated', function($id){ sc_google_map_sync('proposal',$id); });
hooks()->add_action('after_contract_added', function($id){ sc_google_map_sync('contract',$id); });
hooks()->add_action('after_contract_updated', function($id){ sc_google_map_sync('contract',$id); });
hooks()->add_action('after_add_project', function($id){ sc_google_map_sync('project',$id); });
hooks()->add_action('after_update_project', function($id){ sc_google_map_sync('project',$id); });
hooks()->add_action('after_create_credit_note', function($id){ sc_google_map_sync('credit_note',$id); });
hooks()->add_action('after_update_credit_note', function($id){ sc_google_map_sync('credit_note',$id); });

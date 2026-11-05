<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once __DIR__.'/customer_data.php';
hooks()->add_filter('app_view_data', 'scaf_customer_view');
hooks()->add_filter('customers_table_row_data', 'scaf_customer_row', 10, 2);
hooks()->add_filter('admin_customer_contacts_table_row', 'scaf_customer_contact_row', 10, 2);
hooks()->add_filter('all_contacts_table_row', 'scaf_customer_contact_row', 10, 2);
function scaf_customer_view($view)
{
    if (strtolower(get_instance()->router->fetch_class()) !== 'clients') return $view;
    $paths = ['all_contacts','manage','client','tabs','groups/_statement','groups/statement'];
    foreach ($paths as $path) {
        if ($view['path'] === 'admin/clients/'.$path) { $view['path'] = '../../modules/sc_admin_fixes/views/customers/'.$path; break; }
    }
    if ($view['path'] === '../../modules/sc_admin_fixes/views/customers/groups/_statement') {
        $data = $view['data'];
        $client = $data['client'] ?? get_instance()->load->get_var('client');
        $statement = $data['statement'] ?? get_instance()->load->get_var('statement');
        $customerId = is_object($client) ? $client->userid : get_instance()->input->get('customer_id');
        if ($customerId && !empty($statement['currency']->id)) $view['data']['aging'] = scaf_customer_current_aging($customerId, $statement['currency']->id);
    }
    return $view;
}
function scaf_customer_row($row, $record)
{
    if (trim((string)$record['company']) === '' && trim((string)($record['fullname'] ?? '')) !== '') {
        // Use the actual person, preserving native authorized row actions.
        $name = e(trim($record['fullname']));
        $row[2] = preg_replace_callback('~(<a\b[^>]*>).*?(</a>)~s', static function ($match) use ($name) { return $match[1].$name.$match[2]; }, $row[2], 1);
        $row[2] .= '<span class="scaf-residential">'.e(_l('scaf_residential')).'</span>';
    }
    $row['DT_RowAttr']['data-scaf-active'] = (int)$record[db_prefix().'clients.active'];
    return $row;
}
function scaf_customer_contact_row($row, $record)
{
    $id = (int)($record['id'] ?? 0);
    if (!$id) return $row;
    $CI = &get_instance();
    $contact = $CI->db->where('id', $id)->get(db_prefix().'contacts')->row_array();
    if (!$contact || !scaf_customer_allowed($contact['userid'])) return $row;
    $name = trim(($contact['firstname'] ?? '').' '.($contact['lastname'] ?? ''));
    $url = contact_profile_image_url($id);
    $avatar = strpos($url, '/assets/images/user-placeholder.jpg') !== false
        ? '<span class="scaf-contact-avatar'.(!empty($contact['is_primary']) ? ' is-primary' : '').'" aria-hidden="true">'.e(scaf_customer_initials($name)).'</span>'
        : '<img src="'.e($url).'" class="client-profile-image-small mright5'.(!empty($contact['is_primary']) ? ' scaf-contact-primary' : '').'" alt="">';
    // Replace only the native leading image/avatar, retaining actions and GDPR/custom columns.
    $row[0] = preg_replace('~^\s*(?:<img\b[^>]*>|<span\b[^>]*(?:native-contact-initials|scaf-contact-avatar)[^>]*>.*?</span>)~s', '', $row[0], 1);
    $row[0] = $avatar.$row[0];
    $badges = '';
    foreach (['invoice_emails'=>'invoices','estimate_emails'=>'estimates','project_emails'=>'projects'] as $field=>$label) {
        if (!empty($contact[$field])) $badges .= '<span class="scaf-notification">'.e(_l($label)).'</span>';
    }
    $row[0] .= '<div class="scaf-notifications" aria-label="'.e(_l('email_notifications')).'">'.$badges.'</div>';
    return $row;
}

hooks()->add_action('before_do_bulk_action_for_customers', 'scaf_customer_bulk_guard');
/** Validate the entire batch before native handlers can change any record. */
function scaf_customer_bulk_validation($ids, $deleting, $groups, $clearGroups = false)
{
    if (!staff_can($deleting ? 'delete' : 'edit', 'customers')) return false;
    if (!is_array($ids) || !$ids || count($ids) > 1000) return false;
    foreach ($ids as $id) {
        if (!is_scalar($id) || !ctype_digit((string)$id) || (int)$id <= 0 || !scaf_customer_allowed((int)$id)) return false;
    }
    if (!$deleting) {
        if ($groups === 'remove_all') return $clearGroups === true;
        if (!is_array($groups) || !$groups) return false;
        foreach ($groups as $group) if (!is_scalar($group) || !ctype_digit((string)$group) || (int)$group <= 0) return false;
    }
    return true;
}
function scaf_customer_bulk_guard()
{
    $CI = &get_instance();
    if (strtolower($CI->router->fetch_class()) !== 'clients' || strtolower($CI->router->fetch_method()) !== 'bulk_action') return;
    if (strtolower($CI->input->method()) !== 'post' || !scaf_customer_bulk_validation($CI->input->post('ids'), (bool)$CI->input->post('mass_delete'), $CI->input->post('groups'), $CI->input->post('scaf_clear_groups') === '1')) {
        $CI->output->set_status_header(403)->set_content_type('application/json');
        echo json_encode(['success'=>false,'message'=>_l('access_denied')]);
        exit;
    }
}
hooks()->add_filter('customers_table_sql_join', 'scaf_customer_segment_join');
function scaf_customer_segment_join($joins)
{
    $CI = &get_instance();
    if (strtolower($CI->router->fetch_class()) !== 'clients') return $joins;
    $segment = $CI->input->post('scaf_segment');
    if ($segment === 'active' || $segment === 'inactive') {
        $p = db_prefix(); $active = $segment === 'active' ? 1 : 0;
        $joins[] = 'JOIN (SELECT userid FROM '.$p.'clients WHERE active='.$active.') scaf_segment_scope ON scaf_segment_scope.userid='.$p.'clients.userid';
    }
    $p = db_prefix();
    if ($segment === 'residential') $joins[] = 'JOIN (SELECT userid FROM '.$p.'clients WHERE active=1 AND TRIM(company)="") scaf_residential ON scaf_residential.userid='.$p.'clients.userid';
    if ($segment === 'commercial') $joins[] = 'JOIN (SELECT userid FROM '.$p.'clients WHERE TRIM(company)!="") scaf_commercial ON scaf_commercial.userid='.$p.'clients.userid';
    if ($segment === 'pastdue' || $segment === 'vip') {
        if (!staff_can('view','invoices') && !staff_can('view_own','invoices') && !get_option('allow_staff_view_invoices_assigned')) {
            $joins[] = 'JOIN (SELECT userid FROM '.$p.'clients WHERE 1=0) scaf_financial_segment ON scaf_financial_segment.userid='.$p.'clients.userid';
        } else {
            $scope = staff_can('view','invoices') ? '' : ' AND ('.str_replace($p.'invoices.','i.', get_invoices_where_sql_for_staff(get_staff_user_id())).')';
            if ($segment === 'vip') {
                $currency = (int)get_base_currency()->id;
                $sql = 'SELECT i.clientid FROM '.$p.'invoices i WHERE i.status NOT IN (5,6) AND i.currency='.$currency.$scope.' GROUP BY i.clientid HAVING SUM(i.total)>=25000';
            } else {
                $sql = 'SELECT DISTINCT i.clientid FROM '.$p.'invoices i WHERE i.status IN (1,3,4) AND i.duedate IS NOT NULL AND i.duedate!="0000-00-00" AND i.duedate<'.$CI->db->escape(date('Y-m-d')).' AND i.total>COALESCE((SELECT SUM(p.amount) FROM '.$p.'invoicepaymentrecords p WHERE p.invoiceid=i.id),0)+COALESCE((SELECT SUM(c.amount) FROM '.$p.'credits c WHERE c.invoice_id=i.id),0)'.$scope;
            }
            $joins[] = 'JOIN ('.$sql.') scaf_financial_segment ON scaf_financial_segment.clientid='.$p.'clients.userid';
        }
    }
    return $joins;
}

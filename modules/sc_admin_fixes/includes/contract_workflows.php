<?php
defined('BASEPATH') or exit('No direct script access allowed');
function scaf_contract_workflow_states()
{
    return ['draft', 'sent', 'expired', 'declined'];
}
function scaf_contract_workflow($contract)
{
    if (!empty($contract->signed) || !empty($contract->marked_as_signed)) { return 'signed'; }
    $status = get_option('scaf_contract_workflow_' . (int)$contract->id);
    return in_array($status, scaf_contract_workflow_states(), true) ? $status : '';
}
hooks()->add_action('after_contract_view_as_client_link', function ($contract) {
    $CI = &get_instance();
    if (!is_staff_logged_in()) { return; }
    $CI->load->view('sc_admin_fixes/contracts/workflow_actions', ['contract'=>$contract]);
});
hooks()->add_action('after_contract_deleted', function ($id) {
    delete_option('scaf_contract_workflow_' . (int)$id);
});

hooks()->add_action('app_admin_head', function () {
    if (strtolower(get_instance()->router->fetch_class()) !== 'contracts') { return; }
    scaf_asset('assets/css/contract-workflows.css');
    scaf_asset('assets/js/contract-workflows.js');
});

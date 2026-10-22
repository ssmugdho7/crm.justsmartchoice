<?php

$root = dirname(__DIR__);
$module = $root . '/modules/sc_customers_workspace';
$checks = [];

function sccw_check($condition, $message)
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
    echo "PASS: {$message}\n";
}

$bootstrap = file_get_contents($module . '/sc_customers_workspace.php');
$hooks = file_get_contents($module . '/includes/customers.php');
$english = include_language($module . '/language/english/sc_customers_workspace_lang.php');
$spanish = include_language($module . '/language/spanish/sc_customers_workspace_lang.php');
$manifest = json_decode(file_get_contents($module . '/smart_choice_module_manifest.json'), true);

sccw_check(strpos($bootstrap, "scl_licensed('sc_customers_workspace')") !== false, 'activation requires the owner-controlled customer workspace license');
sccw_check(strpos($hooks, "fetch_class()) === 'clients'") !== false, 'enhancements are scoped to the native Clients controller');
sccw_check(strpos($hooks, "app_admin_footer") !== false && strpos($hooks, "app_admin_head") !== false, 'customer CSS and JavaScript use admin hooks');
sccw_check($manifest['core_files_modified'] === [], 'no Perfex core file is replaced');
sccw_check($manifest['migrations'] === [], 'customer records require no migration');
sccw_check(array_keys($english) === array_keys($spanish), 'English and Spanish language keys match');
sccw_check(strpos(file_get_contents($module . '/assets/css/customers.css'), 'body.sccw-customers') !== false, 'all primary presentation rules are customer-page scoped');
sccw_check(strpos(file_get_contents($module . '/assets/js/customers.js'), 'text(value') !== false, 'dynamic translated navigation text is HTML escaped');

function include_language($path)
{
    $lang = [];
    $source = file_get_contents($path);
    preg_match_all('/\\$lang\\[\'([^\']+)\'\\]\\s*=\\s*\'([^\']*)\';/', $source, $matches, PREG_SET_ORDER);
    foreach ($matches as $match) {
        $lang[$match[1]] = $match[2];
    }
    return $lang;
}

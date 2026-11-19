<?php
// Exercise only the actual shared library loader; module registration is unrelated.
namespace ZillapagePreviewFixture;
$sourcePath = $argv[1] ?? dirname(__DIR__) . '/modules/zillapage_off/zillapage.php';
$source = file_get_contents($sourcePath);
$start = strpos($source, 'function zillapage_fetch_library_section(');
$end = strpos($source, 'function zillapage_design_presets(', $start);
if ($start === false || $end === false) { throw new \RuntimeException('Library loader not found'); }
$temporary = sys_get_temp_dir() . '/zillapage-preview-' . bin2hex(random_bytes(8)) . '/';
mkdir($temporary . 'cache', 0700, true);
define('APPPATH', $temporary);
$requests = 0;
$response = '<header>Existing library content</header>';
function curl_init($url) { $GLOBALS['requests']++; return (object) ['url' => $url]; }
function curl_setopt_array($handle, $options) { return true; }
function curl_exec($handle) { return $GLOBALS['response']; }
function curl_getinfo($handle, $option) { return $GLOBALS['response'] === false ? 503 : 200; }
function check($ok, $message) { if (!$ok) { throw new \RuntimeException($message); } echo "PASS $message\n"; }
eval('namespace ' . __NAMESPACE__ . ';' . substr($source, $start, $end - $start));
try {
    check(!defined('WRITEPATH'), 'Runs in the CRM environment without WRITEPATH');
    check(zillapage_fetch_library_section('') === '' && zillapage_fetch_library_section('invalid') === '', 'Missing or invalid optional library URLs remain empty');
    check($requests === 0, 'Invalid URLs do not trigger network requests');
    $url = 'https://library.example.test/header';
    check(zillapage_fetch_library_section($url) === $response, 'Uncached header/footer content loads without a fatal error');
    $cache = APPPATH . 'cache/zillapage_section_' . sha1($url) . '.html';
    check(is_file($cache) && file_get_contents($cache) === $response, 'Library cache uses the CRM application cache directory');
    check(zillapage_fetch_library_section($url) === $response && $requests === 1, 'Fresh cached content is reused without another fetch');
    touch($cache, time() - 901); clearstatcache(true, $cache);
    $response = '<footer>Updated content</footer>';
    check(zillapage_fetch_library_section($url) === $response && $requests === 2, 'Expired cache refreshes with the existing lifetime');
    $response = false;
    check(zillapage_fetch_library_section('https://library.example.test/unavailable') === '', 'Unavailable optional library content does not prevent page rendering');
} finally {
    foreach (glob(APPPATH . 'cache/*') as $file) { unlink($file); }
    rmdir(APPPATH . 'cache'); rmdir(APPPATH);
}

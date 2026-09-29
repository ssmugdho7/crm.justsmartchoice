<?php
// Use the absolute path to your CRM folder
$rootPath = '/home4/scusawco/public_html/crm.justsmartchoice';

// Recursive function to scan directories
function scanDirRecursive($dir, $prefix = '') {
    $result = '';
    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;

        $fullPath = $dir . '/' . $file;
        $result .= $prefix . $file . "\n";

        if (is_dir($fullPath)) {
            $result .= scanDirRecursive($fullPath, $prefix . '    ');
        }
    }
    return $result;
}

// Scan and output
$output = scanDirRecursive($rootPath);

// Save to a file
file_put_contents('crm_file_structure.txt', $output);

echo "<pre>$output</pre>";
echo "\n\nFile structure also saved to crm_file_structure.txt";
?>

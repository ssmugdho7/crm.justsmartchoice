<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Smart_module_packager
{
    protected $excluded = [
        '.git', '.github', '.svn', '.idea', '.vscode', 'node_modules', 'cache', 'logs', 'tmp', 'temp',
        '.DS_Store', 'Thumbs.db', '.env', 'composer.lock.bak'
    ];

    public function package($moduleName, $destinationDir)
    {
        if (!smart_module_manager_is_safe_slug($moduleName)) {
            throw new Exception(_l('smart_module_manager_error_invalid_module'));
        }

        $modulePath = FCPATH . 'modules/' . $moduleName;
        if (!is_dir($modulePath)) {
            throw new Exception(_l('smart_module_manager_error_missing_module'));
        }

        if (!is_dir($destinationDir)) {
            @mkdir($destinationDir, 0755, true);
        }

        $zipPath = rtrim($destinationDir, '/\\') . '/' . $moduleName . '_' . date('Ymd_His') . '.zip';
        $zip = new ZipArchive();

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new Exception(_l('smart_module_manager_error_zip'));
        }

        $this->addDirectoryToZip($zip, $modulePath, $moduleName);
        $zip->close();

        return $zipPath;
    }

    protected function addDirectoryToZip(ZipArchive $zip, $dir, $baseInZip)
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            $path = $file->getPathname();
            $relative = $baseInZip . '/' . substr($path, strlen($dir) + 1);
            $relative = str_replace('\\', '/', $relative);

            if ($this->shouldExclude($path, $relative)) {
                continue;
            }

            if ($file->isDir()) {
                $zip->addEmptyDir($relative);
            } elseif ($file->isFile()) {
                $zip->addFile($path, $relative);
            }
        }
    }

    protected function shouldExclude($path, $relative)
    {
        foreach ($this->excluded as $excluded) {
            if (strpos($relative, '/' . $excluded . '/') !== false || substr($relative, -strlen('/' . $excluded)) === '/' . $excluded) {
                return true;
            }
            if (basename($path) === $excluded) {
                return true;
            }
        }
        return false;
    }
}

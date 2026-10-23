<?php
defined('BASEPATH') or exit('No direct script access allowed');
/** Parse module-owned PHP without executing controllers, migrations or business actions. */
class Debug_mode_module_auditor
{
    public static function scan($root, $module)
    {
        if (!is_string($module) || !preg_match('/^[a-zA-Z0-9_-]+$/D', $module)) { return ['error' => 'Invalid module']; }
        $base = realpath($root);
        $path = realpath($root . '/' . $module);
        if (!$base || !$path || dirname($path) !== $base || !is_file($path . '/' . $module . '.php')) { return ['error' => 'Module not found']; }
        $result = ['module' => $module, 'checked' => 0, 'skipped' => 0, 'issues' => [], 'limited' => false];
        $start = microtime(true);
        try {
            $directory = new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS);
            $filter = new RecursiveCallbackFilterIterator($directory, static function ($file) {
                return !$file->isLink() && (!$file->isDir() || !in_array(strtolower($file->getFilename()), ['vendor', 'node_modules', 'assets', 'uploads', 'cache', '.git'], true));
            });
            foreach (new RecursiveIteratorIterator($filter) as $file) {
                if (strtolower($file->getExtension()) !== 'php') { continue; }
                if ($result['checked'] >= 1000 || microtime(true) - $start > 8) { $result['limited'] = true; break; }
                if ($file->getSize() > 2097152 || !$file->isReadable()) { $result['skipped']++; continue; }
                $source = file_get_contents($file->getPathname());
                if ($source === false) { $result['skipped']++; continue; }
                $result['checked']++;
                try { token_get_all($source, TOKEN_PARSE); }
                catch (ParseError $e) {
                    $result['issues'][] = ['file' => substr($file->getPathname(), strlen($path) + 1), 'line' => $e->getLine(), 'message' => $e->getMessage()];
                    if (count($result['issues']) >= 50) { $result['limited'] = true; break; }
                }
            }
        } catch (UnexpectedValueException $e) { $result['limited'] = true; $result['error'] = 'Some module directories could not be read'; }
        return $result;
    }
}

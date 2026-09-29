<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Repair_scanner
{
    private $CI;
    private $root;
    private $exclude = ['.git', 'application/cache', 'application/logs', 'uploads', 'backups', 'node_modules'];

    public function __construct()
    {
        $this->CI   = &get_instance();
        $configured = trim((string) get_option('sc_ai_repair_crm_root'));
        $real       = $configured !== '' ? realpath($configured) : false;
        $this->root = rtrim($real !== false ? $real : FCPATH, DIRECTORY_SEPARATOR);

        if (get_option('sc_ai_repair_scan_vendor') !== '1') {
            $this->exclude[] = 'application/vendor';
            $this->exclude[] = 'system';
        }
    }

    public function run($run)
    {
        @set_time_limit(0);
        @ini_set('memory_limit', '512M');
        $out = [];
        $this->check_paths($out);
        $this->check_migrations($out);
        $this->check_module_migrations($out);
        $this->check_php($out);
        $this->check_languages($out);
        foreach ($out as $finding) {
            $this->CI->db->insert(db_prefix() . 'sc_ai_repair_findings', [
                'run_id'   => $run,
                'severity' => $finding['severity'],
                'category' => $finding['category'],
                'path'     => $finding['path'] ?? null,
                'message'  => $finding['message'],
                'solution' => $finding['solution'] ?? '',
            ]);
        }
        return $out;
    }

    private function check_paths(&$out)
    {
        foreach (['application', 'system', 'modules', 'assets', 'uploads'] as $path) {
            $full = $this->root . DIRECTORY_SEPARATOR . $path;
            if (!is_dir($full)) {
                $out[] = $this->finding('critical', 'structure', $path, 'Required CRM directory is missing.', 'Restore it from a verified backup.');
            }
        }
        foreach (['application/cache', 'application/logs', 'uploads'] as $path) {
            $full = $this->root . DIRECTORY_SEPARATOR . $path;
            if (is_dir($full) && !is_writable($full)) {
                $out[] = $this->finding('error', 'permissions', $path, 'Directory is not writable.', 'Correct owner/group and write permissions.');
            }
        }
    }

    private function check_migrations(&$out)
    {
        $files = glob($this->root . '/application/migrations/*_version_*.php') ?: [];
        $numbers = [];
        foreach ($files as $file) {
            if (preg_match('/^(\d+)_version_/i', basename($file), $match)) {
                $numbers[] = (int) $match[1];
            }
        }
        sort($numbers);
        $duplicates = array_diff_assoc($numbers, array_unique($numbers));
        foreach (array_unique($duplicates) as $number) {
            $out[] = $this->finding('critical', 'migration', 'application/migrations', 'Duplicate migration ' . $number . '.', 'Keep one unique migration number and never delete historical migrations.');
        }
        if ($numbers) {
            for ($number = min($numbers); $number < max($numbers); $number++) {
                if (!in_array($number, $numbers, true)) {
                    $out[] = $this->finding('warning', 'migration', 'application/migrations', 'Migration gap at ' . $number . '.', 'Add a safe bridge migration if the application expects continuous versions.');
                }
            }
        }
    }


    private function check_module_migrations(&$out)
    {
        $this->CI->load->library('sc_ai_repair/Migration_guard');
        foreach ($this->CI->migration_guard->validate_all_modules() as $result) {
            if (!empty($result['error'])) {
                $out[] = $this->finding(
                    'critical',
                    'module_migration',
                    'modules/' . ($result['slug'] ?? ''),
                    $result['error'],
                    'Repair the module Version header and sequential migration files before upgrading.'
                );
            }
        }
    }

    private function check_php(&$out)
    {
        $roots = [$this->root . '/application', $this->root . '/modules'];
        $maxBytes = max(65536, min(2097152, (int) get_option('sc_ai_repair_max_file_bytes')));
        foreach ($roots as $root) {
            if (!is_dir($root)) {
                continue;
            }
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
            foreach ($iterator as $file) {
                if ($file->getExtension() !== 'php' || $this->excluded($file->getPathname()) || $file->getSize() > $maxBytes) {
                    continue;
                }
                $output = [];
                $code = 0;
                $php = defined('PHP_BINARY') && PHP_BINARY ? PHP_BINARY : 'php';
                exec(escapeshellarg($php) . ' -l ' . escapeshellarg($file->getPathname()) . ' 2>&1', $output, $code);
                if ($code !== 0) {
                    $out[] = $this->finding('critical', 'php_syntax', $this->relative($file->getPathname()), implode("\n", $output), 'Repair PHP syntax before loading this file.');
                }
            }
        }
    }

    private function check_languages(&$out)
    {
        foreach (glob($this->root . '/modules/*/language/english/*_lang.php') ?: [] as $english) {
            $slug    = basename(dirname(dirname($english)));
            $spanish = $this->root . '/modules/' . $slug . '/language/spanish/' . basename($english);
            if (!file_exists($spanish)) {
                $out[] = $this->finding('warning', 'language', $this->relative($spanish), 'Spanish language file is missing.', 'Create Spanish file with the same keys as English.');
            }
        }
    }

    private function excluded($path)
    {
        $relative = str_replace('\\', '/', $this->relative($path));
        foreach ($this->exclude as $excluded) {
            if (str_starts_with($relative, $excluded . '/') || $relative === $excluded) {
                return true;
            }
        }
        return false;
    }

    private function relative($path)
    {
        return ltrim(str_replace(str_replace('\\', '/', $this->root), '', str_replace('\\', '/', $path)), '/');
    }

    private function finding($severity, $category, $path, $message, $solution)
    {
        return compact('severity', 'category', 'path', 'message', 'solution');
    }
}

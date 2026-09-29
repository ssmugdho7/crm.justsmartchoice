<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Repair_engine
{
    private $CI;
    private $root;
    private $protected = [
        'application/config/database.php',
        'application/config/config.php',
        'uploads/',
        'application/vendor/',
        'system/',
    ];

    public function __construct()
    {
        $this->CI = &get_instance();
        $configured = trim((string) get_option('sc_ai_repair_crm_root'));
        $real = $configured !== '' ? realpath($configured) : false;
        $this->root = rtrim($real !== false ? $real : FCPATH, DIRECTORY_SEPARATOR);
        $this->CI->load->library('sc_ai_repair/Openai_repair_client');
        $this->CI->load->library('sc_ai_repair/Migration_guard');
    }

    public function analyze($run, $error)
    {
        $context = $this->context_from_error($error);
        $system = 'You are a senior Perfex CRM 3.4.x and CodeIgniter 3 repair engine. Produce minimal complete-file repairs only. Preserve features, data, API keys, uploads, settings, routes, permissions, and migrations. Never edit vendor, system, database credentials, encryption keys, or uploads. Return only the required JSON schema. The application will safely add required module/core version migrations when needed.';
        $plan = $this->CI->openai_repair_client->request($system, json_encode([
            'error' => $error,
            'context' => $context,
            'php' => PHP_VERSION,
            'crm_root_structure' => $this->structure_summary(),
        ]));
        $this->validate_plan($plan);
        $dir = $this->plan_dir($run);
        file_put_contents($dir . '/plan.json', json_encode($plan, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);
        return $plan;
    }

    public function apply_plan($run)
    {
        $planFile = $this->plan_dir($run) . '/plan.json';
        if (!is_file($planFile)) {
            throw new RuntimeException('Repair plan not found.');
        }

        $plan = json_decode(file_get_contents($planFile), true);
        $this->validate_plan($plan);
        $plan['changes'] = $this->CI->migration_guard->ensure_upgrade_changes($plan['changes']);
        $migrationReport = $this->CI->migration_guard->validate_repair_plan($plan['changes']);
        file_put_contents($planFile, json_encode($plan, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);

        $applied = [];
        $this->CI->db->trans_begin();
        try {
            foreach ($plan['changes'] as $change) {
                $path = $this->safe_path($change['path']);
                if ($change['operation'] === 'rename') {
                    $renameResult = $this->rename_asset($change['path'], $change['new_path'], $run);
                    $applied[] = [
                        'path' => $renameResult['renamed_to'],
                        'operation' => 'rename',
                        'details' => 'Renamed from ' . $renameResult['renamed_from'],
                    ];
                    continue;
                }

                $full = $this->root . DIRECTORY_SEPARATOR . $path;
                $before = is_file($full) ? file_get_contents($full) : '';
                $backup = $this->backup_file($path, $run);
                $tmp = $full . '.scair.tmp';
                if (!is_dir(dirname($full)) && !mkdir(dirname($full), 0755, true) && !is_dir(dirname($full))) {
                    throw new RuntimeException('Cannot create directory for ' . $path);
                }
                if (file_put_contents($tmp, (string) $change['content'], LOCK_EX) === false) {
                    throw new RuntimeException('Cannot write temporary repair file for ' . $path);
                }
                if (pathinfo($full, PATHINFO_EXTENSION) === 'php') {
                    $this->lint($tmp);
                }
                if (!rename($tmp, $full)) {
                    @unlink($tmp);
                    throw new RuntimeException('Cannot replace ' . $path);
                }
                $this->record_change($run, $path, $change['operation'], $before, (string) $change['content'], $backup);
                $applied[] = [
                    'path' => $path,
                    'operation' => $change['operation'],
                    'details' => $change['reason'] ?? 'Repair applied',
                ];
            }

            $summary = 'Changes made successfully. ' . count($applied) . ' file change(s) applied.';
            $this->CI->db->where('id', $run)->update(db_prefix() . 'sc_ai_repair_runs', [
                'status' => 'applied',
                'summary' => $summary,
                'completed_at' => date('Y-m-d H:i:s'),
            ]);

            if ($this->CI->db->trans_status() === false) {
                throw new RuntimeException('Database transaction failed while recording the repair.');
            }
            $this->CI->db->trans_commit();
        } catch (Throwable $e) {
            $this->CI->db->trans_rollback();
            $this->rollback_files_only($run);
            throw $e;
        }

        return [
            'message' => 'Changes made successfully.',
            'changed' => $applied,
            'migration_validation' => $migrationReport,
            'run_id' => (int) $run,
            'report_url' => admin_url('sc_ai_repair/report/' . (int) $run),
        ];
    }

    public function rollback($run)
    {
        $count = $this->rollback_files_only($run);
        $this->CI->db->where('id', $run)->update(db_prefix() . 'sc_ai_repair_runs', [
            'status' => 'rolled_back',
            'summary' => 'Rollback completed. ' . $count . ' file change(s) restored.',
            'completed_at' => date('Y-m-d H:i:s'),
        ]);
        return $count;
    }

    public function clear_cache()
    {
        $cache = $this->root . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'cache';
        $deleted = 0;
        if (is_dir($cache)) {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($cache, FilesystemIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST
            );
            foreach ($iterator as $item) {
                $name = $item->getFilename();
                if (in_array($name, ['index.html', '.htaccess'], true)) {
                    continue;
                }
                if ($item->isDir()) {
                    @rmdir($item->getPathname());
                } elseif (@unlink($item->getPathname())) {
                    $deleted++;
                }
            }
        }
        $opcache = false;
        if (function_exists('opcache_reset')) {
            $opcache = (bool) @opcache_reset();
        }
        return ['deleted_files' => $deleted, 'opcache_reset' => $opcache];
    }

    public function rename_asset($old, $new, $run = 0)
    {
        $old = $this->safe_path($old);
        $new = $this->safe_path($new);
        $from = $this->root . DIRECTORY_SEPARATOR . $old;
        $to = $this->root . DIRECTORY_SEPARATOR . $new;
        if (!is_file($from)) {
            throw new RuntimeException('Source asset not found.');
        }
        if (is_file($to)) {
            throw new RuntimeException('Destination already exists.');
        }
        if (!is_dir(dirname($to)) && !mkdir(dirname($to), 0755, true) && !is_dir(dirname($to))) {
            throw new RuntimeException('Cannot create the destination directory.');
        }
        $backup = $this->backup_file($old, $run ?: time());
        $oldExt = strtolower(pathinfo($from, PATHINFO_EXTENSION));
        $newExt = strtolower(pathinfo($to, PATHINFO_EXTENSION));
        if ($oldExt !== $newExt && in_array($oldExt, ['png', 'jpg', 'jpeg', 'webp'], true) && in_array($newExt, ['png', 'jpg', 'jpeg', 'webp'], true)) {
            $this->convert_image($from, $to, $oldExt, $newExt);
            if (!unlink($from)) {
                throw new RuntimeException('Converted asset but could not remove original file.');
            }
        } elseif (!rename($from, $to)) {
            throw new RuntimeException('Asset rename failed.');
        }

        $changed = [];
        foreach ($this->reference_files() as $file) {
            $text = file_get_contents($file);
            $next = str_replace([$old, basename($old)], [$new, basename($new)], $text);
            if ($next !== $text) {
                $rel = $this->rel($file);
                $fileBackup = $this->backup_file($rel, $run ?: time());
                file_put_contents($file, $next, LOCK_EX);
                if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                    $this->lint($file);
                }
                if ($run) {
                    $this->record_change($run, $rel, 'replace', $text, $next, $fileBackup);
                }
                $changed[] = $rel;
            }
        }
        if ($run) {
            $this->record_change($run, $new, 'rename', '', $new, $backup);
        }
        return ['renamed_from' => $old, 'renamed_to' => $new, 'references_updated' => $changed];
    }

    private function rollback_files_only($run)
    {
        $rows = $this->CI->db->where('run_id', $run)->where('applied', 1)->where('rolled_back', 0)->order_by('id', 'DESC')->get(db_prefix() . 'sc_ai_repair_changes')->result_array();
        foreach ($rows as $row) {
            $full = $this->root . DIRECTORY_SEPARATOR . $row['path'];
            if ($row['operation'] === 'create') {
                if (is_file($full)) {
                    @unlink($full);
                }
            } elseif ($row['backup_path'] && is_file($row['backup_path'])) {
                if (!is_dir(dirname($full))) {
                    @mkdir(dirname($full), 0755, true);
                }
                copy($row['backup_path'], $full);
            }
            $this->CI->db->where('id', $row['id'])->update(db_prefix() . 'sc_ai_repair_changes', ['rolled_back' => 1]);
        }
        return count($rows);
    }

    private function convert_image($from, $to, $oldExt, $newExt)
    {
        if (!extension_loaded('gd')) {
            throw new RuntimeException('GD extension is required to convert image formats.');
        }
        if ($oldExt === 'png') {
            $image = imagecreatefrompng($from);
        } elseif ($oldExt === 'webp') {
            $image = imagecreatefromwebp($from);
        } else {
            $image = imagecreatefromjpeg($from);
        }
        if (!$image) {
            throw new RuntimeException('Unable to read source image.');
        }
        if (in_array($newExt, ['jpg', 'jpeg'], true)) {
            $background = imagecreatetruecolor(imagesx($image), imagesy($image));
            $white = imagecolorallocate($background, 255, 255, 255);
            imagefill($background, 0, 0, $white);
            imagecopy($background, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));
            imagedestroy($image);
            $image = $background;
            $ok = imagejpeg($image, $to, 90);
        } elseif ($newExt === 'png') {
            imagesavealpha($image, true);
            $ok = imagepng($image, $to, 6);
        } else {
            imagesavealpha($image, true);
            $ok = imagewebp($image, $to, 88);
        }
        imagedestroy($image);
        if (!$ok) {
            throw new RuntimeException('Image conversion failed.');
        }
    }

    private function validate_plan($plan)
    {
        if (!is_array($plan) || !isset($plan['changes']) || !is_array($plan['changes'])) {
            throw new RuntimeException('Invalid plan.');
        }
        foreach ($plan['changes'] as $change) {
            if (empty($change['path']) || empty($change['operation'])) {
                throw new RuntimeException('Invalid repair change entry.');
            }
            $this->safe_path($change['path']);
            if (!in_array($change['operation'], ['replace', 'create', 'rename'], true)) {
                throw new RuntimeException('Unsupported operation.');
            }
            if ($change['operation'] !== 'rename' && !array_key_exists('content', $change)) {
                throw new RuntimeException('Repair content is missing for ' . $change['path']);
            }
        }
    }

    private function safe_path($path)
    {
        $path = ltrim(str_replace('\\', '/', $path), '/');
        if ($path === '' || str_contains($path, '..') || preg_match('#^[A-Za-z]:#', $path)) {
            throw new RuntimeException('Unsafe path.');
        }
        foreach ($this->protected as $protected) {
            if ($path === $protected || str_starts_with($path, $protected)) {
                throw new RuntimeException('Protected path: ' . $path);
            }
        }
        return $path;
    }

    private function context_from_error($error)
    {
        $paths = [];
        preg_match_all('#(?:/[^\s:]+)+\.(?:php|js|css)#', $error, $matches);
        foreach (array_slice(array_unique($matches[0]), 0, 8) as $path) {
            $relative = $this->rel($path);
            if ($relative && is_file($this->root . '/' . $relative) && filesize($this->root . '/' . $relative) < $this->max_context_bytes()) {
                $paths[$relative] = file_get_contents($this->root . '/' . $relative);
            }
        }
        return $paths;
    }

    private function structure_summary()
    {
        return implode("\n", array_slice($this->walk('', 2), 0, 500));
    }

    private function walk($sub, $depth)
    {
        $base = $this->root . ($sub ? '/' . $sub : '');
        $output = [];
        if ($depth < 0 || !is_dir($base)) {
            return $output;
        }
        foreach (scandir($base) as $name) {
            if ($name === '.' || $name === '..' || in_array($name, ['uploads', 'logs', 'cache', '.git', 'node_modules'], true)) {
                continue;
            }
            $relative = ltrim($sub . '/' . $name, '/');
            $output[] = $relative . (is_dir($base . '/' . $name) ? '/' : '');
            if (is_dir($base . '/' . $name)) {
                $output = array_merge($output, $this->walk($relative, $depth - 1));
            }
        }
        return $output;
    }

    private function plan_dir($run)
    {
        $directory = __DIR__ . '/../backups/run_' . (int) $run;
        if (!is_dir($directory)) {
            mkdir($directory, 0750, true);
        }
        return realpath($directory) ?: $directory;
    }

    private function backup_file($path, $run)
    {
        $source = $this->root . '/' . $path;
        $destination = $this->plan_dir($run) . '/files/' . $path;
        if (is_file($source)) {
            if (!is_dir(dirname($destination))) {
                mkdir(dirname($destination), 0750, true);
            }
            copy($source, $destination);
            return $destination;
        }
        return '';
    }

    private function lint($file)
    {
        exec('php -l ' . escapeshellarg($file) . ' 2>&1', $output, $code);
        if ($code !== 0) {
            throw new RuntimeException('PHP syntax validation failed: ' . implode("\n", $output));
        }
    }

    private function record_change($run, $path, $operation, $before, $after, $backup)
    {
        $this->CI->db->insert(db_prefix() . 'sc_ai_repair_changes', [
            'run_id' => $run,
            'path' => $path,
            'operation' => $operation,
            'before_hash' => hash('sha256', $before),
            'after_hash' => hash('sha256', $after),
            'backup_path' => $backup,
            'diff_text' => $this->simple_diff($before, $after),
            'applied' => 1,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function simple_diff($before, $after)
    {
        return "--- BEFORE\n" . $before . "\n+++ AFTER\n" . $after;
    }

    private function reference_files()
    {
        $output = [];
        foreach ([$this->root . '/application', $this->root . '/modules', $this->root . '/assets'] as $root) {
            if (!is_dir($root)) {
                continue;
            }
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
            foreach ($iterator as $file) {
                if (!in_array(strtolower($file->getExtension()), ['php', 'js', 'css', 'html', 'json'], true) || $file->getSize() > $this->max_context_bytes()) {
                    continue;
                }
                $output[] = $file->getPathname();
            }
        }
        return $output;
    }

    private function max_context_bytes()
    {
        return max(65536, min(2097152, (int) get_option('sc_ai_repair_max_file_bytes')));
    }

    private function rel($path)
    {
        $path = str_replace('\\', '/', $path);
        $root = str_replace('\\', '/', $this->root);
        return ltrim(str_replace($root, '', $path), '/');
    }
}

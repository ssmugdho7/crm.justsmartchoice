<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_guard
{
    private $CI;
    private $root;

    public function __construct()
    {
        $this->CI = &get_instance();
        $configured = trim((string) get_option('sc_ai_repair_crm_root'));
        $real = $configured !== '' ? realpath($configured) : false;
        $this->root = rtrim($real !== false ? $real : FCPATH, DIRECTORY_SEPARATOR);
    }

    public function ensure_upgrade_changes(array $changes)
    {
        $modules = [];
        $touchesCore = false;
        foreach ($changes as $change) {
            foreach ($this->change_paths($change) as $path) {
                if (preg_match('#^modules/([^/]+)/#', $path, $match)) {
                    if (!str_contains($path, '/migrations/')) {
                        $modules[$match[1]] = true;
                    }
                } elseif ((str_starts_with($path, 'application/') || str_starts_with($path, 'assets/')) && !str_starts_with($path, 'application/migrations/')) {
                    $touchesCore = true;
                }
            }
        }

        foreach (array_keys($modules) as $slug) {
            $mainPath = 'modules/' . $slug . '/' . $slug . '.php';
            $currentMainFile = $this->root . '/' . $mainPath;
            if (!is_file($currentMainFile)) {
                throw new RuntimeException('Module main file not found: ' . $mainPath);
            }
            $installedContent = file_get_contents($currentMainFile);
            $plannedContent = $this->planned_content($changes, $mainPath);
            $workingContent = $plannedContent === null ? $installedContent : $plannedContent;
            $installedVersion = $this->read_version_from_content($installedContent, $mainPath);
            $target = $this->version_number($installedVersion) + 1;
            $targetVersion = $this->number_to_version($target);
            $updatedMain = preg_replace('/(^\s*Version:\s*)([^\r\n*]+)/mi', '$1' . $targetVersion, $workingContent, 1);
            $changes = $this->upsert_change($changes, $mainPath, $updatedMain, 'replace', 'Increment module version for upgrade detection.');

            $migrationPath = 'modules/' . $slug . '/migrations/' . $target . '_version_' . $target . '.php';
            if ($this->planned_content($changes, $migrationPath) === null && !is_file($this->root . '/' . $migrationPath)) {
                $content = "<?php\ndefined('BASEPATH') or exit('No direct script access allowed');\n\nclass Migration_Version_{$target} extends App_module_migration\n{\n    public function up()\n    {\n        // File repair upgrade marker. Schema and existing data are preserved.\n    }\n\n    public function down()\n    {\n        // Upgrade only. Use AI Repair rollback to restore repaired files.\n    }\n}\n";
                $changes[] = ['path' => $migrationPath, 'operation' => 'create', 'content' => $content, 'reason' => 'Create required module migration.'];
            }
        }

        if ($touchesCore) {
            $numbers = $this->migration_numbers($this->root . '/application/migrations');
            if (!$numbers) {
                throw new RuntimeException('CRM core migration history is missing.');
            }
            $target = max($numbers) + 1;
            $migrationPath = 'application/migrations/' . $target . '_version_' . $target . '.php';
            if ($this->planned_content($changes, $migrationPath) === null && !is_file($this->root . '/' . $migrationPath)) {
                $content = "<?php\ndefined('BASEPATH') or exit('No direct script access allowed');\n\nclass Migration_Version_{$target} extends App_migration\n{\n    public function up()\n    {\n        // Core file repair upgrade marker. Schema and existing data are preserved.\n    }\n}\n";
                $changes[] = ['path' => $migrationPath, 'operation' => 'create', 'content' => $content, 'reason' => 'Create required CRM core migration.'];
            }
        }

        return $changes;
    }

    public function validate_repair_plan(array $changes)
    {
        $moduleChanges = [];
        $coreChanges = [];
        foreach ($changes as $change) {
            foreach ($this->change_paths($change) as $path) {
                if (preg_match('#^modules/([^/]+)/#', $path, $match)) {
                    $moduleChanges[$match[1]][] = $change;
                } elseif (str_starts_with($path, 'application/') || str_starts_with($path, 'assets/')) {
                    $coreChanges[] = $change;
                }
            }
        }

        $report = [];
        foreach ($moduleChanges as $slug => $scoped) {
            $mainPath = 'modules/' . $slug . '/' . $slug . '.php';
            $mainFile = $this->root . '/' . $mainPath;
            if (!is_file($mainFile)) {
                throw new RuntimeException('Repair blocked: module main file is missing for ' . $slug . '.');
            }
            $oldVersion = $this->read_module_version($mainFile);
            $plannedMain = $this->planned_content($changes, $mainPath);
            if ($plannedMain === null) {
                throw new RuntimeException('Repair blocked: module version update is missing for ' . $slug . '.');
            }
            $newVersion = $this->read_version_from_content($plannedMain, $mainPath);
            $target = $this->version_number($newVersion);
            if ($target !== $this->version_number($oldVersion) + 1) {
                throw new RuntimeException('Repair blocked: ' . $slug . ' must advance sequentially by one migration.');
            }
            $migrationPath = 'modules/' . $slug . '/migrations/' . $target . '_version_' . $target . '.php';
            $migration = $this->planned_content($changes, $migrationPath);
            if ($migration === null && is_file($this->root . '/' . $migrationPath)) {
                $migration = file_get_contents($this->root . '/' . $migrationPath);
            }
            if ($migration === null) {
                throw new RuntimeException('Repair blocked: required migration is missing: ' . $migrationPath);
            }
            $this->validate_migration_content($migration, $target, $migrationPath, true);
            $report[] = ['scope' => 'module', 'slug' => $slug, 'from' => $oldVersion, 'to' => $newVersion, 'migration' => $target];
        }

        if ($coreChanges) {
            $numbers = $this->migration_numbers($this->root . '/application/migrations');
            $target = max($numbers) + 1;
            $migrationPath = 'application/migrations/' . $target . '_version_' . $target . '.php';
            $migration = $this->planned_content($changes, $migrationPath);
            if ($migration === null && is_file($this->root . '/' . $migrationPath)) {
                $migration = file_get_contents($this->root . '/' . $migrationPath);
            }
            if ($migration === null) {
                throw new RuntimeException('Repair blocked: CRM core changes require ' . $migrationPath);
            }
            $this->validate_migration_content($migration, $target, $migrationPath, false);
            $report[] = ['scope' => 'core', 'from' => max($numbers), 'to' => $target, 'migration' => $target];
        }
        return $report;
    }

    private function upsert_change(array $changes, $path, $content, $operation, $reason)
    {
        foreach ($changes as $index => $change) {
            if (ltrim(str_replace('\\', '/', (string) ($change['path'] ?? '')), '/') === $path) {
                $changes[$index]['content'] = $content;
                $changes[$index]['operation'] = $operation;
                $changes[$index]['reason'] = $reason;
                return $changes;
            }
        }
        $changes[] = ['path' => $path, 'operation' => $operation, 'content' => $content, 'reason' => $reason];
        return $changes;
    }

    private function change_paths(array $change)
    {
        $paths = [ltrim(str_replace('\\', '/', (string) ($change['path'] ?? '')), '/')];
        if (($change['operation'] ?? '') === 'rename' && !empty($change['new_path'])) {
            $paths[] = ltrim(str_replace('\\', '/', (string) $change['new_path']), '/');
        }
        return $paths;
    }

    private function planned_content(array $changes, $path)
    {
        foreach ($changes as $change) {
            if (ltrim(str_replace('\\', '/', (string) ($change['path'] ?? '')), '/') === $path && in_array(($change['operation'] ?? ''), ['replace', 'create'], true)) {
                return (string) ($change['content'] ?? '');
            }
        }
        return null;
    }

    private function validate_migration_content($content, $number, $path, $module)
    {
        $base = $module ? 'App_module_migration' : 'App_migration';
        if (!preg_match('/class\s+Migration_Version_' . preg_quote((string) $number, '/') . '\s+extends\s+' . preg_quote($base, '/') . '/i', $content)) {
            throw new RuntimeException('Repair blocked: migration class/base mismatch in ' . $path . '.');
        }
        if (!preg_match('/function\s+up\s*\(/i', $content)) {
            throw new RuntimeException('Repair blocked: migration ' . $number . ' has no up() method.');
        }
        if ($module && !preg_match('/function\s+down\s*\(/i', $content)) {
            throw new RuntimeException('Repair blocked: module migration ' . $number . ' has no down() method.');
        }
    }

    private function migration_numbers($dir)
    {
        $numbers = [];
        foreach (glob(rtrim($dir, '/') . '/*_version_*.php') ?: [] as $file) {
            if (preg_match('/^(\d+)_version_(\d+)\.php$/', basename($file), $match) && (int) $match[1] === (int) $match[2]) {
                $numbers[] = (int) $match[1];
            }
        }
        return $numbers;
    }

    private function read_module_version($file)
    {
        return $this->read_version_from_content(file_get_contents($file, false, null, 0, 8192), $file);
    }

    private function read_version_from_content($content, $path)
    {
        if (preg_match('/^\s*Version:\s*([^\r\n*]+)/mi', $content, $match)) {
            return trim($match[1]);
        }
        throw new RuntimeException('Version header is missing in ' . $path . '.');
    }

    private function version_number($version)
    {
        $digits = preg_replace('/\D+/', '', (string) $version);
        return $digits === '' ? 0 : (int) $digits;
    }

    private function number_to_version($number)
    {
        $digits = str_pad((string) $number, 3, '0', STR_PAD_LEFT);
        return ((int) $digits[0]) . '.' . ((int) $digits[1]) . '.' . ((int) substr($digits, 2));
    }
}

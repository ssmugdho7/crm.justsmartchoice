<?php

use Carbon\Carbon;

defined('BASEPATH') or exit('No direct script access allowed');

class Backup_module
{
    private $ci;

    public function __construct()
    {
        $this->ci = &get_instance();
    }

    public function make_backup_db($manual = false)
    {
        $auto_backup_hour = intval(get_option('auto_backup_hour')) ? intval(get_option('auto_backup_hour')) : 6;
        $current_time = Carbon::today()->hour($auto_backup_hour)->timestamp;
        $last_backup_time = Carbon::createFromTimestamp(intval(get_option('last_auto_backup')) + intval(get_option('auto_backup_every')) * 24 * 60 * 60)->timestamp;

        if ((get_option('auto_backup_enabled') == '1' && $current_time > $last_backup_time) || $manual == true) {
            $this->create_backup_directory();

            $manager = $this->get_backup_manager_name();

            if ($manager == 'backup_manager') {
                $configFileSystemProvider = new \BackupManager\Config\Config([
                    'local' => [
                        'type' => 'Local',
                        'root' => BACKUPS_FOLDER,
                    ],
                ]);

                if ($this->ci->db->dbdriver != 'mysqli') {
                    return $this->database_backup_codeigniter($manual);
                }

                $port = '';
                if ($parsePort = parse_url(APP_DB_HOSTNAME, PHP_URL_PORT)) {
                    if (is_int($parsePort)) {
                        $port = $parsePort;
                    }
                }

                $configDatabase = new \BackupManager\Config\Config([
                    'production' => [
                        'type'     => 'mysql',
                        'host'     => APP_DB_HOSTNAME,
                        'port'     => $port,
                        'user'     => APP_DB_USERNAME,
                        'pass'     => APP_DB_PASSWORD,
                        'database' => APP_DB_NAME,
                    ],
                ]);

                $filesystems = new \BackupManager\Filesystems\FilesystemProvider($configFileSystemProvider);
                $filesystems->add(new \BackupManager\Filesystems\LocalFilesystem);

                $databases = new \BackupManager\Databases\DatabaseProvider($configDatabase);
                $databases->add(new \BackupManager\Databases\MysqlDatabase);

                $compressors = new \BackupManager\Compressors\CompressorProvider;
                $compressors->add(new \BackupManager\Compressors\GzipCompressor);
                $compressors->add(new \BackupManager\Compressors\NullCompressor);

                $manager = new \BackupManager\Manager($filesystems, $databases, $compressors);

                $backup_name = date('Y-m-d-H-i-s') . '_backup-v' . wordwrap($this->ci->app->get_current_db_version(), 1, '-', true) . '.sql';

                try {
                    $manager->makeBackup()
                        ->run('production', [
                            new \BackupManager\Filesystems\Destination('local', $backup_name),
                        ], 'null');

                    log_activity('Database Backup [' . $backup_name . '.gz' . ']', null);

                    if ($manual == false) {
                        update_option('last_auto_backup', time());
                    }

                    $this->maybe_delete_old_backups();

                    return true;
                } catch (Exception $e) {
                    if (ENVIRONMENT !== 'production') {
                        log_activity('NEW BACKUP MANAGER ERROR [' . $e->getMessage() . ']');
                    }

                    return false;
                }
            } elseif ($manager == 'codeigniter') {
                return $this->database_backup_codeigniter($manual);
            }
        }

        return false;
    }

    public function get_backup_manager_name()
    {
        if (defined('APP_DATABASE_BACKUP_MANAGER')) {
            return APP_DATABASE_BACKUP_MANAGER;
        }

        return extension_loaded('proc_open') ? 'backup_manager' : 'codeigniter';
    }

    public function create_backup_directory()
    {
        if (!is_dir(BACKUPS_FOLDER)) {
            mkdir(BACKUPS_FOLDER, 0755);

            $fp = fopen(rtrim(BACKUPS_FOLDER, '/') . '/index.html', 'w');
            if ($fp) {
                fclose($fp);
            }

            $htaccess = BACKUPS_FOLDER . '.htaccess';
            $fp = fopen($htaccess, 'w');

            if ($fp) {
                fwrite($fp, 'Order Deny,Allow' . PHP_EOL . 'Deny from all');
                fclose($fp);
            }
        }
    }

    private function database_backup_codeigniter($manual = false)
    {
        $this->handle_memory_limit_error();

        $this->ci->load->dbutil();

        $prefs = [
            'format'   => 'zip',
            'filename' => date('Y-m-d-H-i-s') . '_backup.sql',
        ];

        $backup = @$this->ci->dbutil->backup($prefs);
        $backup_name = unique_filename(
            BACKUPS_FOLDER,
            'database_backup_' . date('Y-m-d-H-i-s') . '-v' . wordwrap($this->ci->app->get_current_db_version(), 1, '-', true) . '.zip'
        );

        $save_backup_path = BACKUPS_FOLDER . $backup_name;

        $this->ci->load->helper('file');

        if (@write_file($save_backup_path, $backup)) {
            log_activity('Database Backup [' . $backup_name . ']', null);

            if ($manual == false) {
                update_option('last_auto_backup', time());
            }

            $this->maybe_delete_old_backups();

            return true;
        }

        return false;
    }

    private function maybe_delete_old_backups()
    {
        $delete_backups = get_option('delete_backups_older_then');

        if ($delete_backups != '0') {
            $backups = list_files(BACKUPS_FOLDER);
            $backups_days_to_seconds = ($delete_backups * 24 * 60 * 60);

            foreach ($backups as $b) {
                if ($b == 'index.html') {
                    continue;
                }

                if ((time() - filectime(BACKUPS_FOLDER . $b)) > $backups_days_to_seconds) {
                    @unlink(BACKUPS_FOLDER . $b);
                }
            }
        }
    }

    private function handle_memory_limit_error()
    {
        register_shutdown_function(function () {
            $error = error_get_last();

            if ($error === null) {
                return;
            }

            $message = isset($error['message']) ? $error['message'] : '';
            $file = isset($error['file']) ? $error['file'] : 'Unknown file';
            $line = isset($error['line']) ? $error['line'] : 'Unknown line';

            if (strpos($message, 'Allowed memory size of') !== false) {
                echo '<h2>A fatal error has been triggered during backup because of PHP memory limit.</h2>';
                echo '<div style="font-size:18px;">';
                echo '<p>Your current PHP memory limit is ' . ini_get('memory_limit') . ' which seems <b>too low</b> to process the database backup.</p>';
                echo '<p>As a suggestion please try the following:</p>';
                echo '<ul>';
                echo '<li>Increase the PHP memory limit and try to perform the database backup again.</li>';
                echo '<li>Try the optional backup manager by defining this constant in application/config/app-config.php: <pre><code>define(\'APP_DATABASE_BACKUP_MANAGER\', \'backup_manager\');</code></pre></li>';
                echo '</ul>';
                echo '</div>';

                return;
            }

            $fatalTypes = [
                E_ERROR,
                E_PARSE,
                E_CORE_ERROR,
                E_COMPILE_ERROR,
                E_USER_ERROR,
            ];

            if (isset($error['type']) && in_array($error['type'], $fatalTypes, true)) {
                throw new RuntimeException(
                    'Fatal error: ' .
                    $message .
                    ' in ' .
                    $file .
                    ' on line ' .
                    $line
                );
            }
        });
    }
}
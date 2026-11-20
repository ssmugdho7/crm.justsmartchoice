<?php

namespace app\services\upgrade;

defined('BASEPATH') or exit('No direct script access allowed');

use app\services\upgrade\Response;
use app\services\zip\Unzip;
use app\services\zip\ExtractException;
use app\services\upgrade\Config;

abstract class AbstractCurlAdapter implements CoreInterface
{
    use Response;

    protected $config;

    public function setConfig(Config $config)
    {
        $this->config = $config;
        $this->maybeCreateUpgradeDirectory();

        return $this;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function extract($zipFile)
    {
        $unzip = new Unzip();
        $unzip->throwExceptionOnInvalidFileName(false);

        try {
            $upgradeCopyLocation = $this->copyUpgrade($zipFile);

            $unzip->extract($zipFile, $this->config->extract_to);
        } catch (ExtractException $e) {
            $this->failedExtractException($zipFile, $upgradeCopyLocation);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    protected function maybeCreateUpgradeDirectory()
    {
        if (!is_dir($this->config->tmp_update_dir)) {
            mkdir($this->config->tmp_update_dir, DIR_WRITE_MODE);
            $fp = (\function_exists('sc_recovery_fopen') ? '\sc_recovery_fopen' : '\fopen')($this->config->tmp_update_dir . 'index.html', 'w');
            if ($fp) {
                (\function_exists('sc_recovery_fclose') ? '\sc_recovery_fclose' : '\fclose')($fp);
            }
        }
    }

    protected function copyUpgrade($zipFile)
    {
        if (!file_exists($zipFile)) {
            return false;
        }

        $copyFileLocation = app_temp_dir() . time() . '-upgrade-version-' . basename($zipFile);
        $upgradeCopied    = false;

        if (@(\function_exists('sc_recovery_copy') ? '\sc_recovery_copy' : '\copy')($zipFile, $copyFileLocation)) {

            // Delete old upgrade stored data
            if ($oldUpgradeData = get_last_upgrade_copy_data()) {
                @(\function_exists('sc_recovery_unlink') ? '\sc_recovery_unlink' : '\unlink')($oldUpgradeData->path);
            }

            $optionData = ['path' => $copyFileLocation, 'version' => $this->config->latest_version, 'time' => time()];
            update_option('last_upgrade_copy_data', json_encode($optionData));

            $upgradeCopied = true;
        }

        return $upgradeCopied ? $copyFileLocation : false;
    }

    protected function cleanTmpFiles()
    {
        if (is_dir($this->config->tmp_update_dir)) {
            @delete_files($this->config->tmp_update_dir);
            if (@!delete_dir($this->config->tmp_update_dir)) {
                @(\function_exists('sc_recovery_rename') ? '\sc_recovery_rename' : '\rename')($this->config->tmp_update_dir, $this->config->tmp_dir . 'delete_this_' . uniqid());
            }
        }
    }

    public function __destruct()
    {
        $this->cleanTmpFiles();
    }
}

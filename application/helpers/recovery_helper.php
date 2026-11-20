<?php
defined('BASEPATH') || PHP_SAPI === 'cli' or exit('No direct script access allowed');
require_once __DIR__ . '/../libraries/recovery/TrashStore.php';

function sc_recovery_root(): string
{
    return defined('SC_RECOVERY_ROOT') ? SC_RECOVERY_ROOT : dirname(rtrim(FCPATH, '/'), 2) . '/.crm-trash/' . substr(hash('sha256', rtrim(FCPATH, '/')), 0, 16);
}
function sc_recovery_enabled(): bool
{
    if (!defined('FCPATH')) return false;
    return defined('SC_RECOVERY_ENABLED') ? SC_RECOVERY_ENABLED : is_file(sc_recovery_root() . '/enabled');
}
function sc_recovery_store(): ScTrashStore
{
    static $store;
    return $store ?? ($store = new ScTrashStore(FCPATH, sc_recovery_root()));
}
function sc_recovery_batch(): string
{
    static $batch;
    return $batch ?? ($batch = bin2hex(random_bytes(16)));
}
function sc_recovery_actor(): string
{
    $ci = null;
    if (function_exists('get_instance')) $ci = &get_instance();
    if ($ci && isset($ci->session) && function_exists('is_staff_logged_in') && is_staff_logged_in()) return 'staff:' . (int)get_staff_user_id();
    if ($ci && isset($ci->session) && function_exists('is_client_logged_in') && is_client_logged_in()) return 'contact:' . (int)get_contact_user_id();
    return PHP_SAPI === 'cli' ? 'system' : 'request';
}
function sc_recovery_capture($path, string $event, ?string $destination = null): void
{
    if (!is_string($path) || !sc_recovery_enabled()) return;
    sc_recovery_store()->capture($path, $event, sc_recovery_batch(), sc_recovery_actor(), $destination);
}
function sc_recovery_operation(array $paths, callable $operation)
{
    $locks=sc_recovery_enabled()?sc_recovery_store()->lockPaths($paths):[];
    try{return $operation();}finally{ScTrashStore::unlockPaths($locks);}
}
function sc_recovery_unlink($filename, $context = null)
{
    $arguments=func_get_args();
    return sc_recovery_operation([$filename],function()use($filename,$arguments){sc_recovery_capture($filename,'delete');return unlink(...$arguments);});
}
function sc_recovery_rmdir($directory, $context = null)
{
    // rmdir only removes empty directories. File contents are captured by unlink.
    return rmdir(...func_get_args());
}
function sc_recovery_rename($from, $to, $context = null)
{
    $arguments=func_get_args();
    return sc_recovery_operation([$from,$to],function()use($from,$to,$arguments){sc_recovery_capture($from,'move');sc_recovery_capture($to,'replace');$result=rename(...$arguments);if($result)sc_recovery_capture($to,'write');return $result;});
}
function sc_recovery_copy($from, $to, $context = null)
{
    $arguments=func_get_args();
    return sc_recovery_operation([$to],function()use($to,$arguments){sc_recovery_capture($to,'replace');$result=copy(...$arguments);if($result)sc_recovery_capture($to,'write');return $result;});
}
function sc_recovery_move_uploaded_file($from, $to)
{
    return sc_recovery_operation([$to],function()use($from,$to){sc_recovery_capture($to,'replace');if(is_uploaded_file($from))sc_recovery_capture($from,'upload',$to);return move_uploaded_file($from,$to);});
}
function sc_recovery_file_put_contents($filename, $data, $flags = 0, $context = null)
{
    $arguments=func_get_args();
    return sc_recovery_operation([$filename],function()use($filename,$arguments){sc_recovery_capture($filename,'replace');$result=file_put_contents(...$arguments);if($result!==false)sc_recovery_capture($filename,'write');return $result;});
}
function sc_recovery_fopen($filename, $mode, $use_include_path = false, $context = null)
{
    $writes=is_string($mode)&&strpbrk($mode,'waxc+')!==false;
    $locks=$writes&&sc_recovery_enabled()?sc_recovery_store()->lockPaths([$filename]):[];
    try{
        if($writes)sc_recovery_capture($filename,'replace');
        $handle=fopen(...func_get_args());
        if($handle&&$locks){$GLOBALS['sc_recovery_open_files'][(int)$handle]=['path'=>$filename,'locks'=>$locks,'stream'=>$handle];$locks=[];}
        return $handle;
    }finally{ScTrashStore::unlockPaths($locks);}
}
function sc_recovery_fclose($stream)
{
    $id=is_resource($stream)?(int)$stream:null;$entry=$id!==null?($GLOBALS['sc_recovery_open_files'][$id]??null):null;
    if($entry!==null)fflush($stream);
    try{$result=fclose($stream);if($result&&$entry!==null)sc_recovery_capture($entry['path'],'write');return $result;}
    finally{if($id!==null)unset($GLOBALS['sc_recovery_open_files'][$id]);ScTrashStore::unlockPaths($entry['locks']??[]);}
}
register_shutdown_function(function(){
    foreach($GLOBALS['sc_recovery_open_files']??[] as $entry){
        try{if(is_resource($entry['stream']??null))fflush($entry['stream']);sc_recovery_capture($entry['path'],'write');}catch(Throwable $e){error_log('CRM recovery: an open media write could not be archived.');}
        ScTrashStore::unlockPaths($entry['locks']);
    }
});

function sc_recovery_guard_sql(string $sql, string $prefix): string
{
    if (!sc_recovery_enabled()) return $sql;
    $head=preg_replace('~\A(?:\s+|/\*.*?\*/|--[^\r\n]*(?:\r?\n|$)|\#[^\r\n]*(?:\r?\n|$))*~s','',$sql);
    if (preg_match('/^TRUNCATE\s+(?:TABLE\s+)?((?:`[^`]+`|[a-zA-Z0-9_$]+)(?:\.(?:`[^`]+`|[a-zA-Z0-9_$]+))?)\s*;?\s*$/iD',$head,$match)) {
        // Preserve "clear all" workflows while making every removed row recoverable.
        // Keep auto-increment sequences monotonic instead of reusing old record IDs.
        return 'DELETE FROM '.$match[1];
    }
    if (preg_match('/^DROP\s+TEMPORARY\s+TABLE\b/i',$head)) return $sql;
    if (preg_match('/^(?:TRUNCATE\s|DROP\s+(?:TABLE|DATABASE)\b|ALTER\s+TABLE\s+.*\b(?:DROP|TRUNCATE)\b)/is',$head)) {
        throw new RuntimeException('This operation bypasses Trash and has been stopped. Use a verified backup and the documented maintenance procedure.');
    }
    if (preg_match('/^(?:DELETE|UPDATE|REPLACE)\b/i',$head) && stripos($head,$prefix.'sc_recovery_')!==false) {
        throw new RuntimeException('Recovery archives cannot be removed or overwritten through the CRM.');
    }
    return $sql;
}
function sc_recovery_after_schema_change(string $sql, mysqli $connection, string $prefix): void
{
    if (!sc_recovery_enabled()) return;
    if (!preg_match('/^\s*(?:CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?|ALTER\s+TABLE\s+)(`[^`]+`|[a-zA-Z0-9_$]+)/i',$sql,$match)) return;
    $table=trim($match[1],'`');
    if (strpos($table,$prefix)!==0 || strpos($table,$prefix.'sc_recovery_')===0) return;
    // Legacy modules issue CREATE IF NOT EXISTS on normal requests. An existing
    // registered table needs no DDL or whole-schema scan on that hot path.
    if (preg_match('/^\s*CREATE\s+TABLE\b/i', $sql)) {
        $registry = '`' . str_replace('`', '``', $prefix . 'sc_recovery_coverage') . '`';
        $statement = $connection->prepare("SELECT 1 FROM $registry WHERE table_name=? LIMIT 1");
        $statement->bind_param('s', $table);
        $statement->execute();
        $known = $statement->get_result()->fetch_row();
        $statement->close();
        if ($known) return;
    }
    // Repeated no-op ALTERs are also common in legacy initializers. Compare only
    // that table's columns and serialize the rare expensive coverage refresh.
    $cacheDir = sc_recovery_root() . '/schema-checks';
    if (!is_dir($cacheDir) && !mkdir($cacheDir, 0700, true) && !is_dir($cacheDir)) throw new RuntimeException('Recovery schema verification storage is unavailable.');
    $cache = $cacheDir . '/' . hash('sha256', $table);
    $lock = fopen($cache . '.lock', 'c');
    if (!$lock || !flock($lock, LOCK_EX)) throw new RuntimeException('Recovery schema verification is busy.');
    try {
        $escaped = $connection->real_escape_string($table);
        $columns = $connection->query("SELECT COLUMN_NAME,COLUMN_TYPE,IS_NULLABLE,EXTRA FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='$escaped' ORDER BY ORDINAL_POSITION")->fetch_all(MYSQLI_ASSOC);
        $hash = hash('sha256', json_encode($columns));
        if (is_file($cache) && file_get_contents($cache) === $hash) return;
        require_once __DIR__.'/../libraries/recovery/DatabaseArchive.php';
        (new ScDatabaseArchive($connection,$prefix))->install($table);
        if (file_put_contents($cache, $hash) === false) throw new RuntimeException('Recovery schema verification could not be saved.');
        chmod($cache, 0600);chmod($cache . '.lock', 0600);
    } finally {flock($lock, LOCK_UN);fclose($lock);}
}

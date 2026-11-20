<?php
/** Private immutable file recovery store. No application database is required. */
final class ScTrashStore
{
    private string $site;
    private string $root;
    private ?PDO $index = null;
    private static array $ownedLocks = [];

    public function __construct(string $site, string $root)
    {
        $this->site = rtrim(realpath($site) ?: $site, DIRECTORY_SEPARATOR);
        if ($root === '' || $root[0] !== '/' || strpos($root, "\0") !== false || strpos($root, '://') !== false) throw new RuntimeException('Recovery storage must use a private absolute path.');
        $tail = [];
        $ancestor = rtrim($root, DIRECTORY_SEPARATOR);
        while (!file_exists($ancestor) && $ancestor !== dirname($ancestor)) {array_unshift($tail, basename($ancestor));$ancestor=dirname($ancestor);}
        $resolved = realpath($ancestor);
        if ($resolved === false || is_link($ancestor) || in_array('..', $tail, true)) throw new RuntimeException('Invalid recovery storage path.');
        $this->root = rtrim($resolved . ($tail ? '/' . implode('/', $tail) : ''), DIRECTORY_SEPARATOR);
        if (is_link($root)) throw new RuntimeException('Recovery storage cannot be a symbolic link.');
        if ($this->root === $this->site || strpos($this->root . '/', $this->site . '/') === 0) {
            throw new RuntimeException('The recovery store must be outside the public website.');
        }
    }

    public function root(): string { return $this->root; }

    public function protectedPath(string $path): ?string
    {
        if ($path === '' || strpos($path, "\0") !== false || strpos($path, '://') !== false) return null;
        if ($path[0] !== '/') $path = getcwd() . '/' . $path;
        // Resolve the parent, not the final component: never follow a file symlink.
        $parent = realpath(dirname($path));
        if ($parent === false) return null;
        $absolute = $parent . '/' . basename($path);
        if (strpos($absolute, $this->site . '/') !== 0) return null;
        $relative = substr($absolute, strlen($this->site) + 1);
        return preg_match('~(?:^|/)(?:uploads|media|storage|attachments)(?:/|$)~', $relative) ? $absolute : null;
    }

    private function mkdir(string $dir): void
    {
        if (!is_dir($dir) && !@mkdir($dir, 0700, true) && !is_dir($dir)) {
            throw new RuntimeException('Recovery storage is unavailable. The original file was kept.');
        }
        if (is_link($dir)) throw new RuntimeException('Recovery storage cannot be a symbolic link.');
        @chmod($dir, 0700);
    }

    public function database(): PDO
    {
        if ($this->index) return $this->index;
        $this->mkdir($this->root);
        $file = $this->root . '/files.sqlite';
        if (is_link($file)) throw new RuntimeException('Invalid recovery index.');
        $db = new PDO('sqlite:' . $file, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $db->exec('PRAGMA busy_timeout=10000');
        $db->exec('PRAGMA journal_mode=WAL');
        $db->exec('PRAGMA synchronous=FULL');
        $db->exec('CREATE TABLE IF NOT EXISTS files (id INTEGER PRIMARY KEY AUTOINCREMENT, path TEXT NOT NULL, name TEXT NOT NULL, sha256 TEXT NOT NULL, bytes INTEGER NOT NULL, event TEXT NOT NULL, batch TEXT NOT NULL, actor TEXT NOT NULL, created_at TEXT NOT NULL, restored_at TEXT, UNIQUE(path,sha256,event,batch))');
        $db->exec('CREATE INDEX IF NOT EXISTS files_event_id ON files(event,id)');
        $db->exec('CREATE INDEX IF NOT EXISTS files_path_hash ON files(path,sha256)');
        @chmod($file, 0600);
        $this->index = $db;
        return $db;
    }

    public function capture(string $path, string $event, string $batch, string $actor = '', ?string $destination = null): ?int
    {
        $absolute = $this->protectedPath($destination ?? $path);
        if ($absolute === null || !file_exists($path)) return null;
        if (is_link($path)) throw new RuntimeException('A linked media file cannot be deleted until it is backed up separately.');
        if (is_dir($path)) {
            $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS));
            foreach ($it as $file) {
                if ($file->isLink()) throw new RuntimeException('A linked media file cannot be deleted until it is backed up separately.');
                if ($file->isFile()) $this->capture($file->getPathname(), $event, $batch, $actor);
            }
            return null;
        }
        if (!is_file($path) || !is_readable($path)) throw new RuntimeException('The original file could not be safely archived. Deletion was stopped.');
        $hash = hash_file('sha256', $path);
        if ($hash === false) throw new RuntimeException('Could not verify the recovery copy.');
        $this->mkdir($this->root);
        $this->mkdir($this->root . '/objects');
        $dir = $this->root . '/objects/' . substr($hash, 0, 2);
        $this->mkdir($dir);
        $object = $dir . '/' . $hash;
        if (!is_file($object)) {
            $temp = tempnam($dir, '.copy-');
            if ($temp === false) throw new RuntimeException('Recovery copy could not be created.');
            try {
                if (!copy($path, $temp) || hash_file('sha256', $temp) !== $hash || hash_file('sha256', $path) !== $hash) {
                    throw new RuntimeException('The recovery copy did not verify. The original file was kept.');
                }
                chmod($temp, 0600);
                if (!rename($temp, $object)) throw new RuntimeException('The recovery copy could not be committed.');
            } finally { if (is_file($temp)) unlink($temp); }
        } elseif (is_link($object) || hash_file('sha256', $object) !== $hash) {
            throw new RuntimeException('Recovery storage failed its integrity check. Deletion was stopped.');
        }
        $relative = substr($absolute, strlen($this->site) + 1);
        $db = $this->database();
        $stmt = $db->prepare('INSERT OR IGNORE INTO files(path,name,sha256,bytes,event,batch,actor,created_at) VALUES(?,?,?,?,?,?,?,?)');
        $stmt->execute([$relative, basename($relative), $hash, filesize($object), $event, $batch, $actor, gmdate('Y-m-d H:i:s')]);
        return (int)$db->lastInsertId();
    }

    public function lockPaths(array $paths): array
    {
        $keys=[];
        foreach($paths as $path){if(is_string($path)&&($protected=$this->protectedPath($path))!==null)$keys[hash('sha256',$protected)]=true;}
        if(!$keys)return [];
        $this->mkdir($this->root);$this->mkdir($this->root.'/locks');$keys=array_keys($keys);sort($keys);$locks=[];
        try{foreach($keys as $key){$file=$this->root.'/locks/'.$key;if(is_link($file))throw new RuntimeException('Invalid recovery lock.');if(isset(self::$ownedLocks[$file])){self::$ownedLocks[$file]['count']++;$locks[]=$file;continue;}$handle=fopen($file,'c');if(!$handle||!flock($handle,LOCK_EX))throw new RuntimeException('The file is busy. Try again.');chmod($file,0600);self::$ownedLocks[$file]=['handle'=>$handle,'count'=>1];$locks[]=$file;}return $locks;}
        catch(Throwable $e){self::unlockPaths($locks);throw $e;}
    }
    public static function unlockPaths(array $locks): void
    {
        foreach(array_reverse($locks) as $file){if(!isset(self::$ownedLocks[$file]))continue;if(--self::$ownedLocks[$file]['count']>0)continue;$handle=self::$ownedLocks[$file]['handle'];if(is_resource($handle)){flock($handle,LOCK_UN);fclose($handle);}unset(self::$ownedLocks[$file]);}
    }

    public function entries(int $page = 1, bool $all = false, string $search = ''): array
    {
        $where = $all ? '1=1' : "event NOT IN ('baseline','upload','write','incoming')";
        $parameters = [];
        if ($search !== '') { $where .= ' AND (path LIKE ? OR name LIKE ?)'; $parameters = ['%' . $search . '%', '%' . $search . '%']; }
        $stmt = $this->database()->prepare('SELECT * FROM files WHERE ' . $where . ' ORDER BY id DESC LIMIT 50 OFFSET ' . max(0, ($page - 1) * 50));
        $stmt->execute($parameters);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function entry(int $id): array
    {
        $stmt = $this->database()->prepare('SELECT * FROM files WHERE id=?');$stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) throw new RuntimeException('Recovery entry not found.');
        return $row;
    }

    public function object(array $entry): string
    {
        if (!preg_match('/^[a-f0-9]{64}$/D', $entry['sha256'])) throw new RuntimeException('Invalid recovery object.');
        $file = $this->root . '/objects/' . substr($entry['sha256'], 0, 2) . '/' . $entry['sha256'];
        if (is_link($file) || !is_file($file) || hash_file('sha256', $file) !== $entry['sha256']) throw new RuntimeException('Recovery integrity check failed.');
        return $file;
    }

    public function restore(int $id): string
    {
        $row = $this->entry($id);
        if ($row['event'] === 'incoming') throw new RuntimeException('Download this upload and attach it through its original CRM form.');
        $relative = $row['path'];
        if (preg_match('~(?:^|/)\.\.(?:/|$)|^[\\/]|\x00~', $relative)) throw new RuntimeException('Invalid restore path.');
        $target = $this->site . '/' . $relative;
        $parent = dirname($target);
        // Validate every existing ancestor before creating a missing directory.
        $ancestor = $parent;
        while (!file_exists($ancestor) && $ancestor !== dirname($ancestor)) $ancestor = dirname($ancestor);
        $resolved = realpath($ancestor);
        if ($resolved === false || strpos($resolved . '/', $this->site . '/') !== 0 || is_link($ancestor)) throw new RuntimeException('Restore destination is outside the website.');
        if (!is_dir($parent) && !mkdir($parent, 0755, true) && !is_dir($parent)) throw new RuntimeException('Restore directory could not be created.');
        if ($this->protectedPath($target) !== $target || file_exists($target) || is_link($target)) throw new RuntimeException('The current file was kept. Restore only to an unused original path.');
        $source = $this->object($row);
        // Exclusive creation prevents replacing a file written by another request.
        $out = @fopen($target, 'xb');
        if (!$out) throw new RuntimeException('The current file was kept. Restore destination is occupied.');
        $in = fopen($source, 'rb');
        try {
            if (!$in || stream_copy_to_stream($in, $out) !== (int)$row['bytes']) throw new RuntimeException('Restoring the file failed. The archived copy is still available.');
        } finally { if (is_resource($in)) fclose($in); fclose($out); }
        if (hash_file('sha256', $target) !== $row['sha256']) throw new RuntimeException('Restored file failed verification. The archived copy is still available.');
        chmod($target, 0644);
        $this->database()->prepare('UPDATE files SET restored_at=? WHERE id=?')->execute([gmdate('Y-m-d H:i:s'), $id]);
        return $relative;
    }
}

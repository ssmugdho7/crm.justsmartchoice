<?php

defined('BASEPATH') or exit('No direct script access allowed');

/** First-party public website analytics. Raw IPs never go to Google. */
class Visitor_store
{
    private $db;
    private $prefix;
    private $secret;

    public function __construct(PDO $db, string $prefix, string $secret)
    {
        if (!preg_match('/^[a-zA-Z0-9_]+$/D', $prefix) || strlen($secret) < 16) {
            throw new InvalidArgumentException('Invalid visitor store configuration.');
        }
        $this->db = $db;
        $this->prefix = $prefix;
        $this->secret = $secret;
    }

    public static function connect(): PDO
    {
        $db = new PDO('mysql:host=' . APP_DB_HOSTNAME . ';dbname=' . APP_DB_NAME . ';charset=utf8mb4', APP_DB_USERNAME, APP_DB_PASSWORD, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT => 3,
        ]);
        $db->exec("SET time_zone = '+00:00'");
        return $db;
    }

    public static function publicPath($value): ?string
    {
        if (!is_string($value) || strlen($value) > 500) return null;
        $path = parse_url($value, PHP_URL_PATH);
        if (!is_string($path) || !preg_match('~^/(?:[a-zA-Z0-9_/-]+(?:\.php|\.html)?)?/?$~D', $path)) return null;
        if (preg_match('~(?:^|/)(?:admin|api|login|logout|account|authentication|customer|clients?|contracts?|payments?|invoices?|uploads?|mail|tools)(?:[/.]|$)~i', $path)) return null;
        // Sharing tokens, emails, long record identifiers, and form values are not page names.
        if (preg_match('~(?:[a-f0-9]{24,}|[0-9]{7,})~i', $path)) return null;
        return $path;
    }

    public static function originAllowed(array $server): bool
    {
        $origin = $server['HTTP_ORIGIN'] ?? '';
        return in_array($origin, ['https://justsmartchoice.com', 'https://www.justsmartchoice.com'], true);
    }

    private function query(string $sql, array $values = []): PDOStatement
    {
        $q = $this->db->prepare($sql);
        $q->execute($values);
        return $q;
    }

    public function signedCookie(string $id, int $expires): string
    {
        $value = $id . '.' . $expires;
        return $value . '.' . hash_hmac('sha256', $value, $this->secret);
    }

    public function cookieId($value, int $now): ?string
    {
        if (!is_string($value) || !preg_match('/^([a-f0-9]{32})\.([0-9]{10})\.([a-f0-9]{64})$/D', $value, $m)) return null;
        if ((int) $m[2] <= $now || !hash_equals($this->signedCookie($m[1], (int) $m[2]), $value)) return null;
        return $m[1];
    }

    /** Returns cookies only. Callers must never return visitor records publicly. */
    public function capture(array $data, array $server, array $cookies, ?int $clockMs = null): array
    {
        $nowMs = $clockMs ?? (int) floor(microtime(true) * 1000);
        $now = (int) floor($nowMs / 1000);
        $path = self::publicPath($data['path'] ?? null);
        $page = $data['page'] ?? '';
        $kind = $data['kind'] ?? '';
        $total = $data['active_ms'] ?? null;
        // Only the server-observed peer address is used. Forwarding/client IP headers are untrusted.
        $ip = $server['REMOTE_ADDR'] ?? '';
        $ua = $server['HTTP_USER_AGENT'] ?? '';
        $referer = parse_url($server['HTTP_REFERER'] ?? '');
        if (!self::originAllowed($server) || !in_array($referer['host'] ?? '', ['justsmartchoice.com', 'www.justsmartchoice.com'], true)
            || ($data['consent'] ?? false) !== true || !$path || !is_string($page) || !preg_match('/^[a-f0-9]{32}$/D', $page)
            || !in_array($kind, ['page', 'heartbeat', 'leave'], true) || !is_int($total) || $total < 0 || $total > 86400000
            || !filter_var($ip, FILTER_VALIDATE_IP) || !$ua || preg_match('/bot|crawler|spider|headless|preview|curl|wget|python|monitor/i', $ua)) {
            throw new InvalidArgumentException('Invalid visitor event.');
        }
        $ip = inet_ntop(inet_pton($ip));
        $p = $this->prefix;
        $stamp = gmdate('Y-m-d H:i:s', $now);
        $bucket = gmdate('Y-m-d H:i:00', $now);
        $ipKey = hash_hmac('sha256', $ip, $this->secret);
        $this->query("INSERT INTO {$p}ga_visitor_limits (ip_key, minute, hits) VALUES (?, ?, 1) ON DUPLICATE KEY UPDATE hits=hits+1", [$ipKey, $bucket]);
        $hits = $this->query("SELECT hits FROM {$p}ga_visitor_limits WHERE ip_key=? AND minute=?", [$ipKey, $bucket])->fetchColumn();
        if ($hits > 120) throw new OverflowException('Visitor event rate exceeded.');

        $visitor = $this->cookieId($cookies['_sc_visitor'] ?? null, $now) ?? bin2hex(random_bytes(16));
        $visitorHash = hash_hmac('sha256', $visitor, $this->secret);
        $session = $this->cookieId($cookies['_sc_visit'] ?? null, $now);
        $newCookies = [];
        $this->db->beginTransaction();
        try {
            $row = $session ? $this->query("SELECT * FROM {$p}ga_visitor_sessions WHERE id=? AND visitor_hash=? FOR UPDATE", [$session, $visitorHash])->fetch() : false;
            if (!$row || strtotime($row['last_seen'] . ' UTC') < $now - 1800) {
                if ($kind !== 'page') { $this->db->rollBack(); return []; }
                $session = bin2hex(random_bytes(16));
                $browser = preg_match('/Edg\//', $ua) ? 'Edge' : (preg_match('/Firefox\//', $ua) ? 'Firefox' : (preg_match('/Chrome\//', $ua) ? 'Chrome' : (preg_match('/Safari\//', $ua) ? 'Safari' : 'Other')));
                $device = preg_match('/iPad|Tablet/i', $ua) ? 'Tablet' : (preg_match('/Mobile|Android/i', $ua) ? 'Mobile' : 'Desktop');
                $this->query("INSERT INTO {$p}ga_visitor_sessions (id,visitor_hash,ip_address,browser,device,first_seen,last_seen,current_path,active_ms,last_credit_ms) VALUES (?,?,?,?,?,?,?,?,0,?)", [$session,$visitorHash,$ip,$browser,$device,$stamp,$stamp,$path,$nowMs]);
                $row = ['last_credit_ms' => $nowMs];
            }
            $existing = $this->query("SELECT * FROM {$p}ga_visitor_pages WHERE session_id=? AND page_token=? FOR UPDATE", [$session,$page])->fetch();
            if (!$existing && $kind === 'page') {
                $this->query("INSERT INTO {$p}ga_visitor_pages (session_id,page_token,path,first_seen,last_seen,active_ms,client_active_ms,is_open) VALUES (?,?,?,?,?,0,?,1)", [$session,$page,$path,$stamp,$stamp,$total]);
                $existing = ['client_active_ms'=>$total,'last_seen'=>$stamp,'path'=>$path,'is_open'=>1];
            }
            if (!$existing || $existing['path'] !== $path) { $this->db->rollBack(); return []; }
            $delta = min(max(0, $total - (int)$existing['client_active_ms']), max(0, $now - strtotime($existing['last_seen'] . ' UTC') + 1) * 1000, 30000);
            $credit = min($delta, max(0, $nowMs - (int)$row['last_credit_ms']));
            $open = $kind === 'leave' ? 0 : (int)$existing['is_open'];
            $this->query("UPDATE {$p}ga_visitor_pages SET active_ms=active_ms+?,client_active_ms=GREATEST(client_active_ms,?),last_seen=?,is_open=? WHERE session_id=? AND page_token=?", [$credit,$total,$stamp,$open,$session,$page]);
            $this->query("UPDATE {$p}ga_visitor_sessions SET active_ms=active_ms+?,last_credit_ms=GREATEST(last_credit_ms,?),last_seen=?,current_path=?,ip_address=? WHERE id=?", [$credit,$nowMs,$stamp,$path,$ip,$session]);
            $this->db->commit();
            $newCookies['_sc_visitor'] = $this->signedCookie($visitor, $now + 30 * 86400);
            $newCookies['_sc_visit'] = $this->signedCookie($session, $now + 1800);
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
        return $newCookies;
    }

    public function purge(): void
    {
        $p = $this->prefix;
        $this->query("DELETE FROM {$p}ga_visitor_sessions WHERE last_seen < ? LIMIT 1000", [gmdate('Y-m-d H:i:s', time()-30*86400)]);
        $this->query("DELETE FROM {$p}ga_visitor_limits WHERE minute < ? LIMIT 2000", [gmdate('Y-m-d H:i:s', time()-600)]);
    }

    public function enabled(): bool
    {
        $p=$this->prefix;
        return $this->query("SELECT value FROM {$p}options WHERE name=?",['ga_visitors_enabled'])->fetchColumn()==='1'
            && (int)$this->query("SELECT active FROM {$p}modules WHERE module_name=?",['google_analytics'])->fetchColumn()===1;
    }

    public function report(string $date, string $timezone, int $before = 0, string $search = ''): array
    {
        $zone = new DateTimeZone($timezone);
        $start = new DateTimeImmutable($date, $zone);
        $end = $start->modify('+1 day');
        $from = $start->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');
        $to = $end->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');
        $p = $this->prefix;
        $summary = $this->query("SELECT COUNT(DISTINCT s.visitor_hash) visitors, COUNT(DISTINCT s.id) sessions, COUNT(*) views, COALESCE(SUM(v.active_ms),0) active_ms FROM {$p}ga_visitor_pages v JOIN {$p}ga_visitor_sessions s ON s.id=v.session_id WHERE v.first_seen>=? AND v.first_seen<?", [$from,$to])->fetch();
        $live = $this->query("SELECT COUNT(DISTINCT s.visitor_hash) FROM {$p}ga_visitor_sessions s WHERE s.last_seen>=? AND EXISTS (SELECT 1 FROM {$p}ga_visitor_pages v WHERE v.session_id=s.id AND v.is_open=1 AND v.last_seen>=?)", [gmdate('Y-m-d H:i:s',time()-45),gmdate('Y-m-d H:i:s',time()-45)])->fetchColumn();
        // Stable keyset pagination, latest session first. No private data appears in links.
        $where = "EXISTS (SELECT 1 FROM {$p}ga_visitor_pages daypage WHERE daypage.session_id=s.id AND daypage.first_seen>=? AND daypage.first_seen<?)";
        $values = [$from,$to];
        if ($before > 0) { $where .= ' AND s.sequence<?'; $values[] = $before; }
        if ($search !== '') { $where .= ' AND s.ip_address=?'; $values[] = $search; }
        $sessions = $this->query("SELECT s.sequence,s.id,s.ip_address,s.browser,s.device,s.first_seen,s.last_seen,s.active_ms,s.current_path,(SELECT COUNT(*) FROM {$p}ga_visitor_pages v WHERE v.session_id=s.id) pages, EXISTS(SELECT 1 FROM {$p}ga_visitor_pages v WHERE v.session_id=s.id AND v.is_open=1 AND v.last_seen>=?) live FROM {$p}ga_visitor_sessions s WHERE {$where} ORDER BY s.sequence DESC LIMIT 51", array_merge([gmdate('Y-m-d H:i:s',time()-45)],$values))->fetchAll();
        $more = count($sessions)>50;
        $sessions = array_slice($sessions,0,50);
        $daily = [];
        for ($i=13;$i>=0;$i--) {
            $day=$start->modify('-'.$i.' days');
            if ($day < new DateTimeImmutable('today -29 days',$zone)) continue;
            $a=$day->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');
            $b=$day->modify('+1 day')->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');
            $counts=$this->query("SELECT COUNT(DISTINCT s.visitor_hash) visitors,COUNT(*) views FROM {$p}ga_visitor_pages v JOIN {$p}ga_visitor_sessions s ON s.id=v.session_id WHERE v.first_seen>=? AND v.first_seen<?",[$a,$b])->fetch();
            $daily[]=['date'=>$day->format('Y-m-d')]+$counts;
        }
        return ['summary'=>$summary,'live'=>(int)$live,'sessions'=>$sessions,'daily'=>$daily,'next'=>$more?(int)end($sessions)['sequence']:0];
    }

    public function session(string $id): array
    {
        $p=$this->prefix;
        $row=$this->query("SELECT id,ip_address,browser,device,first_seen,last_seen,active_ms FROM {$p}ga_visitor_sessions WHERE id=? AND last_seen>=?",[$id,gmdate('Y-m-d H:i:s',time()-30*86400)])->fetch();
        if (!$row) return [];
        $pages=$this->query("SELECT id,path,first_seen,last_seen,active_ms FROM {$p}ga_visitor_pages WHERE session_id=? ORDER BY id LIMIT 501",[$id])->fetchAll();
        return ['session'=>$row,'pages'=>array_slice($pages,0,500),'truncated'=>count($pages)>500];
    }
}

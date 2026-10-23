<?php

defined('BASEPATH') or exit('No direct script access allowed');

/** Read bounded tails, keeping complete error records and their stack traces together. */
class Debug_mode_log_reader
{
    public static function redact($text)
    {
        $text = preg_replace('~(https?://[^\s?<>"\']+)\?[^\s<>"\']*~i', '$1?[redacted]', (string) $text);
        $text = preg_replace('~\b(Bearer\s+)[A-Za-z0-9._\~+/-]+=*~i', '$1[redacted]', $text);
        $text = preg_replace('~\b(password|passwd|secret|token|api[_-]?key|authorization|cookie)\b([\s"\']*[:=][\s"\']*)[^\s,;"\']+~i', '$1$2[redacted]', $text);
        return preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $text);
    }

    public static function read(array $sources, $limit = 500, $cutoff = null, $maxBytes = 1048576)
    {
        $rows = [];
        $cutoff = $cutoff ?? strtotime('-7 days');
        foreach ($sources as $source) {
            $file = $source['path'];
            $handle = @fopen($file, 'rb');
            if (!$handle) { continue; }
            try {
                $size = fstat($handle)['size'];
                if ($size > $maxBytes) {
                    fseek($handle, -$maxBytes, SEEK_END);
                    fgets($handle); // Discard a partial record header at the byte boundary.
                }
                $tail = stream_get_contents($handle, $maxBytes);
            } finally {
                fclose($handle);
            }
            $records = [];
            foreach (preg_split('/\r?\n/', $tail ?: '') as $line) {
                if (preg_match('/^(ERROR|DEBUG|INFO) - (\d{4}-\d\d-\d\d \d\d:\d\d:\d\d) --> (.*)$/', $line, $m)) {
                    $records[] = ['time' => strtotime($m[2]), 'text' => $line, 'error' => $m[1] === 'ERROR'];
                } elseif (preg_match('/^\[([^\]]+)\]\s*(PHP .*)$/', $line, $m)) {
                    $records[] = ['time' => strtotime($m[1]), 'text' => $line, 'error' => true];
                } elseif ($line !== '' && $records) {
                    $last = count($records) - 1;
                    // Limit a malformed or very large individual event as well as the file tail.
                    if (strlen($records[$last]['text']) < 16384) { $records[$last]['text'] .= "\n" . $line; }
                }
            }
            foreach ($records as $sequence => $record) {
                if (!$record['error'] || $record['time'] === false || $record['time'] < $cutoff) { continue; }
                $text = self::redact($record['text']);
                $header = strtok($text, "\n");
                $severity = preg_match('/fatal|parseerror|exception|parse error/i', $header) ? 'error'
                    : (preg_match('/warning/i', $header) ? 'warning' : (preg_match('/notice|deprecated|language line/i', $header) ? 'notice' : 'error'));
                $module = 'core';
                if (preg_match('~(?:/modules/|\[module=)([a-zA-Z0-9_-]+)~', $text, $m)) { $module = $m[1]; }
                $route = '';
                if (preg_match('~\[route=(/[^\]]*)\]~', $text, $m)) { $route = $m[1]; }
                if (strpos($text, '[browser]') !== false) { $severity = 'browser'; }
                $rows[] = ['file' => $source['label'], 'line' => $text, 'time' => $record['time'], 'sequence' => $sequence,
                    'source' => $source['type'], 'module' => $module, 'route' => $route, 'severity' => $severity];
            }
        }
        usort($rows, static function ($a, $b) {
            return ($b['time'] <=> $a['time']) ?: strcmp($a['file'], $b['file']) ?: ($b['sequence'] <=> $a['sequence']);
        });
        return array_slice($rows, 0, max(1, min(2000, (int) $limit)));
    }
}

<?php

defined('BASEPATH') or exit('No direct script access allowed');

/** Prepare redacted, module-scoped diagnostics for the administrator debug drawer. */
class Debug_mode_console_formatter
{
    public static function format(array $rows, $module, $root)
    {
        $module = is_string($module) ? $module : '';
        $displayRoot = rtrim((string) $root, '/\\');
        $root = realpath($displayRoot) ?: $displayRoot;
        $events = [];

        foreach ($rows as $row) {
            $routeMatches = isset($row['route']) && preg_match('~^/admin/' . preg_quote($module, '~') . '(?:/|$)~', $row['route']);
            if (($row['module'] ?? '') !== $module && !$routeMatches) { continue; }

            $rawText = (string) ($row['line'] ?? '');
            $location = self::findLocation($rawText, $module, $root);
            $text = self::cleanText($rawText, [$displayRoot, $root]);
            $lines = preg_split('/\r?\n/', $text) ?: [];
            $summary = array_shift($lines) ?: 'Unknown diagnostic event';
            $summary = preg_replace('/^(?:ERROR|DEBUG|INFO) - \d{4}-\d\d-\d\d \d\d:\d\d:\d\d -->\s*/', '', $summary);
            $trace = [];
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '') { continue; }
                $trace[] = substr($line, 0, 700);
                if (count($trace) >= 10) { break; }
            }

            $events[] = [
                'id' => substr(hash('sha256', ($row['file'] ?? '') . '|' . ($row['time'] ?? 0) . '|' . $text), 0, 16),
                'severity' => in_array(($row['severity'] ?? ''), ['error', 'warning', 'notice', 'browser'], true) ? $row['severity'] : 'error',
                'summary' => substr(trim($summary), 0, 1500),
                'trace' => $trace,
                'source' => substr((string) ($row['file'] ?? 'CRM log'), 0, 100),
                'occurred_at' => date('Y-m-d H:i:s', (int) ($row['time'] ?? time())),
                'location' => $location,
            ];
        }

        return array_slice($events, 0, 50);
    }

    private static function cleanText($text, array $roots)
    {
        foreach (array_unique($roots) as $root) {
            if ($root !== '') { $text = str_replace([$root . '/', $root . '\\'], '[app]/', $text); }
        }
        return Debug_mode_log_reader::redact($text);
    }

    private static function findLocation($text, $module, $root)
    {
        if ($root === '' || !preg_match('~((?:[A-Za-z]:)?[/\\\\][^\r\n]*?\.php)(?:\s+on\s+line|\s+)(\d+)~i', $text, $match)) {
            return null;
        }

        $candidate = realpath($match[1]);
        $moduleRoot = realpath($root . '/modules/' . $module);
        if (!$candidate || !$moduleRoot || pathinfo($candidate, PATHINFO_EXTENSION) !== 'php'
            || strpos($candidate, $moduleRoot . DIRECTORY_SEPARATOR) !== 0 || !is_readable($candidate)
            || filesize($candidate) > 2097152) {
            return null;
        }

        $line = max(1, (int) $match[2]);
        $source = @file($candidate, FILE_IGNORE_NEW_LINES);
        if (!is_array($source)) { return null; }
        $start = max(1, $line - 3);
        $end = min(count($source), $line + 3);
        $excerpt = [];
        for ($number = $start; $number <= $end; $number++) {
            $excerpt[] = [
                'number' => $number,
                'text' => substr(Debug_mode_log_reader::redact($source[$number - 1]), 0, 1000),
                'current' => $number === $line,
            ];
        }

        return [
            'file' => str_replace(DIRECTORY_SEPARATOR, '/', substr($candidate, strlen($root) + 1)),
            'line' => $line,
            'excerpt' => $excerpt,
        ];
    }
}

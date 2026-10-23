<?php

defined('BASEPATH') or exit('No direct script access allowed');

/** Keep page diagnostics open across visits until an administrator verifies the code fix. */
class Debug_mode_issue_tracker
{
    const MAX_ISSUES = 500;

    public static function collapse(array $events, $scope, $route)
    {
        $collapsed = [];
        foreach ($events as $event) {
            $fingerprint = self::fingerprint($event, $scope, $route);
            $seenAt = max(0, (int) ($event['occurred_at_unix'] ?? 0));
            if (!isset($collapsed[$fingerprint])) {
                $event['fingerprint'] = $fingerprint;
                $event['first_seen_unix'] = $seenAt;
                $event['last_seen_unix'] = $seenAt;
                $event['occurrences'] = 1;
                $collapsed[$fingerprint] = $event;
                continue;
            }
            $collapsed[$fingerprint]['first_seen_unix'] = min($collapsed[$fingerprint]['first_seen_unix'], $seenAt);
            $collapsed[$fingerprint]['last_seen_unix'] = max($collapsed[$fingerprint]['last_seen_unix'], $seenAt);
            $collapsed[$fingerprint]['occurrences']++;
        }
        return array_values($collapsed);
    }

    public static function decode($json)
    {
        $state = is_string($json) ? json_decode($json, true) : null;
        if (!is_array($state)) { return []; }
        $clean = [];
        foreach ($state as $fingerprint => $issue) {
            if (!is_string($fingerprint) || !preg_match('/^[a-f0-9]{64}$/D', $fingerprint) || !is_array($issue)) { continue; }
            if (!isset($issue['scope'], $issue['route'], $issue['summary']) || !is_string($issue['scope'])
                || !is_string($issue['route']) || !is_string($issue['summary'])) { continue; }
            $issue['fingerprint'] = $fingerprint;
            $issue['status'] = ($issue['status'] ?? '') === 'resolved' ? 'resolved' : 'open';
            $issue['resolved_at'] = max(0, (int) ($issue['resolved_at'] ?? 0));
            $issue['first_seen_unix'] = max(0, (int) ($issue['first_seen_unix'] ?? 0));
            $issue['last_seen_unix'] = max(0, (int) ($issue['last_seen_unix'] ?? 0));
            $issue['occurrences'] = max(1, (int) ($issue['occurrences'] ?? 1));
            $clean[$fingerprint] = $issue;
        }
        return $clean;
    }

    public static function synchronize(array $state, array $recent, $scope, $route)
    {
        foreach ($recent as $event) {
            $fingerprint = $event['fingerprint'];
            $stored = $state[$fingerprint] ?? null;
            $issue = self::storable($event, $scope, $route);
            if (is_array($stored)) {
                $issue['first_seen_unix'] = min((int) $stored['first_seen_unix'], (int) $issue['first_seen_unix']);
                $issue['occurrences'] = max((int) $stored['occurrences'], (int) $issue['occurrences']);
                $issue['status'] = $stored['status'];
                $issue['resolved_at'] = (int) $stored['resolved_at'];
                if ($issue['status'] === 'resolved' && $issue['last_seen_unix'] > $issue['resolved_at']) {
                    $issue['status'] = 'open';
                    $issue['resolved_at'] = 0;
                }
            }
            $state[$fingerprint] = $issue;
        }
        return self::bounded($state);
    }

    public static function openForRoute(array $state, $scope, $route)
    {
        $issues = array_values(array_filter($state, static function ($issue) use ($scope, $route) {
            return ($issue['status'] ?? '') === 'open' && ($issue['scope'] ?? '') === $scope && ($issue['route'] ?? '') === $route;
        }));
        usort($issues, static function ($a, $b) { return ($b['last_seen_unix'] ?? 0) <=> ($a['last_seen_unix'] ?? 0); });
        return $issues;
    }

    public static function resolve(array $state, $fingerprint, $scope, $route, $resolvedAt = null)
    {
        if (!isset($state[$fingerprint]) || ($state[$fingerprint]['scope'] ?? '') !== $scope
            || ($state[$fingerprint]['route'] ?? '') !== $route) { return false; }
        $state[$fingerprint]['status'] = 'resolved';
        $state[$fingerprint]['resolved_at'] = $resolvedAt === null ? time() : max(1, (int) $resolvedAt);
        return $state;
    }

    private static function fingerprint(array $event, $scope, $route)
    {
        $summary = preg_replace('/\s*\[request=[^\]]+\]/i', '', (string) ($event['summary'] ?? ''));
        $location = $event['location'] ?? null;
        $where = is_array($location) ? (($location['file'] ?? '') . ':' . ($location['line'] ?? 0)) : '';
        return hash('sha256', implode('|', [$scope, $route, $event['severity'] ?? '', trim($summary), $event['source'] ?? '', $where]));
    }

    private static function storable(array $event, $scope, $route)
    {
        $location = $event['location'] ?? null;
        if (is_array($location)) {
            $excerpt = [];
            foreach (array_slice((array) ($location['excerpt'] ?? []), 0, 7) as $line) {
                if (!is_array($line)) { continue; }
                $excerpt[] = [
                    'number' => max(0, (int) ($line['number'] ?? 0)),
                    'text' => substr((string) ($line['text'] ?? ''), 0, 500),
                    'current' => !empty($line['current']),
                ];
            }
            $location = [
                'file' => substr((string) ($location['file'] ?? ''), 0, 500),
                'line' => max(0, (int) ($location['line'] ?? 0)),
                'excerpt' => $excerpt,
            ];
        } else {
            $location = null;
        }
        return [
            'fingerprint' => $event['fingerprint'],
            'scope' => substr((string) $scope, 0, 100),
            'route' => substr((string) $route, 0, 240),
            'status' => 'open',
            'resolved_at' => 0,
            'severity' => substr((string) ($event['severity'] ?? 'error'), 0, 20),
            'summary' => substr((string) ($event['summary'] ?? 'Unknown diagnostic event'), 0, 1500),
            'trace' => array_slice(array_map(static function ($line) { return substr((string) $line, 0, 500); }, (array) ($event['trace'] ?? [])), 0, 5),
            'source' => substr((string) ($event['source'] ?? 'CRM log'), 0, 100),
            'first_seen_unix' => max(0, (int) ($event['first_seen_unix'] ?? 0)),
            'last_seen_unix' => max(0, (int) ($event['last_seen_unix'] ?? 0)),
            'occurrences' => max(1, (int) ($event['occurrences'] ?? 1)),
            'location' => $location,
        ];
    }

    private static function bounded(array $state)
    {
        if (count($state) <= self::MAX_ISSUES) { return $state; }
        uasort($state, static function ($a, $b) {
            $aOpen = ($a['status'] ?? '') === 'open';
            $bOpen = ($b['status'] ?? '') === 'open';
            if ($aOpen !== $bOpen) { return $aOpen ? -1 : 1; }
            return ($b['last_seen_unix'] ?? 0) <=> ($a['last_seen_unix'] ?? 0);
        });
        return array_slice($state, 0, self::MAX_ISSUES, true);
    }
}

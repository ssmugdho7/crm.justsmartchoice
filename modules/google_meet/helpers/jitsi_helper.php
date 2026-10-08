<?php

defined('BASEPATH') or exit('No direct script access allowed');

function jitsi_server_domain($value = null)
{
    $value = trim((string)($value === null ? (get_option('jitsi_server_domain') ?: 'meet.jit.si') : $value));
    $value = preg_replace('~^https?://~i', '', $value);
    $value = rtrim($value, '/');
    if (!preg_match('~^(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z]{2,63}(?::[0-9]{1,5})?$~i', $value)) {
        throw new InvalidArgumentException('Enter a Jitsi server hostname without a path, credentials or query string.');
    }
    $port = parse_url('https://' . $value, PHP_URL_PORT);
    if ($port !== null && ($port < 1 || $port > 65535)) {
        throw new InvalidArgumentException('Enter a valid HTTPS port for the Jitsi server.');
    }
    return strtolower($value);
}

function jitsi_generate_room_name($prefix = null)
{
    $prefix = $prefix === null ? (get_option('jitsi_room_prefix') ?: 'SC') : $prefix;
    $prefix = substr(preg_replace('/[^A-Za-z0-9_-]/', '', (string)$prefix), 0, 24) ?: 'SC';
    // 16 random bytes = 128 bits. The date and prefix are labels, not entropy.
    $random = bin2hex(random_bytes(16));
    return $prefix . '-' . gmdate('Ym') . '-' . substr($random, 0, 16) . '-' . substr($random, 16);
}

function jitsi_generate_room_pin()
{
    return (string)random_int(100000, 999999);
}

function jitsi_build_room_url($roomName)
{
    if (!preg_match('/^[A-Za-z0-9_-]{10,191}$/D', (string)$roomName)) {
        throw new InvalidArgumentException('Invalid Jitsi room name.');
    }
    return 'https://' . jitsi_server_domain() . '/' . rawurlencode($roomName);
}

/** Infer a room only from an approved domain or previously stored Jitsi identity. */
function jitsi_meeting_room($meeting)
{
    $meeting = (object)$meeting;
    $parts = parse_url(trim((string)($meeting->meet_link ?? '')));
    if (!$parts || ($parts['scheme'] ?? '') !== 'https' || empty($parts['host']) || isset($parts['user']) || isset($parts['pass'])) {
        return null;
    }
    $domain = strtolower($parts['host']) . (isset($parts['port']) ? ':' . $parts['port'] : '');
    try { jitsi_server_domain($domain); } catch (InvalidArgumentException $e) { return null; }
    $room = rawurldecode(ltrim($parts['path'] ?? '', '/'));
    if (!preg_match('/^[A-Za-z0-9_-]{10,191}$/D', $room)) { return null; }
    $stored = ($meeting->provider ?? '') === 'jitsi' && ($meeting->room_name ?? '') === $room;
    try { $configuredDomain = jitsi_server_domain(); } catch (InvalidArgumentException $e) { $configuredDomain = null; }
    if (!$stored && !in_array($domain, [$configuredDomain, 'meet.jit.si'], true)) { return null; }
    return ['domain' => $domain, 'room_name' => $room, 'room_pin' => (string)($meeting->room_pin ?? '')];
}

/** New automatic rooms never replace a valid previously stored link. */
function jitsi_prepare_meeting_link($link, $existing = null)
{
    $existing = $existing ? (object)$existing : null;
    $link = trim((string)$link);
    if ($link === '' && $existing) { $link = trim((string)($existing->meet_link ?? '')); }
    if ($link === '' || preg_match('~^https://meet\.google\.com/new(?:[/?#].*)?$~i', $link)) {
        $room = $existing && !empty($existing->room_name) && ($existing->provider ?? '') === 'jitsi'
            ? $existing->room_name : jitsi_generate_room_name();
        return ['provider' => 'jitsi', 'room_name' => $room,
            'room_pin' => $existing && !empty($existing->room_pin) ? $existing->room_pin : (get_option('jitsi_require_pin') === '1' ? jitsi_generate_room_pin() : null),
            'meet_link' => jitsi_build_room_url($room)];
    }
    $candidate = (object)['meet_link' => $link];
    if ($existing && $link === ($existing->meet_link ?? '')) { $candidate = clone $existing; }
    $room = jitsi_meeting_room($candidate);
    if ($room) {
        return ['provider' => 'jitsi', 'room_name' => $room['room_name'], 'room_pin' => $room['room_pin'] ?: null, 'meet_link' => $link];
    }
    if ($existing && $link === ($existing->meet_link ?? '') && preg_match('~^https://meet\.google\.com/[a-z]{3}-[a-z]{4}-[a-z]{3}(?:[/?#].*)?$~i', $link)) {
        return ['provider' => 'google_meet', 'room_name' => null, 'room_pin' => null, 'meet_link' => $link];
    }
    throw new InvalidArgumentException('Enter a shared Jitsi room URL or a supported saved legacy meeting URL.');
}

function jitsi_build_invitation_payload($meeting, $client = false)
{
    $meeting = (object)$meeting;
    $title = (string)($meeting->subject ?? $meeting->title ?? 'Video meeting');
    $link = trim((string)($meeting->meet_link ?? ''));
    $text = $title . "\nStarts: " . ($meeting->start_time ?? '') . "\nTimezone: " . (get_option('google_meet_timezone') ?: date_default_timezone_get())
        . "\nScheduled duration: " . (int)($meeting->duration_minutes ?? 30) . " minutes\nJoin: " . $link;
    if (!empty($meeting->room_pin)) { $text .= "\nRoom PIN: " . $meeting->room_pin; }
    return ['link' => $link, 'plain_text' => $text,
        'whatsapp_url' => 'https://api.whatsapp.com/send?text=' . rawurlencode($text),
        'mailto_url' => 'mailto:?subject=' . rawurlencode($title) . '&body=' . rawurlencode($text),
        'ics_url' => $client ? site_url('google_meet/meeting_clients/calendar/' . (int)$meeting->id) : admin_url('google_meet/calendar/' . (int)$meeting->id)];
}

function jitsi_calendar_content($meeting)
{
    $meeting = (object)$meeting;
    $escape = function ($text) { return str_replace(["\\", "\r", "\n", ';', ','], ["\\\\", '', '\\n', '\\;', '\\,'], (string)$text); };
    try { $zone = new DateTimeZone(get_option('google_meet_timezone') ?: date_default_timezone_get()); }
    catch (Exception $e) { $zone = new DateTimeZone('UTC'); }
    $start = new DateTimeImmutable($meeting->start_time, $zone);
    $end = !empty($meeting->end_time) ? new DateTimeImmutable($meeting->end_time, $zone) : $start->modify('+' . max(1, (int)$meeting->duration_minutes) . ' minutes');
    $utc = new DateTimeZone('UTC');
    $lines = ['BEGIN:VCALENDAR', 'VERSION:2.0', 'PRODID:-//Smart Choice//Video Meetings//EN', 'CALSCALE:GREGORIAN', 'BEGIN:VEVENT',
        'UID:crm-meeting-' . (int)$meeting->id . '@' . (parse_url(site_url(), PHP_URL_HOST) ?: 'justsmartchoice.com'),
        'DTSTAMP:' . gmdate('Ymd\THis\Z'), 'DTSTART:' . $start->setTimezone($utc)->format('Ymd\THis\Z'),
        'DTEND:' . $end->setTimezone($utc)->format('Ymd\THis\Z'), 'SUMMARY:' . $escape($meeting->subject ?? $meeting->title ?? ''),
        'DESCRIPTION:' . $escape(jitsi_build_invitation_payload($meeting)['plain_text']), 'LOCATION:' . $escape($meeting->meet_link ?? ''), 'END:VEVENT', 'END:VCALENDAR'];
    $folded = [];
    foreach ($lines as $line) {
        while (strlen($line) > 75) {
            $length = 75;
            while ($length > 0 && (ord($line[$length]) & 0xC0) === 0x80) { $length--; }
            $folded[] = substr($line, 0, $length);
            $line = ' ' . substr($line, $length);
        }
        $folded[] = $line;
    }
    return implode("\r\n", $folded) . "\r\n";
}

/** Rename provider branding in displayed/sent text without changing route identifiers or URLs. */
function video_meeting_display_text($text)
{
    return preg_replace('/\bGoogle\s+Meet(?:ing)?(s)?\b/i', 'Video Meeting$1', (string)$text);
}

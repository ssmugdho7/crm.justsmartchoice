<?php
defined('BASEPATH') or exit('No direct script access allowed');

function ga_visitor_schema(string $prefix): array
{
    if (!preg_match('/^[a-zA-Z0-9_]+$/D', $prefix)) throw new InvalidArgumentException('Invalid table prefix.');
    return [
        "CREATE TABLE IF NOT EXISTS {$prefix}ga_visitor_sessions (
            sequence BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            id CHAR(32) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
            visitor_hash CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
            ip_address VARCHAR(45) NOT NULL, browser VARCHAR(20) NOT NULL, device VARCHAR(10) NOT NULL,
            first_seen DATETIME NOT NULL, last_seen DATETIME NOT NULL, current_path VARCHAR(500) NOT NULL,
            active_ms BIGINT UNSIGNED NOT NULL DEFAULT 0, last_credit_ms BIGINT UNSIGNED NOT NULL,
            PRIMARY KEY(sequence), UNIQUE KEY session_id(id), KEY first_seen(first_seen), KEY last_seen(last_seen),
            KEY visitor_hash(visitor_hash), KEY ip_seen(ip_address,last_seen)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        "CREATE TABLE IF NOT EXISTS {$prefix}ga_visitor_pages (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            session_id CHAR(32) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
            page_token CHAR(32) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
            path VARCHAR(500) NOT NULL, first_seen DATETIME NOT NULL, last_seen DATETIME NOT NULL,
            active_ms BIGINT UNSIGNED NOT NULL DEFAULT 0, client_active_ms BIGINT UNSIGNED NOT NULL DEFAULT 0,
            is_open TINYINT UNSIGNED NOT NULL DEFAULT 1,
            PRIMARY KEY(id), UNIQUE KEY page_event(session_id,page_token), KEY first_seen(first_seen),
            CONSTRAINT {$prefix}ga_vp_session FOREIGN KEY(session_id) REFERENCES {$prefix}ga_visitor_sessions(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        "CREATE TABLE IF NOT EXISTS {$prefix}ga_visitor_limits (
            ip_key CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL, minute DATETIME NOT NULL,
            hits INT UNSIGNED NOT NULL DEFAULT 1, PRIMARY KEY(ip_key,minute), KEY minute(minute)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    ];
}

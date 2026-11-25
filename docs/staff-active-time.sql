-- Additive installation for the CRM active-time summary. No historical data is changed.
CREATE TABLE IF NOT EXISTS tblsc_staff_active_time (
 staff_id INT NOT NULL,
 tracked_date DATE NOT NULL,
 seconds INT UNSIGNED NOT NULL DEFAULT 0,
 last_seen DATETIME NOT NULL,
 is_active TINYINT(1) NOT NULL DEFAULT 0,
 PRIMARY KEY (staff_id,tracked_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

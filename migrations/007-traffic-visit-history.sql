-- Retain an immutable audit trail of traffic-record edits, exits, and deletions.
CREATE TABLE IF NOT EXISTS traffic_visit_history (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    visit_id INT UNSIGNED NOT NULL,
    serial_no VARCHAR(30) NOT NULL,
    visitor_name VARCHAR(190) NOT NULL DEFAULT '',
    action VARCHAR(24) NOT NULL DEFAULT 'updated',
    changed_by INT UNSIGNED NULL,
    changed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    before_json LONGTEXT NULL,
    after_json LONGTEXT NULL,
    changes_json LONGTEXT NOT NULL,
    INDEX idx_traffic_history_visit (visit_id, changed_at),
    INDEX idx_traffic_history_changed_at (changed_at),
    INDEX idx_traffic_history_user (changed_by),
    CONSTRAINT fk_traffic_history_user FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Private attachments for user-authored offline map features.
-- Additive migration; does not alter ticket-system tables.
CREATE TABLE IF NOT EXISTS offline_map_feature_attachments (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    feature_id BIGINT UNSIGNED NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    stored_name VARCHAR(80) NOT NULL,
    mime_type VARCHAR(120) NOT NULL,
    size_bytes BIGINT UNSIGNED NOT NULL,
    uploaded_by BIGINT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_map_attachment_feature (feature_id),
    KEY idx_map_attachment_uploader (uploaded_by),
    CONSTRAINT fk_map_attachment_feature
        FOREIGN KEY (feature_id) REFERENCES offline_map_features(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

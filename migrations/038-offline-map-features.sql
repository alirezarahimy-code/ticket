-- Offline map user features. Base cartography is delivered separately as versioned local PMTiles.
-- This migration is additive and does not alter existing ticket-system tables.
CREATE TABLE IF NOT EXISTS offline_map_features (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    title VARCHAR(190) NOT NULL,
    description TEXT NULL,
    feature_type VARCHAR(32) NOT NULL DEFAULT 'point',
    geometry_json LONGTEXT NOT NULL,
    properties_json LONGTEXT NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    updated_by BIGINT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_offline_map_features_created (created_at),
    KEY idx_offline_map_features_type (feature_type),
    KEY idx_offline_map_features_owner (created_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- گروه‌های عمومی دسترسی؛ مستقل از گروه‌های عملیاتی غذا.
-- مجوزهای گروه به مجوزهای نقش اضافه می‌شوند و هیچ نقش یا مجوز موجودی را بازنویسی نمی‌کنند.
CREATE TABLE IF NOT EXISTS user_access_groups (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    description VARCHAR(500) NULL,
    created_by INT UNSIGNED NULL,
    updated_by INT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_user_access_groups_name (name),
    INDEX idx_user_access_groups_updated_at (updated_at),
    CONSTRAINT fk_user_access_groups_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_user_access_groups_updated_by FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS user_access_group_members (
    group_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    added_by INT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (group_id, user_id),
    INDEX idx_user_access_group_members_user (user_id, group_id),
    CONSTRAINT fk_user_access_group_members_group FOREIGN KEY (group_id) REFERENCES user_access_groups(id) ON DELETE CASCADE,
    CONSTRAINT fk_user_access_group_members_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_user_access_group_members_added_by FOREIGN KEY (added_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS user_access_group_permissions (
    group_id INT UNSIGNED NOT NULL,
    permission_code VARCHAR(100) NOT NULL,
    assigned_by INT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (group_id, permission_code),
    INDEX idx_user_access_group_permissions_code (permission_code),
    CONSTRAINT fk_user_access_group_permissions_group FOREIGN KEY (group_id) REFERENCES user_access_groups(id) ON DELETE CASCADE,
    CONSTRAINT fk_user_access_group_permissions_assigned_by FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

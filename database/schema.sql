CREATE TABLE IF NOT EXISTS inquiries (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    inquiry_date DATETIME NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(190) NOT NULL,
    tel VARCHAR(50) DEFAULT '',
    category VARCHAR(100) NOT NULL,
    katasiki VARCHAR(100) NOT NULL,
    parts_num VARCHAR(100) DEFAULT '',
    toc VARCHAR(20) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET @schema_name := DATABASE();

SET @sql := (
    SELECT IF(
        EXISTS(
            SELECT 1
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = @schema_name
              AND TABLE_NAME = 'acc'
              AND COLUMN_NAME = 'member_type'
        ),
        'SELECT 1',
        'ALTER TABLE acc ADD COLUMN member_type VARCHAR(20) NULL AFTER state'
    )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
    SELECT IF(
        EXISTS(
            SELECT 1
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = @schema_name
              AND TABLE_NAME = 'acc'
              AND COLUMN_NAME = 'biz_status'
        ),
        'SELECT 1',
        'ALTER TABLE acc ADD COLUMN biz_status VARCHAR(20) NULL AFTER member_type'
    )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
    SELECT IF(
        EXISTS(
            SELECT 1
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = @schema_name
              AND TABLE_NAME = 'acc'
              AND COLUMN_NAME = 'password_hash'
        ),
        'SELECT 1',
        'ALTER TABLE acc ADD COLUMN password_hash VARCHAR(255) NULL AFTER pass'
    )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
    SELECT IF(
        EXISTS(
            SELECT 1
            FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = @schema_name
              AND TABLE_NAME = 'acc'
              AND INDEX_NAME = 'idx_acc_member_type'
        ),
        'SELECT 1',
        'ALTER TABLE acc ADD INDEX idx_acc_member_type (member_type)'
    )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
    SELECT IF(
        EXISTS(
            SELECT 1
            FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = @schema_name
              AND TABLE_NAME = 'acc'
              AND INDEX_NAME = 'idx_acc_biz_status'
        ),
        'SELECT 1',
        'ALTER TABLE acc ADD INDEX idx_acc_biz_status (biz_status)'
    )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

UPDATE acc
SET member_type = CASE
    WHEN state = 2 THEN 'biz'
    WHEN state = 1 THEN 'net'
    ELSE 'guest'
END
WHERE COALESCE(member_type, '') = '';

UPDATE acc
SET biz_status = CASE
    WHEN COALESCE(member_type, '') = 'biz' OR state = 2 THEN 'approved'
    ELSE 'none'
END
WHERE COALESCE(biz_status, '') = '';

UPDATE acc
SET member_type = 'guest',
    biz_status = 'none'
WHERE state = 9
  AND (member_type <> 'guest' OR biz_status <> 'none');

CREATE TABLE IF NOT EXISTS special_member_requests (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    acc_id INT(11) NOT NULL,
    company_name VARCHAR(190) NOT NULL DEFAULT '',
    shop_name VARCHAR(190) NOT NULL DEFAULT '',
    contact_name VARCHAR(190) NOT NULL DEFAULT '',
    email VARCHAR(190) NOT NULL DEFAULT '',
    tel VARCHAR(50) NOT NULL DEFAULT '',
    zip VARCHAR(20) NOT NULL DEFAULT '',
    address_line1 VARCHAR(190) NOT NULL DEFAULT '',
    address_line2 VARCHAR(190) NOT NULL DEFAULT '',
    address_line3 VARCHAR(190) NOT NULL DEFAULT '',
    website_url VARCHAR(255) NOT NULL DEFAULT '',
    business_type VARCHAR(190) NOT NULL DEFAULT '',
    notes TEXT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'pending',
    requested_at DATETIME NOT NULL,
    reviewed_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_special_member_requests_acc_id (acc_id),
    KEY idx_special_member_requests_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS special_member_request_files (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    request_id BIGINT UNSIGNED NOT NULL,
    stored_name VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    mime_type VARCHAR(100) NOT NULL DEFAULT '',
    file_size INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY idx_special_member_request_files_request_id (request_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

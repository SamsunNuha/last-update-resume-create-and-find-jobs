-- Fix resumes table schema completely
USE smart_resume_job_finder;

-- First, check if columns exist before adding them
SET @dbname = DATABASE();
SET @tablename = 'resumes';

-- Add portfolio column if it doesn't exist
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'portfolio');
SET @query = IF(@col_exists = 0, 
    'ALTER TABLE resumes ADD COLUMN portfolio VARCHAR(255) NULL AFTER website', 
    'SELECT "portfolio column already exists"');
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add updated_at column if it doesn't exist
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'updated_at');
SET @query = IF(@col_exists = 0, 
    'ALTER TABLE resumes ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER extra_details', 
    'SELECT "updated_at column already exists"');
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add is_locked column if it doesn't exist (for free user limit)
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'is_locked');
SET @query = IF(@col_exists = 0, 
    'ALTER TABLE resumes ADD COLUMN is_locked TINYINT(1) DEFAULT 0 AFTER updated_at', 
    'SELECT "is_locked column already exists"');
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SELECT 'Schema update completed successfully' AS status;

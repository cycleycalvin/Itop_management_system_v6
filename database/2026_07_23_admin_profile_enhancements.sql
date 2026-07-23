USE centexs_itop_ims;

-- Add administrative & profile enhancement columns to users table
ALTER TABLE users
    ADD COLUMN IF NOT EXISTS position VARCHAR(100) NULL AFTER role,
    ADD COLUMN IF NOT EXISTS department VARCHAR(100) NULL AFTER position,
    ADD COLUMN IF NOT EXISTS office_location VARCHAR(190) NULL AFTER department,
    ADD COLUMN IF NOT EXISTS employee_id VARCHAR(50) NULL AFTER office_location,
    ADD COLUMN IF NOT EXISTS two_factor_enabled TINYINT(1) NOT NULL DEFAULT 0 AFTER time_zone,
    ADD COLUMN IF NOT EXISTS password_changed_at DATETIME NULL AFTER last_login,
    ADD COLUMN IF NOT EXISTS theme_preference VARCHAR(20) NOT NULL DEFAULT 'light' AFTER time_zone,
    ADD COLUMN IF NOT EXISTS default_landing_page VARCHAR(50) NOT NULL DEFAULT 'dashboard' AFTER theme_preference,
    ADD COLUMN IF NOT EXISTS notification_preferences JSON NULL AFTER default_landing_page;

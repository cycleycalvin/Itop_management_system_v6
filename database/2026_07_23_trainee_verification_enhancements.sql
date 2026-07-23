-- Trainee Document Verification & Master Data Enhancements

ALTER TABLE trainee_documents ADD COLUMN IF NOT EXISTS status VARCHAR(50) NOT NULL DEFAULT 'Pending Verification';
ALTER TABLE trainee_documents ADD COLUMN IF NOT EXISTS verification_notes TEXT NULL;
ALTER TABLE trainee_documents ADD COLUMN IF NOT EXISTS verified_at DATETIME NULL;

ALTER TABLE trainee_profiles ADD COLUMN IF NOT EXISTS is_verified TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE trainee_profiles ADD COLUMN IF NOT EXISTS verification_status VARCHAR(50) NOT NULL DEFAULT 'Pending Verification';

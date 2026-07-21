USE centexs_itop_ims;

-- Add master data reference columns to users table if they do not exist
ALTER TABLE users
    ADD COLUMN IF NOT EXISTS company_id INT NULL AFTER address,
    ADD COLUMN IF NOT EXISTS institution_id INT NULL AFTER company_id,
    ADD COLUMN IF NOT EXISTS location_id INT NULL AFTER institution_id,
    ADD COLUMN IF NOT EXISTS profession_id INT NULL AFTER location_id;

-- Add foreign key constraints if not already exist
-- Since ALTER TABLE ADD CONSTRAINT might fail if it already exists, let's make it simple.
-- The standard practice in these migration files is to write plain DDL.
ALTER TABLE users
    ADD CONSTRAINT fk_users_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE SET NULL,
    ADD CONSTRAINT fk_users_institution FOREIGN KEY (institution_id) REFERENCES institutions(id) ON DELETE SET NULL,
    ADD CONSTRAINT fk_users_location FOREIGN KEY (location_id) REFERENCES locations(id) ON DELETE SET NULL,
    ADD CONSTRAINT fk_users_profession FOREIGN KEY (profession_id) REFERENCES professions(id) ON DELETE SET NULL;

USE centexs_itop_ims;

ALTER TABLE users
    ADD COLUMN IF NOT EXISTS role VARCHAR(50) NULL AFTER role_id,
    ADD COLUMN IF NOT EXISTS profile_picture VARCHAR(255) NULL AFTER address,
    ADD COLUMN IF NOT EXISTS last_login DATETIME NULL AFTER email_verified_at,
    MODIFY status ENUM('pending','active','inactive','suspended') NOT NULL DEFAULT 'pending';

UPDATE users
JOIN roles ON roles.id = users.role_id
SET users.role = roles.slug
WHERE users.role IS NULL;

ALTER TABLE courses
    ADD COLUMN IF NOT EXISTS thumbnail_image VARCHAR(255) NULL AFTER description,
    ADD COLUMN IF NOT EXISTS max_participants INT NULL AFTER capacity,
    ADD COLUMN IF NOT EXISTS course_status ENUM('draft','published','active','completed','archived') NULL AFTER status,
    ADD COLUMN IF NOT EXISTS created_by INT NULL AFTER fee;

CREATE INDEX IF NOT EXISTS idx_courses_status ON courses (status);
CREATE INDEX IF NOT EXISTS idx_courses_category ON courses (category);

UPDATE courses SET max_participants = capacity WHERE max_participants IS NULL;
UPDATE courses SET course_status = status WHERE course_status IS NULL;

CREATE TABLE IF NOT EXISTS certificate_templates (
    template_id INT AUTO_INCREMENT PRIMARY KEY,
    template_name VARCHAR(190) NOT NULL,
    background_image VARCHAR(255),
    logo VARCHAR(255),
    signature VARCHAR(255),
    font_family VARCHAR(120) NOT NULL DEFAULT 'Arial',
    font_size INT NOT NULL DEFAULT 28,
    text_color VARCHAR(20) NOT NULL DEFAULT '#182230',
    layout_json JSON NULL,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_certificate_templates_status (status)
);

INSERT INTO certificate_templates (
    template_name,
    font_family,
    font_size,
    text_color,
    layout_json,
    status
)
SELECT
    'Classic Bordered Certification',
    'Arial, sans-serif',
    28,
    '#182230',
    '{
        "style": {
            "template": "classic_border",
            "title_color": "#aa3338",
            "accent_color": "#244f63",
            "border_color": "#25566a",
            "seal_color": "#b53638",
            "pattern_opacity": 0.45
        },
        "show_seal": true,
        "show_verification": true,
        "seal_text": "ITOP",
        "seal_caption": "Certified",
        "placeholders": {
            "title": "Certificate Title",
            "recipient": "Participant Name",
            "course": "Course Name",
            "date": "Issue Date",
            "certificate_no": "Certificate Number",
            "issuer_title": "Authorized Signatory"
        }
    }',
    'active'
WHERE NOT EXISTS (
    SELECT 1 FROM certificate_templates WHERE template_name = 'Classic Bordered Certification'
);

ALTER TABLE certificates
    ADD COLUMN IF NOT EXISTS template_id INT NULL AFTER trainee_id,
    ADD COLUMN IF NOT EXISTS issue_date DATE NULL AFTER issued_at,
    ADD COLUMN IF NOT EXISTS certificate_number VARCHAR(80) NULL AFTER certificate_no,
    ADD COLUMN IF NOT EXISTS pdf_path VARCHAR(255) NULL AFTER file_path,
    ADD COLUMN IF NOT EXISTS status ENUM('issued','reissued','revoked') NOT NULL DEFAULT 'issued' AFTER issued_by;

CREATE INDEX IF NOT EXISTS idx_certificates_trainee ON certificates (trainee_id);
CREATE INDEX IF NOT EXISTS idx_certificates_course ON certificates (course_id);

UPDATE certificates SET issue_date = issued_at WHERE issue_date IS NULL;
UPDATE certificates SET certificate_number = certificate_no WHERE certificate_number IS NULL;
UPDATE certificates SET pdf_path = file_path WHERE pdf_path IS NULL AND file_path IS NOT NULL;

ALTER TABLE evaluations
    ADD COLUMN IF NOT EXISTS course_rating TINYINT NULL AFTER rating,
    ADD COLUMN IF NOT EXISTS instructor_rating TINYINT NULL AFTER course_rating,
    ADD COLUMN IF NOT EXISTS comments TEXT NULL AFTER feedback,
    ADD COLUMN IF NOT EXISTS completed_at DATETIME NULL AFTER comments;

CREATE INDEX IF NOT EXISTS idx_evaluations_course ON evaluations (course_id);
CREATE INDEX IF NOT EXISTS idx_evaluations_trainee ON evaluations (trainee_id);

UPDATE evaluations SET course_rating = rating WHERE course_rating IS NULL;
UPDATE evaluations SET comments = feedback WHERE comments IS NULL AND feedback IS NOT NULL;

CREATE TABLE IF NOT EXISTS trainee_profiles (
    profile_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    identity_number VARCHAR(100),
    phone VARCHAR(50),
    address TEXT,
    education TEXT,
    employment TEXT,
    emergency_contact TEXT,
    profile_picture VARCHAR(255),
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_trainee_profiles_identity (identity_number),
    CONSTRAINT fk_trainee_profiles_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS trainee_documents (
    document_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    document_type VARCHAR(120) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    uploaded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_trainee_documents_user (user_id),
    CONSTRAINT fk_trainee_documents_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

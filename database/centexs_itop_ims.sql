CREATE DATABASE IF NOT EXISTS centexs_itop_ims CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE centexs_itop_ims;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS activity_logs, reports, evaluations, certificates, certificate_templates, trainee_documents, trainee_profiles, quiz_results, quiz_questions, quizzes, assignment_submissions, assignments, learning_materials, attendance, enrolments, courses, announcements, website_settings, users, roles;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(80) NOT NULL,
    slug VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT NOT NULL,
    name VARCHAR(160) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    phone VARCHAR(50),
    address TEXT,
    profile_picture VARCHAR(255),
    status ENUM('pending','active','inactive','suspended') NOT NULL DEFAULT 'pending',
    email_verified_at DATETIME NULL,
    last_login DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id)
);

CREATE TABLE courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    instructor_id INT NULL,
    title VARCHAR(190) NOT NULL,
    category VARCHAR(120) NOT NULL,
    description TEXT,
    thumbnail_image VARCHAR(255),
    start_date DATE NULL,
    end_date DATE NULL,
    capacity INT NOT NULL DEFAULT 25,
    max_participants INT NULL,
    status ENUM('draft','published','active','completed','archived') NOT NULL DEFAULT 'draft',
    course_status ENUM('draft','published','active','completed','archived') NULL,
    fee DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    created_by INT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (instructor_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE enrolments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    trainee_id INT NOT NULL,
    status ENUM('pending','active','completed','rejected','withdrawn') NOT NULL DEFAULT 'pending',
    progress_percent TINYINT NOT NULL DEFAULT 0,
    completed_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_enrolment (course_id, trainee_id),
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (trainee_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    enrolment_id INT NOT NULL,
    session_date DATE NOT NULL,
    status ENUM('present','absent','late','excused') NOT NULL,
    remarks VARCHAR(255),
    FOREIGN KEY (enrolment_id) REFERENCES enrolments(id) ON DELETE CASCADE
);

CREATE TABLE learning_materials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    title VARCHAR(190) NOT NULL,
    type ENUM('document','video','link','archive') NOT NULL DEFAULT 'document',
    file_path VARCHAR(255),
    external_url VARCHAR(500),
    uploaded_by INT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    title VARCHAR(190) NOT NULL,
    instructions TEXT,
    due_date DATETIME NULL,
    max_score DECIMAL(6,2) NOT NULL DEFAULT 100.00,
    created_by INT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE assignment_submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    assignment_id INT NOT NULL,
    trainee_id INT NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    notes TEXT,
    score DECIMAL(6,2) NULL,
    feedback TEXT,
    status ENUM('submitted','graded','resubmission_required') NOT NULL DEFAULT 'submitted',
    submitted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    graded_at DATETIME NULL,
    UNIQUE KEY unique_submission (assignment_id, trainee_id),
    FOREIGN KEY (assignment_id) REFERENCES assignments(id) ON DELETE CASCADE,
    FOREIGN KEY (trainee_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE quizzes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    title VARCHAR(190) NOT NULL,
    total_marks DECIMAL(6,2) NOT NULL DEFAULT 100.00,
    status ENUM('draft','published','closed') NOT NULL DEFAULT 'draft',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

CREATE TABLE quiz_questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    quiz_id INT NOT NULL,
    question TEXT NOT NULL,
    option_a VARCHAR(255),
    option_b VARCHAR(255),
    option_c VARCHAR(255),
    option_d VARCHAR(255),
    correct_option CHAR(1),
    marks DECIMAL(6,2) NOT NULL DEFAULT 1.00,
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE
);

CREATE TABLE quiz_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    quiz_id INT NOT NULL,
    trainee_id INT NOT NULL,
    score DECIMAL(6,2) NOT NULL DEFAULT 0.00,
    submitted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_quiz_result (quiz_id, trainee_id),
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE,
    FOREIGN KEY (trainee_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE certificate_templates (
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

CREATE TABLE certificates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    trainee_id INT NOT NULL,
    template_id INT NULL,
    certificate_no VARCHAR(80) NOT NULL UNIQUE,
    certificate_number VARCHAR(80) NULL,
    verification_code VARCHAR(80) NOT NULL UNIQUE,
    file_path VARCHAR(255),
    pdf_path VARCHAR(255),
    issued_at DATE NOT NULL,
    issue_date DATE NULL,
    issued_by INT NULL,
    status ENUM('issued','reissued','revoked') NOT NULL DEFAULT 'issued',
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (trainee_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (template_id) REFERENCES certificate_templates(template_id) ON DELETE SET NULL,
    FOREIGN KEY (issued_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE evaluations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    trainee_id INT NOT NULL,
    rating TINYINT NOT NULL,
    course_rating TINYINT NULL,
    instructor_rating TINYINT NULL,
    feedback TEXT,
    comments TEXT,
    completed_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_evaluation (course_id, trainee_id),
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (trainee_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE trainee_profiles (
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
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE trainee_documents (
    document_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    document_type VARCHAR(120) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    uploaded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_trainee_documents_user (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    report_type VARCHAR(100) NOT NULL,
    title VARCHAR(190) NOT NULL,
    generated_by INT NULL,
    file_path VARCHAR(255),
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (generated_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(190) NOT NULL,
    body TEXT NOT NULL,
    is_public TINYINT(1) NOT NULL DEFAULT 1,
    created_by INT NULL,
    published_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE website_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(120) NOT NULL UNIQUE,
    setting_value TEXT
);

CREATE TABLE activity_logs (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action VARCHAR(255) NOT NULL,
    ip_address VARCHAR(80),
    user_agent VARCHAR(255),
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

INSERT INTO roles (id, name, slug) VALUES
(1, 'Administrator', 'admin'),
(2, 'Instructor', 'instructor'),
(3, 'Trainee', 'trainee');

-- Default password for seed users: password
INSERT INTO users (id, role_id, name, email, password_hash, phone, status, email_verified_at) VALUES
(1, 1, 'CENTEXS Administrator', 'admin@centexs.local', '$2y$10$/UmdvyHGo04hGxRykYJSSuYJIPr/3ndLia8Do8jiiLd3cS8OLw6MG', '+6082000001', 'active', NOW()),
(2, 2, 'Instructor Aida Rahman', 'instructor@centexs.local', '$2y$10$/UmdvyHGo04hGxRykYJSSuYJIPr/3ndLia8Do8jiiLd3cS8OLw6MG', '+6082000002', 'active', NOW()),
(3, 3, 'Trainee Daniel Ling', 'trainee@centexs.local', '$2y$10$/UmdvyHGo04hGxRykYJSSuYJIPr/3ndLia8Do8jiiLd3cS8OLw6MG', '+6082000003', 'active', NOW());

INSERT INTO courses (id, instructor_id, title, category, description, start_date, end_date, capacity, max_participants, status, course_status, fee, created_by) VALUES
(1, 2, 'Industrial IoT Operations', 'Digital Technology', 'Hands-on training for connected sensors, data acquisition, dashboards, and industrial monitoring workflows.', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), 30, 30, 'active', 'active', 0.00, 1),
(2, 2, 'Cloud Support Fundamentals', 'Cloud Computing', 'Practical cloud administration, identity, storage, backup, and support operations for entry-level technical teams.', DATE_ADD(CURDATE(), INTERVAL 14 DAY), DATE_ADD(CURDATE(), INTERVAL 45 DAY), 25, 25, 'published', 'published', 0.00, 1),
(3, NULL, 'Cybersecurity Awareness for Operations', 'Cybersecurity', 'Security foundations for safe workplace systems, phishing prevention, incident reporting, and data protection.', DATE_ADD(CURDATE(), INTERVAL 21 DAY), DATE_ADD(CURDATE(), INTERVAL 35 DAY), 40, 40, 'published', 'published', 0.00, 1);

INSERT INTO enrolments (course_id, trainee_id, status, progress_percent) VALUES
(1, 3, 'active', 45);

INSERT INTO learning_materials (course_id, title, type, external_url, uploaded_by) VALUES
(1, 'IoT Programme Brief', 'link', 'https://centexs.my', 2),
(1, 'Sensor Data Collection Video', 'video', 'https://www.youtube.com/', 2);

INSERT INTO assignments (course_id, title, instructions, due_date, max_score, created_by) VALUES
(1, 'Device Setup Reflection', 'Upload a short report describing setup steps, issues found, and evidence of sensor readings.', DATE_ADD(NOW(), INTERVAL 7 DAY), 100, 2);

INSERT INTO quizzes (course_id, title, total_marks, status) VALUES
(1, 'IoT Safety Check', 20, 'published');

INSERT INTO certificate_templates (template_id, template_name, font_family, font_size, text_color, layout_json, status) VALUES
(1, 'Classic Bordered Certification', 'Arial, sans-serif', 28, '#182230', '{
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
}', 'active');

INSERT INTO certificates (course_id, trainee_id, template_id, certificate_no, certificate_number, verification_code, issued_at, issue_date, issued_by, status) VALUES
(1, 3, 1, 'ITOP-2026-0001', 'ITOP-2026-0001', 'CENTEXS-VERIFY-0001', CURDATE(), CURDATE(), 1, 'issued');

INSERT INTO evaluations (course_id, trainee_id, rating, course_rating, instructor_rating, feedback, comments, completed_at) VALUES
(1, 3, 5, 5, 5, 'Clear training flow and useful practical activities.', 'Clear training flow and useful practical activities.', NOW());

INSERT INTO announcements (title, body, is_public, created_by, published_at) VALUES
('ITOP Registration Now Open', 'Applications are open for the latest CENTEXS ITOP technical training intakes.', 1, 1, NOW()),
('Learning Materials Updated', 'New course notes and video links have been added for active trainees.', 0, 2, NOW());

INSERT INTO website_settings (setting_key, setting_value) VALUES
('site_name', 'CENTEXS ITOP Management System'),
('support_email', 'info@centexs.my');

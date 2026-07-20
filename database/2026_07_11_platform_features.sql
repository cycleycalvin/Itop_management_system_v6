USE centexs_itop_ims;

ALTER TABLE users
    ADD COLUMN IF NOT EXISTS identity_number VARCHAR(100) NULL AFTER address,
    ADD COLUMN IF NOT EXISTS gender VARCHAR(30) NULL AFTER identity_number,
    ADD COLUMN IF NOT EXISTS date_of_birth DATE NULL AFTER gender,
    ADD COLUMN IF NOT EXISTS institution_company VARCHAR(190) NULL AFTER date_of_birth,
    ADD COLUMN IF NOT EXISTS time_zone VARCHAR(80) NOT NULL DEFAULT 'Asia/Kuala_Lumpur' AFTER institution_company,
    ADD COLUMN IF NOT EXISTS first_login DATETIME NULL AFTER last_login,
    ADD COLUMN IF NOT EXISTS last_active DATETIME NULL AFTER first_login;

ALTER TABLE enrolments
    ADD COLUMN IF NOT EXISTS attendance_requirement_met TINYINT(1) NOT NULL DEFAULT 0 AFTER progress_percent,
    ADD COLUMN IF NOT EXISTS assessments_completed TINYINT(1) NOT NULL DEFAULT 0 AFTER attendance_requirement_met,
    ADD COLUMN IF NOT EXISTS evaluation_submitted TINYINT(1) NOT NULL DEFAULT 0 AFTER assessments_completed,
    ADD COLUMN IF NOT EXISTS certificate_available TINYINT(1) NOT NULL DEFAULT 0 AFTER evaluation_submitted;

ALTER TABLE certificates
    ADD COLUMN IF NOT EXISTS approval_status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending' AFTER status,
    ADD COLUMN IF NOT EXISTS approved_by INT NULL AFTER approval_status,
    ADD COLUMN IF NOT EXISTS approved_at DATETIME NULL AFTER approved_by,
    ADD COLUMN IF NOT EXISTS rejection_reason TEXT NULL AFTER approved_at;

CREATE TABLE IF NOT EXISTS user_sessions (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_token VARCHAR(128) NOT NULL,
    device_label VARCHAR(190),
    browser VARCHAR(190),
    ip_address VARCHAR(80),
    last_active DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    revoked_at DATETIME NULL,
    UNIQUE KEY unique_session_token (session_token),
    INDEX idx_user_sessions_user (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS login_activity (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    email VARCHAR(190),
    status ENUM('success','failed') NOT NULL,
    ip_address VARCHAR(80),
    user_agent VARCHAR(255),
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_login_activity_user (user_id),
    INDEX idx_login_activity_created (created_at),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS conversations (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    subject VARCHAR(190) NOT NULL DEFAULT 'Conversation',
    created_by INT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS conversation_participants (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    conversation_id BIGINT NOT NULL,
    user_id INT NOT NULL,
    last_read_at DATETIME NULL,
    deleted_at DATETIME NULL,
    UNIQUE KEY unique_conversation_user (conversation_id, user_id),
    FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS messages (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    conversation_id BIGINT NOT NULL,
    sender_id INT NOT NULL,
    body TEXT NOT NULL,
    attachment_path VARCHAR(255),
    read_at DATETIME NULL,
    deleted_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_messages_conversation (conversation_id),
    INDEX idx_messages_created (created_at),
    FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS notifications (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    sender_id INT NULL,
    notification_type VARCHAR(80) NOT NULL,
    title VARCHAR(190) NOT NULL,
    description TEXT,
    related_url VARCHAR(500),
    read_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL,
    INDEX idx_notifications_user_read (user_id, read_at),
    INDEX idx_notifications_type (notification_type),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS course_progress (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    enrolment_id INT NOT NULL,
    progress_percent TINYINT NOT NULL DEFAULT 0,
    learning_status ENUM('not_started','in_progress','completed') NOT NULL DEFAULT 'not_started',
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_progress_enrolment (enrolment_id),
    FOREIGN KEY (enrolment_id) REFERENCES enrolments(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS assessments (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    title VARCHAR(190) NOT NULL,
    assessment_type ENUM('quiz','exam','practical','project') NOT NULL DEFAULT 'quiz',
    max_score DECIMAL(6,2) NOT NULL DEFAULT 100.00,
    due_date DATETIME NULL,
    status ENUM('draft','available','closed') NOT NULL DEFAULT 'draft',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS grades (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    enrolment_id INT NOT NULL,
    assessment_id BIGINT NULL,
    assignment_submission_id INT NULL,
    grade_label VARCHAR(50),
    score DECIMAL(6,2) NULL,
    max_score DECIMAL(6,2) NOT NULL DEFAULT 100.00,
    feedback TEXT,
    graded_by INT NULL,
    graded_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (enrolment_id) REFERENCES enrolments(id) ON DELETE CASCADE,
    FOREIGN KEY (assessment_id) REFERENCES assessments(id) ON DELETE SET NULL,
    FOREIGN KEY (assignment_submission_id) REFERENCES assignment_submissions(id) ON DELETE SET NULL,
    FOREIGN KEY (graded_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS certificate_approvals (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    certificate_id INT NOT NULL,
    reviewer_id INT NOT NULL,
    status ENUM('approved','rejected') NOT NULL,
    remarks TEXT,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (certificate_id) REFERENCES certificates(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewer_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS certificate_download_logs (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    certificate_id INT NOT NULL,
    user_id INT NOT NULL,
    ip_address VARCHAR(80),
    downloaded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (certificate_id) REFERENCES certificates(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS dashboard_statistics (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    statistic_key VARCHAR(120) NOT NULL UNIQUE,
    statistic_label VARCHAR(190) NOT NULL,
    statistic_value DECIMAL(12,2) NOT NULL DEFAULT 0,
    statistic_group VARCHAR(80) NOT NULL DEFAULT 'dashboard',
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS report_cache (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    cache_key VARCHAR(190) NOT NULL UNIQUE,
    report_type VARCHAR(100) NOT NULL,
    filters_json JSON NULL,
    payload_json JSON NULL,
    generated_by INT NULL,
    generated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME NULL,
    FOREIGN KEY (generated_by) REFERENCES users(id) ON DELETE SET NULL
);

UPDATE users SET first_login = last_login WHERE first_login IS NULL AND last_login IS NOT NULL;

UPDATE enrolments
SET status = 'completed',
    progress_percent = 100,
    attendance_requirement_met = 1,
    assessments_completed = 1,
    evaluation_submitted = 1,
    certificate_available = 1,
    completed_at = COALESCE(completed_at, NOW())
WHERE trainee_id = 3 AND course_id = 1;

UPDATE certificates
SET approval_status = 'approved',
    approved_by = 1,
    approved_at = COALESCE(approved_at, NOW())
WHERE trainee_id = 3 AND course_id = 1;

INSERT INTO course_progress (enrolment_id, progress_percent, learning_status)
SELECT id, progress_percent, IF(status = 'completed', 'completed', 'in_progress')
FROM enrolments
ON DUPLICATE KEY UPDATE progress_percent = VALUES(progress_percent), learning_status = VALUES(learning_status);

INSERT INTO grades (enrolment_id, grade_label, score, max_score, feedback, graded_by, graded_at)
SELECT e.id, 'Final Assessment', 92.00, 100.00, 'Completed all required learning outcomes.', 2, NOW()
FROM enrolments e
WHERE e.trainee_id = 3 AND e.course_id = 1
  AND NOT EXISTS (SELECT 1 FROM grades g WHERE g.enrolment_id = e.id AND g.grade_label = 'Final Assessment');

INSERT INTO notifications (user_id, sender_id, notification_type, title, description, related_url)
SELECT 3, 1, 'certificate_ready', 'Certificate Ready', 'Your Industrial IoT Operations certificate has been approved and is ready to download.', 'index.php?page=trainee-certificates'
WHERE NOT EXISTS (SELECT 1 FROM notifications WHERE user_id = 3 AND notification_type = 'certificate_ready');

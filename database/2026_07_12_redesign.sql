USE centexs_itop_ims;

-- ── Academy ID on Courses (if not already present) ──
ALTER TABLE courses
    ADD COLUMN IF NOT EXISTS academy_id INT NULL AFTER created_by;

-- Link existing courses to academies by category keyword matching
UPDATE courses c
JOIN academies a ON (
    (a.code = 'ADGEA' AND (c.category LIKE '%Digital%' OR c.category LIKE '%Aerospace%' OR c.category LIKE '%Green%' OR c.category LIKE '%Cloud%' OR c.category LIKE '%Cyber%' OR c.category LIKE '%IoT%' OR c.category LIKE '%Data%' OR c.category LIKE '%AI%'))
    OR
    (a.code = 'IESGA' AND (c.category LIKE '%Industry%' OR c.category LIKE '%ESG%' OR c.category LIKE '%Manufacturing%' OR c.category LIKE '%Safety%' OR c.category LIKE '%Quality%'))
)
SET c.academy_id = a.id
WHERE c.academy_id IS NULL;

-- ── Success Stories ──
CREATE TABLE IF NOT EXISTS success_stories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    trainee_name VARCHAR(160) NOT NULL,
    course_title VARCHAR(190) NOT NULL,
    quote TEXT NOT NULL,
    photo VARCHAR(255) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO success_stories (trainee_name, course_title, quote, is_active, sort_order) VALUES
('Daniel Ling', 'Industrial IoT Operations', 'The hands-on approach at CENTEXS gave me real industry skills. I secured a position as an IoT technician within two months of completing the programme.', 1, 1),
('Sarah Tan', 'Cloud Support Fundamentals', 'CENTEXS instructors were incredibly supportive. The practical labs prepared me for real-world cloud administration challenges.', 1, 2),
('Ahmad Razak', 'Cybersecurity Awareness', 'This programme transformed my understanding of workplace security. I now lead our company''s security awareness initiatives.', 1, 3);

-- ── Upcoming Intakes ──
CREATE TABLE IF NOT EXISTS upcoming_intakes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    academy_id INT NULL,
    course_id INT NULL,
    intake_title VARCHAR(190) NOT NULL,
    intake_date DATE NOT NULL,
    description TEXT,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (academy_id) REFERENCES academies(id) ON DELETE SET NULL,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL
);

INSERT INTO upcoming_intakes (academy_id, intake_title, intake_date, description, is_active) VALUES
((SELECT id FROM academies WHERE code = 'ADGEA' LIMIT 1), 'ADGEA Intake — August 2026', DATE_ADD(CURDATE(), INTERVAL 30 DAY), 'New intake for Aerospace, Digital & Green Energy Academy programmes. Limited seats available.', 1),
((SELECT id FROM academies WHERE code = 'IESGA' LIMIT 1), 'IESGA Intake — September 2026', DATE_ADD(CURDATE(), INTERVAL 60 DAY), 'Industry & ESG Academy opening for Q3 enrolment. Apply early to secure your place.', 1),
((SELECT id FROM academies WHERE code = 'ADGEA' LIMIT 1), 'Advanced IoT Programme — October 2026', DATE_ADD(CURDATE(), INTERVAL 90 DAY), 'Specialised IoT operations training for working professionals.', 1);

-- ── Additional Website Settings for Admin Control ──
INSERT INTO website_settings (setting_key, setting_value) VALUES
('hero_title', 'CENTEXS training operations in one secure web platform')
ON DUPLICATE KEY UPDATE setting_value = setting_value;

INSERT INTO website_settings (setting_key, setting_value) VALUES
('hero_subtitle', 'Manage programme registration, learning materials, assessments, progress, reports, evaluations, and certificates for real-world ITOP delivery.')
ON DUPLICATE KEY UPDATE setting_value = setting_value;

INSERT INTO website_settings (setting_key, setting_value) VALUES
('footer_about', 'Centre for Technology Excellence Sarawak (CENTEXS) is a premier training institution in Sarawak, delivering industry-driven technical and vocational programmes.')
ON DUPLICATE KEY UPDATE setting_value = setting_value;

INSERT INTO website_settings (setting_key, setting_value) VALUES
('footer_address', 'CENTEXS Kuching, Jalan Canna, Off Jalan Wan Alwi, 93350 Kuching, Sarawak, Malaysia')
ON DUPLICATE KEY UPDATE setting_value = setting_value;

INSERT INTO website_settings (setting_key, setting_value) VALUES
('footer_phone', '+60 82-363 200')
ON DUPLICATE KEY UPDATE setting_value = setting_value;

INSERT INTO website_settings (setting_key, setting_value) VALUES
('footer_email', 'info@centexs.my')
ON DUPLICATE KEY UPDATE setting_value = setting_value;

INSERT INTO website_settings (setting_key, setting_value) VALUES
('footer_social_facebook', 'https://www.facebook.com/centexs.sarawak')
ON DUPLICATE KEY UPDATE setting_value = setting_value;

INSERT INTO website_settings (setting_key, setting_value) VALUES
('footer_social_linkedin', 'https://www.linkedin.com/company/centexs')
ON DUPLICATE KEY UPDATE setting_value = setting_value;

INSERT INTO website_settings (setting_key, setting_value) VALUES
('show_upcoming_intakes', '1')
ON DUPLICATE KEY UPDATE setting_value = setting_value;

INSERT INTO website_settings (setting_key, setting_value) VALUES
('show_success_stories', '1')
ON DUPLICATE KEY UPDATE setting_value = setting_value;

INSERT INTO website_settings (setting_key, setting_value) VALUES
('show_announcements_home', '1')
ON DUPLICATE KEY UPDATE setting_value = setting_value;

USE centexs_itop_ims;

CREATE TABLE IF NOT EXISTS academies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(30) NOT NULL UNIQUE,
    name VARCHAR(190) NOT NULL,
    description TEXT,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS training_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL UNIQUE,
    description TEXT,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS locations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL UNIQUE,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS companies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(190) NOT NULL UNIQUE,
    location_id INT NULL,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (location_id) REFERENCES locations(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS institutions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(190) NOT NULL UNIQUE,
    location_id INT NULL,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (location_id) REFERENCES locations(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS professions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(160) NOT NULL UNIQUE,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP
);

ALTER TABLE courses
    ADD COLUMN IF NOT EXISTS academy_id INT NULL AFTER instructor_id,
    ADD COLUMN IF NOT EXISTS training_category_id INT NULL AFTER academy_id;

CREATE TABLE IF NOT EXISTS training_statistics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    academy_id INT NOT NULL,
    course_id INT NULL,
    course_name VARCHAR(190) NOT NULL,
    participants INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_training_stat (academy_id, course_name),
    FOREIGN KEY (academy_id) REFERENCES academies(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS participant_statistics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    academy_id INT NULL,
    category_id INT NULL,
    course_id INT NULL,
    company_id INT NULL,
    profession_id INT NULL,
    location_id INT NULL,
    report_year INT NULL,
    participant_count INT NOT NULL DEFAULT 0,
    statistic_type VARCHAR(60) NOT NULL DEFAULT 'sample',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_participant_statistics_year (report_year),
    INDEX idx_participant_statistics_type (statistic_type),
    FOREIGN KEY (academy_id) REFERENCES academies(id) ON DELETE SET NULL,
    FOREIGN KEY (category_id) REFERENCES training_categories(id) ON DELETE SET NULL,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE SET NULL,
    FOREIGN KEY (profession_id) REFERENCES professions(id) ON DELETE SET NULL,
    FOREIGN KEY (location_id) REFERENCES locations(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS yearly_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    report_year INT NOT NULL UNIQUE,
    participants INT NOT NULL DEFAULT 0,
    courses_count INT NOT NULL DEFAULT 0,
    certificates_issued INT NOT NULL DEFAULT 0,
    report_notes TEXT,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS dashboard_summary (
    id INT AUTO_INCREMENT PRIMARY KEY,
    metric_key VARCHAR(120) NOT NULL UNIQUE,
    metric_label VARCHAR(190) NOT NULL,
    metric_value DECIMAL(12,2) NOT NULL DEFAULT 0,
    metric_group VARCHAR(80) NOT NULL DEFAULT 'itop',
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO academies (code, name, description)
SELECT 'ADGEA', 'Aerospace, Digital and Green Energy Academy', 'ITOP academy for aerospace, digital technology, telecommunication, green energy, IoT, Industry 4.0, and AI education programmes.'
WHERE NOT EXISTS (SELECT 1 FROM academies WHERE code = 'ADGEA');
INSERT INTO academies (code, name, description)
SELECT 'IESGA', 'Industry and ESG Academy', 'ITOP academy for industry, ESG, electrical, hospitality, and manufacturing programmes.'
WHERE NOT EXISTS (SELECT 1 FROM academies WHERE code = 'IESGA');

INSERT IGNORE INTO training_categories (name) VALUES
('Telecommunication'), ('Digital Technology'), ('Green Energy'), ('Internet of Things (IoT)'), ('Industry 4.0'),
('Hospitality'), ('ESG'), ('Electrical'), ('Manufacturing'), ('AI & Education'), ('Security');

INSERT IGNORE INTO locations (name) VALUES
('Kuching'), ('Bintulu'), ('Lawas'), ('Kota Samarahan'), ('Kapit'), ('Sabah'), ('Brunei');

INSERT IGNORE INTO companies (name, location_id) VALUES
('Lyskcom Sdn. Bhd.', (SELECT id FROM locations WHERE name = 'Kuching')),
('East Field Technologies Sdn. Bhd.', (SELECT id FROM locations WHERE name = 'Kuching')),
('MKOM Sdn. Bhd.', (SELECT id FROM locations WHERE name = 'Kuching')),
('Empayar Kelana Sdn. Bhd.', (SELECT id FROM locations WHERE name = 'Kuching')),
('BCL Sdn. Bhd.', (SELECT id FROM locations WHERE name = 'Kuching')),
('Marzman Enterprise Sdn. Bhd.', (SELECT id FROM locations WHERE name = 'Kuching')),
('TelBru (Brunei)', (SELECT id FROM locations WHERE name = 'Brunei')),
('Sacofa Sdn. Bhd.', (SELECT id FROM locations WHERE name = 'Kuching')),
('Kaban Builders Sdn. Bhd.', (SELECT id FROM locations WHERE name = 'Kuching')),
('Silverridge Sdn. Bhd.', (SELECT id FROM locations WHERE name = 'Kuching')),
('Sea Telco Engineering Sdn. Bhd.', (SELECT id FROM locations WHERE name = 'Kuching')),
('Jalur Milenium Sdn. Bhd.', (SELECT id FROM locations WHERE name = 'Kuching')),
('Foursons Sdn. Bhd.', (SELECT id FROM locations WHERE name = 'Kuching')),
('Sarawak Energy Berhad', (SELECT id FROM locations WHERE name = 'Kuching')),
('SM Digital Innovation Sdn. Bhd.', (SELECT id FROM locations WHERE name = 'Kuching')),
('Valsa (Sarawak) Sdn. Bhd.', (SELECT id FROM locations WHERE name = 'Kuching')),
('Sarawak Media Group', (SELECT id FROM locations WHERE name = 'Kuching')),
('The Spring', (SELECT id FROM locations WHERE name = 'Kuching')),
('Sarawak ICT Association', (SELECT id FROM locations WHERE name = 'Kuching')),
('KPKP TVET', (SELECT id FROM locations WHERE name = 'Kuching')),
('PETRONAS', (SELECT id FROM locations WHERE name = 'Bintulu')),
('Politeknik', (SELECT id FROM locations WHERE name = 'Kuching')),
('SEAMEO VOCTECH Brunei', (SELECT id FROM locations WHERE name = 'Brunei'));

INSERT IGNORE INTO institutions (name, location_id) VALUES
('CENTEXS', (SELECT id FROM locations WHERE name = 'Kuching')),
('KPKP TVET', (SELECT id FROM locations WHERE name = 'Kuching')),
('Politeknik', (SELECT id FROM locations WHERE name = 'Kuching')),
('SEAMEO VOCTECH Brunei', (SELECT id FROM locations WHERE name = 'Brunei')),
('Sarawak ICT Association', (SELECT id FROM locations WHERE name = 'Kuching'));

INSERT IGNORE INTO professions (name) VALUES
('Director'), ('Managing Director'), ('Manager'), ('Project Manager'), ('Technical Director'), ('Engineer'),
('Assistant Engineer'), ('Supervisor'), ('Technician'), ('Linesman'), ('Wireman'), ('Executive'),
('Procurement Officer'), ('Professor'), ('Lecturer'), ('TVET Lecturer'), ('Vocational Training Officer (Pegawai Latihan Vokasional)');

INSERT INTO dashboard_summary (metric_key, metric_label, metric_value, metric_group) VALUES
('programme_duration_start_year', 'Programme Start Year', 2018, 'programme'),
('programme_duration_end_year', 'Programme End Year', 2025, 'programme'),
('total_participants', 'Total Participants', 862, 'programme'),
('adgea_participants', 'ADGEA Participants', 522, 'academy'),
('iesga_participants', 'IESGA Participants', 340, 'academy'),
('training_completion_rate', 'Training Completion Rate', 100, 'training')
ON DUPLICATE KEY UPDATE metric_value = VALUES(metric_value), metric_label = VALUES(metric_label), metric_group = VALUES(metric_group);

INSERT INTO yearly_reports (report_year, participants, courses_count, certificates_issued, report_notes) VALUES
(2018, 45, 3, 45, 'ITOP launch period, October to December 2018.'),
(2019, 96, 8, 96, 'Expanded telecommunication and digital training.'),
(2020, 78, 7, 78, 'Continued delivery through restricted operating conditions.'),
(2021, 105, 9, 105, 'Growth in digital and industry training.'),
(2022, 132, 10, 132, 'Expanded academy participation.'),
(2023, 156, 11, 156, 'Peak programme activity across ADGEA and IESGA.'),
(2024, 148, 10, 148, 'Sustained delivery across technical categories.'),
(2025, 102, 8, 102, 'Programme data through November 2025.')
ON DUPLICATE KEY UPDATE participants = VALUES(participants), courses_count = VALUES(courses_count), certificates_issued = VALUES(certificates_issued), report_notes = VALUES(report_notes);

INSERT INTO courses (title, category, description, academy_id, training_category_id, capacity, max_participants, status, course_status, fee)
SELECT course_title, category_name, CONCAT('ITOP master course: ', course_title), a.id, tc.id, 30, 30, 'published', 'published', 0.00
FROM (
    SELECT 'Introduction to Telecommunication Infrastructure' course_title, 'Telecommunication' category_name, 'ADGEA' academy_code UNION ALL
    SELECT 'Telecommunication Infrastructure', 'Telecommunication', 'ADGEA' UNION ALL
    SELECT 'Fiber Optic Splicing', 'Telecommunication', 'ADGEA' UNION ALL
    SELECT 'FTTX OSP Hardware Installation', 'Telecommunication', 'ADGEA' UNION ALL
    SELECT 'FTTX OSP Training (TelBru, Brunei)', 'Telecommunication', 'ADGEA' UNION ALL
    SELECT 'Wireless Base Station and Antenna Subsystem Training', 'Telecommunication', 'ADGEA' UNION ALL
    SELECT 'Wireless Cellular Hardware Installation', 'Telecommunication', 'ADGEA' UNION ALL
    SELECT 'Wireless and Microwave Hardware Installation', 'Telecommunication', 'ADGEA' UNION ALL
    SELECT '5G Training', 'Telecommunication', 'ADGEA' UNION ALL
    SELECT 'Datacom', 'Digital Technology', 'ADGEA' UNION ALL
    SELECT 'Internet of Things (IoT) Foundation & Application', 'Internet of Things (IoT)', 'ADGEA' UNION ALL
    SELECT 'Digital Marketing', 'Digital Technology', 'ADGEA' UNION ALL
    SELECT 'Digital Byte Size', 'Digital Technology', 'ADGEA' UNION ALL
    SELECT 'Industry 4.0', 'Industry 4.0', 'ADGEA' UNION ALL
    SELECT 'IR4.0', 'Industry 4.0', 'ADGEA' UNION ALL
    SELECT 'Smart Manufacturing', 'Industry 4.0', 'ADGEA' UNION ALL
    SELECT 'IR4.0 Applications to Enhance Teaching and Learning Through Cloud Services', 'Industry 4.0', 'ADGEA' UNION ALL
    SELECT 'Online Applications for Content Development', 'AI & Education', 'ADGEA' UNION ALL
    SELECT 'Celik AI Memacu Pendidikan', 'AI & Education', 'ADGEA' UNION ALL
    SELECT 'Canva dalam Pendidikan', 'AI & Education', 'ADGEA' UNION ALL
    SELECT 'Immersive Technologies (EON-XR, Photogrammetry & 360)', 'Digital Technology', 'ADGEA' UNION ALL
    SELECT 'Green Energy', 'Green Energy', 'ADGEA' UNION ALL
    SELECT 'Greenhouse Gas (GHG) Accounting & Reporting', 'ESG', 'IESGA' UNION ALL
    SELECT 'Chargeman L1', 'Electrical', 'IESGA' UNION ALL
    SELECT 'Chargeman L2 (O/H)', 'Electrical', 'IESGA' UNION ALL
    SELECT 'Chargeman H2 (O/H)', 'Electrical', 'IESGA' UNION ALL
    SELECT 'Hospitality & Service Quality Training', 'Hospitality', 'IESGA' UNION ALL
    SELECT 'Healthy Menu Preparation (Penyediaan Menu Sihat)', 'Hospitality', 'IESGA' UNION ALL
    SELECT 'Fiberglass Composite Boat Building', 'Manufacturing', 'IESGA' UNION ALL
    SELECT 'Fiberglass Boat Training Programme', 'Manufacturing', 'IESGA' UNION ALL
    SELECT 'Asas Simen Ferro', 'Manufacturing', 'IESGA' UNION ALL
    SELECT 'Data Security Course for Resident Office Samarahan', 'Security', 'ADGEA'
) seed
JOIN academies a ON a.code = seed.academy_code
JOIN training_categories tc ON tc.name = seed.category_name
WHERE NOT EXISTS (SELECT 1 FROM courses c WHERE c.title = seed.course_title);

UPDATE courses c
JOIN training_categories tc ON tc.name = c.category
SET c.training_category_id = tc.id
WHERE c.training_category_id IS NULL;

UPDATE courses c SET c.academy_id = (SELECT id FROM academies WHERE code = 'ADGEA')
WHERE c.academy_id IS NULL AND c.category IN ('Telecommunication', 'Digital Technology', 'Green Energy', 'Internet of Things (IoT)', 'Industry 4.0', 'AI & Education', 'Security');
UPDATE courses c SET c.academy_id = (SELECT id FROM academies WHERE code = 'IESGA')
WHERE c.academy_id IS NULL AND c.category IN ('ESG', 'Electrical', 'Hospitality', 'Manufacturing');

INSERT INTO training_statistics (academy_id, course_id, course_name, participants)
SELECT a.id, c.id, seed.course_name, seed.participants
FROM (
    SELECT 'ADGEA' academy_code, 'Fiber Optic Splicing' course_name, 71 participants UNION ALL
    SELECT 'ADGEA', 'Antenna Subsystem', 17 UNION ALL
    SELECT 'ADGEA', 'FTTX OSP Hardware Installation', 13 UNION ALL
    SELECT 'ADGEA', 'Wireless & Microwave Hardware Installation', 43 UNION ALL
    SELECT 'ADGEA', '5G Training', 21 UNION ALL
    SELECT 'ADGEA', 'Datacom', 53 UNION ALL
    SELECT 'ADGEA', 'Digital Marketing', 10 UNION ALL
    SELECT 'ADGEA', 'Immersive Technologies (EON-XR, Photogrammetry & 360)', 45 UNION ALL
    SELECT 'ADGEA', 'Wireless Cellular Hardware Installation', 50 UNION ALL
    SELECT 'ADGEA', 'IoT Foundation & Application', 10 UNION ALL
    SELECT 'ADGEA', 'Green Energy', 5 UNION ALL
    SELECT 'ADGEA', 'Smart Manufacturing', 19 UNION ALL
    SELECT 'ADGEA', 'IR4.0 Applications Programme', 60 UNION ALL
    SELECT 'ADGEA', 'Telecommunication Infrastructure', 35 UNION ALL
    SELECT 'ADGEA', 'Wireless Base Station Training', 10 UNION ALL
    SELECT 'ADGEA', 'Celik AI Memacu Pendidikan', 29 UNION ALL
    SELECT 'ADGEA', 'Canva dalam Pendidikan', 31 UNION ALL
    SELECT 'IESGA', 'Chargeman L1', 11 UNION ALL
    SELECT 'IESGA', 'Chargeman L2 (O/H)', 78 UNION ALL
    SELECT 'IESGA', 'Chargeman H2 (O/H)', 11 UNION ALL
    SELECT 'IESGA', 'Asas Simen Ferro', 18 UNION ALL
    SELECT 'IESGA', 'Hospitality & Service Quality Training', 166 UNION ALL
    SELECT 'IESGA', 'GHG Accounting & Reporting', 3 UNION ALL
    SELECT 'IESGA', 'Fiberglass Boat Training Programme', 20 UNION ALL
    SELECT 'IESGA', 'Healthy Menu Preparation', 33
) seed
JOIN academies a ON a.code = seed.academy_code
LEFT JOIN courses c ON c.title = seed.course_name
ON DUPLICATE KEY UPDATE participants = VALUES(participants), course_id = VALUES(course_id);

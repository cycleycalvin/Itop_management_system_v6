<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Content extends Model
{
    public function announcements(bool $publicOnly = false, ?string $role = null, ?int $userId = null): array
    {
        $sql = 'SELECT a.*, u.name AS author_name FROM announcements a LEFT JOIN users u ON u.id = a.created_by';
        $params = [];

        if ($publicOnly) {
            $sql .= ' WHERE a.is_public = 1';
        } elseif ($role === 'trainee' && $userId !== null) {
            $sql .= ' WHERE a.is_public = 1 OR a.created_by IN (
                SELECT DISTINCT c.instructor_id 
                FROM enrolments e 
                JOIN courses c ON c.id = e.course_id 
                WHERE e.trainee_id = ? AND c.instructor_id IS NOT NULL
            )';
            $params[] = $userId;
        } elseif ($role === 'instructor' && $userId !== null) {
            $sql .= ' WHERE a.is_public = 1 OR a.created_by = ?';
            $params[] = $userId;
        }

        $sql .= ' ORDER BY a.published_at DESC, a.created_at DESC LIMIT 10';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function addAnnouncement(array $data): void
    {
        $stmt = $this->db->prepare('INSERT INTO announcements (title, body, is_public, created_by, published_at) VALUES (?, ?, ?, ?, NOW())');
        $stmt->execute([$data['title'], $data['body'], $data['is_public'], $data['created_by']]);
    }

    public function stats(): array
    {
        $queries = [
            'total_participants' => 'SELECT COALESCE((SELECT SUM(participants) FROM training_statistics), 0) + (SELECT COUNT(*) FROM users JOIN roles ON roles.id = users.role_id WHERE roles.slug = "trainee")',
            'total_courses' => 'SELECT COUNT(DISTINCT title) FROM courses',
            'total_companies' => 'SELECT COUNT(*) FROM companies',
            'total_institutions' => 'SELECT COUNT(*) FROM institutions',
            'certificates_issued' => 'SELECT COUNT(*) FROM certificates',
            'total_revenue' => 'SELECT COALESCE(SUM(courses.fee), 0) FROM enrolments JOIN courses ON courses.id = enrolments.course_id WHERE enrolments.status IN ("active","completed")',
        ];
        $stats = [];
        foreach ($queries as $key => $sql) {
            $stats[$key] = (float) $this->db->query($sql)->fetchColumn();
        }
        return $stats;
    }

    public function dashboardAnalytics(): array
    {
        // Establish table structure if missing
        $this->db->exec("CREATE TABLE IF NOT EXISTS custom_analytics (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(190) NOT NULL,
            chart_type VARCHAR(50) NOT NULL DEFAULT 'bar',
            data_source VARCHAR(100) NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        $this->db->exec("CREATE TABLE IF NOT EXISTS custom_analytics_data (
            id INT AUTO_INCREMENT PRIMARY KEY,
            custom_analytic_id INT NOT NULL,
            label VARCHAR(190) NOT NULL,
            value INT NOT NULL DEFAULT 0,
            FOREIGN KEY (custom_analytic_id) REFERENCES custom_analytics(id) ON DELETE CASCADE
        )");

        $customCharts = $this->db->query("SELECT * FROM custom_analytics ORDER BY id ASC")->fetchAll();
        $customChartsCompiled = [];
        foreach ($customCharts as $cc) {
            $data = [];
            if ($cc['data_source'] === 'academy') {
                $data = $this->db->query('SELECT a.code AS label, SUM(ts.participants) AS value FROM training_statistics ts JOIN academies a ON a.id = ts.academy_id GROUP BY a.id ORDER BY value DESC')->fetchAll();
            } elseif ($cc['data_source'] === 'category') {
                $data = $this->db->query('SELECT tc.name AS label, SUM(ps.participant_count) AS value FROM participant_statistics ps JOIN training_categories tc ON tc.id = ps.category_id WHERE ps.statistic_type = "category" GROUP BY tc.id ORDER BY value DESC')->fetchAll();
            } elseif ($cc['data_source'] === 'company') {
                $data = $this->db->query('SELECT co.name AS label, SUM(ps.participant_count) AS value FROM participant_statistics ps JOIN companies co ON co.id = ps.company_id WHERE ps.statistic_type = "company" GROUP BY co.id ORDER BY value DESC LIMIT 10')->fetchAll();
            } elseif ($cc['data_source'] === 'profession') {
                $data = $this->db->query('SELECT p.name AS label, SUM(ps.participant_count) AS value FROM participant_statistics ps JOIN professions p ON p.id = ps.profession_id WHERE ps.statistic_type = "profession" GROUP BY p.id ORDER BY value DESC LIMIT 10')->fetchAll();
            } elseif ($cc['data_source'] === 'custom_manual') {
                $stmt = $this->db->prepare('SELECT id, label, value FROM custom_analytics_data WHERE custom_analytic_id = ? ORDER BY label ASC');
                $stmt->execute([$cc['id']]);
                $data = $stmt->fetchAll();
            }
            $customChartsCompiled[] = [
                'id' => (int) $cc['id'],
                'title' => $cc['title'],
                'chart_type' => $cc['chart_type'],
                'data_source' => $cc['data_source'],
                'data' => $data
            ];
        }

        return [
            'programme' => $this->db->query('SELECT a.code AS label, SUM(ts.participants) AS value FROM training_statistics ts JOIN academies a ON a.id = ts.academy_id GROUP BY a.id ORDER BY value DESC')->fetchAll(),
            'course_participants' => $this->db->query('SELECT course_name AS label, participants AS value FROM training_statistics ORDER BY participants DESC LIMIT 12')->fetchAll(),
            'years' => $this->db->query('SELECT report_year AS label, participants AS value FROM yearly_reports ORDER BY report_year')->fetchAll(),
            'monthly' => $this->db->query('SELECT DATE_FORMAT(created_at, "%Y-%m") AS label, COUNT(*) AS value FROM enrolments GROUP BY DATE_FORMAT(created_at, "%Y-%m") ORDER BY label DESC LIMIT 12')->fetchAll(),
            'categories' => $this->db->query('SELECT tc.name AS label, SUM(ps.participant_count) AS value FROM participant_statistics ps JOIN training_categories tc ON tc.id = ps.category_id WHERE ps.statistic_type = "category" GROUP BY tc.id ORDER BY value DESC')->fetchAll(),
            'companies' => $this->db->query('SELECT co.name AS label, SUM(ps.participant_count) AS value FROM participant_statistics ps JOIN companies co ON co.id = ps.company_id WHERE ps.statistic_type = "company" GROUP BY co.id ORDER BY value DESC LIMIT 10')->fetchAll(),
            'professions' => $this->db->query('SELECT p.name AS label, SUM(ps.participant_count) AS value FROM participant_statistics ps JOIN professions p ON p.id = ps.profession_id WHERE ps.statistic_type = "profession" GROUP BY p.id ORDER BY value DESC LIMIT 10')->fetchAll(),
            'completion' => $this->db->query('SELECT "Training Completion Rate" AS label, COALESCE((SELECT metric_value FROM dashboard_summary WHERE metric_key = "training_completion_rate"), 100) AS value')->fetchAll(),
            'certificates' => $this->db->query('SELECT DATE_FORMAT(COALESCE(issue_date, issued_at), "%Y-%m") AS label, COUNT(*) AS value FROM certificates GROUP BY DATE_FORMAT(COALESCE(issue_date, issued_at), "%Y-%m") ORDER BY label DESC LIMIT 12')->fetchAll(),
            'popularity' => $this->db->query('SELECT course_name AS label, participants AS value FROM training_statistics ORDER BY participants DESC LIMIT 8')->fetchAll(),
            'custom_charts' => $customChartsCompiled,
        ];
    }

    public function recentActivity(): array
    {
        return [
            'registrations' => $this->db->query('SELECT users.name, users.email, users.created_at FROM users JOIN roles ON roles.id = users.role_id WHERE roles.slug = "trainee" ORDER BY users.created_at DESC LIMIT 5')->fetchAll(),
            'enrolments' => $this->db->query('SELECT e.created_at, u.name AS trainee_name, c.title AS course_title, e.status FROM enrolments e JOIN users u ON u.id = e.trainee_id JOIN courses c ON c.id = e.course_id ORDER BY e.created_at DESC LIMIT 5')->fetchAll(),
            'certificates' => $this->db->query('SELECT cert.certificate_number, cert.certificate_no, COALESCE(cert.issue_date, cert.issued_at) AS issued_on, u.name AS trainee_name, c.title AS course_title FROM certificates cert JOIN users u ON u.id = cert.trainee_id JOIN courses c ON c.id = cert.course_id ORDER BY COALESCE(cert.issue_date, cert.issued_at) DESC LIMIT 5')->fetchAll(),
            'evaluations' => $this->db->query('SELECT e.*, u.name AS trainee_name, c.title AS course_title FROM evaluations e JOIN users u ON u.id = e.trainee_id JOIN courses c ON c.id = e.course_id WHERE e.comments IS NULL AND e.feedback IS NULL ORDER BY e.created_at DESC LIMIT 5')->fetchAll(),
        ];
    }

    public function trends(): array
    {
        $trends = [];
        
        // 1. Total Participants (Trainees)
        $currPart = (float) $this->db->query('SELECT COUNT(*) FROM users JOIN roles ON roles.id = users.role_id WHERE roles.slug = "trainee" AND users.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)')->fetchColumn();
        $prevPart = (float) $this->db->query('SELECT COUNT(*) FROM users JOIN roles ON roles.id = users.role_id WHERE roles.slug = "trainee" AND users.created_at BETWEEN DATE_SUB(NOW(), INTERVAL 60 DAY) AND DATE_SUB(NOW(), INTERVAL 30 DAY)')->fetchColumn();
        $trends['total_participants'] = $this->calculatePercentageChange($currPart, $prevPart);

        // 2. Total Courses
        $currCourses = (float) $this->db->query('SELECT COUNT(*) FROM courses WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)')->fetchColumn();
        $prevCourses = (float) $this->db->query('SELECT COUNT(*) FROM courses WHERE created_at BETWEEN DATE_SUB(NOW(), INTERVAL 60 DAY) AND DATE_SUB(NOW(), INTERVAL 30 DAY)')->fetchColumn();
        $trends['total_courses'] = $this->calculatePercentageChange($currCourses, $prevCourses);

        // 3. Total Companies
        $currCompanies = (float) $this->db->query('SELECT COUNT(*) FROM companies WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)')->fetchColumn();
        $prevCompanies = (float) $this->db->query('SELECT COUNT(*) FROM companies WHERE created_at BETWEEN DATE_SUB(NOW(), INTERVAL 60 DAY) AND DATE_SUB(NOW(), INTERVAL 30 DAY)')->fetchColumn();
        $trends['total_companies'] = $this->calculatePercentageChange($currCompanies, $prevCompanies);

        // 4. Total Institutions
        $currInst = (float) $this->db->query('SELECT COUNT(*) FROM institutions WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)')->fetchColumn();
        $prevInst = (float) $this->db->query('SELECT COUNT(*) FROM institutions WHERE created_at BETWEEN DATE_SUB(NOW(), INTERVAL 60 DAY) AND DATE_SUB(NOW(), INTERVAL 30 DAY)')->fetchColumn();
        $trends['total_institutions'] = $this->calculatePercentageChange($currInst, $prevInst);

        // 5. Certificates Issued
        $currCert = (float) $this->db->query('SELECT COUNT(*) FROM certificates WHERE COALESCE(issue_date, issued_at) >= DATE_SUB(NOW(), INTERVAL 30 DAY)')->fetchColumn();
        $prevCert = (float) $this->db->query('SELECT COUNT(*) FROM certificates WHERE COALESCE(issue_date, issued_at) BETWEEN DATE_SUB(NOW(), INTERVAL 60 DAY) AND DATE_SUB(NOW(), INTERVAL 30 DAY)')->fetchColumn();
        $trends['certificates_issued'] = $this->calculatePercentageChange($currCert, $prevCert);

        // 6. Total Revenue
        $currRev = (float) $this->db->query('SELECT COALESCE(SUM(courses.fee), 0) FROM enrolments JOIN courses ON courses.id = enrolments.course_id WHERE enrolments.status IN ("active","completed") AND enrolments.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)')->fetchColumn();
        $prevRev = (float) $this->db->query('SELECT COALESCE(SUM(courses.fee), 0) FROM enrolments JOIN courses ON courses.id = enrolments.course_id WHERE enrolments.status IN ("active","completed") AND enrolments.created_at BETWEEN DATE_SUB(NOW(), INTERVAL 60 DAY) AND DATE_SUB(NOW(), INTERVAL 30 DAY)')->fetchColumn();
        $trends['total_revenue'] = $this->calculatePercentageChange($currRev, $prevRev);

        return $trends;
    }

    public function filteredAnalytics(array $filters = []): array
    {
        $where = ' WHERE 1=1';
        $params = [];

        if (!empty($filters['academy_id'])) {
            $where .= ' AND c.academy_id = ?';
            $params[] = (int) $filters['academy_id'];
        }
        if (!empty($filters['course_id'])) {
            $where .= ' AND e.course_id = ?';
            $params[] = (int) $filters['course_id'];
        }
        if (!empty($filters['instructor_id'])) {
            $where .= ' AND c.instructor_id = ?';
            $params[] = (int) $filters['instructor_id'];
        }
        if (!empty($filters['start_date'])) {
            $where .= ' AND e.created_at >= ?';
            $params[] = $filters['start_date'] . ' 00:00:00';
        }
        if (!empty($filters['end_date'])) {
            $where .= ' AND e.created_at <= ?';
            $params[] = $filters['end_date'] . ' 23:59:59';
        }

        // 1. Programme (Academy participants)
        $progSql = 'SELECT a.code AS label, COUNT(e.id) AS value 
                    FROM enrolments e 
                    JOIN courses c ON c.id = e.course_id 
                    JOIN academies a ON a.id = c.academy_id' 
                    . $where . ' GROUP BY a.id ORDER BY value DESC';
        $stmt = $this->db->prepare($progSql);
        $stmt->execute($params);
        $programme = $stmt->fetchAll();

        // 2. Course participants
        $courseSql = 'SELECT c.title AS label, COUNT(e.id) AS value 
                      FROM enrolments e 
                      JOIN courses c ON c.id = e.course_id' 
                      . $where . ' GROUP BY c.id ORDER BY value DESC LIMIT 12';
        $stmt = $this->db->prepare($courseSql);
        $stmt->execute($params);
        $course_participants = $stmt->fetchAll();

        // 3. Monthly Trend
        $monthlySql = 'SELECT DATE_FORMAT(e.created_at, "%Y-%m") AS label, COUNT(e.id) AS value 
                       FROM enrolments e 
                       JOIN courses c ON c.id = e.course_id' 
                       . $where . ' GROUP BY label ORDER BY label DESC LIMIT 12';
        $stmt = $this->db->prepare($monthlySql);
        $stmt->execute($params);
        $monthly = $stmt->fetchAll();

        // 4. Categories
        $catSql = 'SELECT tc.name AS label, COUNT(e.id) AS value 
                   FROM enrolments e 
                   JOIN courses c ON c.id = e.course_id 
                   JOIN training_categories tc ON tc.id = c.training_category_id' 
                   . $where . ' GROUP BY tc.id ORDER BY value DESC';
        $stmt = $this->db->prepare($catSql);
        $stmt->execute($params);
        $categories = $stmt->fetchAll();

        // 5. Completion
        $compSql = 'SELECT e.status AS label, COUNT(e.id) AS value 
                    FROM enrolments e 
                    JOIN courses c ON c.id = e.course_id' 
                    . $where . ' GROUP BY e.status';
        $stmt = $this->db->prepare($compSql);
        $stmt->execute($params);
        $completion = $stmt->fetchAll();

        // 6. Certificates
        $certWhere = str_replace('e.created_at', 'cert.issued_at', $where);
        $certWhere = str_replace('e.course_id', 'cert.course_id', $certWhere);
        $certSql = 'SELECT DATE_FORMAT(COALESCE(cert.issue_date, cert.issued_at), "%Y-%m") AS label, COUNT(cert.id) AS value 
                    FROM certificates cert 
                    JOIN courses c ON c.id = cert.course_id' 
                    . $certWhere . ' GROUP BY label ORDER BY label DESC LIMIT 12';
        $stmt = $this->db->prepare($certSql);
        $stmt->execute($params);
        $certificates = $stmt->fetchAll();

        // 7. Years
        $yearsSql = 'SELECT DATE_FORMAT(e.created_at, "%Y") AS label, COUNT(e.id) AS value 
                     FROM enrolments e 
                     JOIN courses c ON c.id = e.course_id' 
                     . $where . ' GROUP BY label ORDER BY label';
        $stmt = $this->db->prepare($yearsSql);
        $stmt->execute($params);
        $years = $stmt->fetchAll();

        return [
            'programme' => $programme,
            'course_participants' => $course_participants,
            'monthly' => $monthly,
            'categories' => $categories,
            'completion' => $completion,
            'certificates' => $certificates,
            'years' => $years
        ];
    }

    private function calculatePercentageChange(float $current, float $previous): float
    {
        if ($previous == 0) {
            return $current > 0 ? 100.0 : 0.0;
        }
        return (($current - $previous) / $previous) * 100.0;
    }
}


<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class MasterData extends Model
{
    private const TABLES = [
        'academies' => ['id' => 'id', 'label' => 'name', 'fields' => ['code', 'name', 'description', 'status']],
        'training_categories' => ['id' => 'id', 'label' => 'name', 'fields' => ['name', 'description', 'status']],
        'companies' => ['id' => 'id', 'label' => 'name', 'fields' => ['name', 'location_id', 'status']],
        'institutions' => ['id' => 'id', 'label' => 'name', 'fields' => ['name', 'location_id', 'status']],
        'locations' => ['id' => 'id', 'label' => 'name', 'fields' => ['name', 'status']],
        'professions' => ['id' => 'id', 'label' => 'name', 'fields' => ['name', 'status']],
    ];

    public function tables(): array
    {
        return self::TABLES;
    }

    public function list(string $table): array
    {
        $this->assertTable($table);
        return $this->db->query('SELECT * FROM ' . $table . ' ORDER BY ' . self::TABLES[$table]['label'])->fetchAll();
    }

    public function find(string $table, int $id): ?array
    {
        $this->assertTable($table);
        $pk = self::TABLES[$table]['id'];
        $stmt = $this->db->prepare('SELECT * FROM ' . $table . ' WHERE ' . $pk . ' = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function save(string $table, array $data): int
    {
        $this->assertTable($table);
        $pk = self::TABLES[$table]['id'];
        $fields = self::TABLES[$table]['fields'];
        $values = [];
        foreach ($fields as $field) {
            $values[$field] = $data[$field] ?? null;
        }

        if (!empty($data[$pk])) {
            $assignments = implode(', ', array_map(static fn (string $field): string => $field . ' = ?', $fields));
            $stmt = $this->db->prepare('UPDATE ' . $table . ' SET ' . $assignments . ', updated_at = NOW() WHERE ' . $pk . ' = ?');
            $stmt->execute([...array_values($values), $data[$pk]]);
            return (int) $data[$pk];
        }

        $columns = implode(', ', $fields);
        $placeholders = implode(', ', array_fill(0, count($fields), '?'));
        $stmt = $this->db->prepare('INSERT INTO ' . $table . ' (' . $columns . ') VALUES (' . $placeholders . ')');
        $stmt->execute(array_values($values));
        return (int) $this->db->lastInsertId();
    }

    public function delete(string $table, int $id): void
    {
        $this->assertTable($table);
        $pk = self::TABLES[$table]['id'];
        $stmt = $this->db->prepare('DELETE FROM ' . $table . ' WHERE ' . $pk . ' = ?');
        $stmt->execute([$id]);
    }

    public function statistics(): array
    {
        return [
            'training' => $this->db->query('SELECT ts.*, a.code AS academy_code, a.name AS academy_name, c.title AS course_title FROM training_statistics ts JOIN academies a ON a.id = ts.academy_id LEFT JOIN courses c ON c.id = ts.course_id ORDER BY a.code, ts.participants DESC')->fetchAll(),
            'participant' => $this->db->query('SELECT ps.*, a.code AS academy_code, tc.name AS category_name, c.title AS course_title, co.name AS company_name, p.name AS profession_name FROM participant_statistics ps LEFT JOIN academies a ON a.id = ps.academy_id LEFT JOIN training_categories tc ON tc.id = ps.category_id LEFT JOIN courses c ON c.id = ps.course_id LEFT JOIN companies co ON co.id = ps.company_id LEFT JOIN professions p ON p.id = ps.profession_id ORDER BY ps.report_year, ps.participant_count DESC')->fetchAll(),
            'yearly' => $this->db->query('SELECT * FROM yearly_reports ORDER BY report_year')->fetchAll(),
            'summary' => $this->db->query('SELECT * FROM dashboard_summary ORDER BY metric_label')->fetchAll(),
        ];
    }

    public function saveTrainingStatistic(array $data): int
    {
        if (!empty($data['id'])) {
            $stmt = $this->db->prepare('UPDATE training_statistics SET academy_id=?, course_id=?, course_name=?, participants=?, updated_at=NOW() WHERE id=?');
            $stmt->execute([$data['academy_id'], $data['course_id'] ?: null, $data['course_name'], $data['participants'], $data['id']]);
            return (int) $data['id'];
        }
        $stmt = $this->db->prepare('INSERT INTO training_statistics (academy_id, course_id, course_name, participants) VALUES (?, ?, ?, ?)');
        $stmt->execute([$data['academy_id'], $data['course_id'] ?: null, $data['course_name'], $data['participants']]);
        return (int) $this->db->lastInsertId();
    }

    public function deleteTrainingStatistic(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM training_statistics WHERE id = ?');
        $stmt->execute([$id]);
    }

    private function assertTable(string $table): void
    {
        if (!isset(self::TABLES[$table])) {
            throw new \InvalidArgumentException('Unsupported master data table.');
        }
    }
}

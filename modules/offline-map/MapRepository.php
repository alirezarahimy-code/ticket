<?php
declare(strict_types=1);

/**
 * Persistence for user-authored map features. Base map tiles are read-only,
 * versioned files and are intentionally never stored in this table.
 */
final class OfflineMapRepository
{
    public function __construct(private PDO $pdo) {}

    public function ready(): bool
    {
        try {
            $this->pdo->query('SELECT 1 FROM offline_map_features LIMIT 1');
            return true;
        } catch (Throwable) {
            return false;
        }
    }

    public function search(string $query = '', int $limit = 200): array
    {
        $limit = max(1, min(500, $limit));
        if ($query === '') {
            $stmt = $this->pdo->query('SELECT id,title,description,feature_type,geometry_json,properties_json,created_by,created_at,updated_at FROM offline_map_features ORDER BY updated_at DESC LIMIT ' . $limit);
        } else {
            $stmt = $this->pdo->prepare('SELECT id,title,description,feature_type,geometry_json,properties_json,created_by,created_at,updated_at FROM offline_map_features WHERE title LIKE ? OR description LIKE ? ORDER BY updated_at DESC LIMIT ' . $limit);
            $like = '%' . $query . '%';
            $stmt->execute([$like, $like]);
        }
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        foreach ($rows as &$row) {
            $row['id'] = (int) $row['id'];
            $row['created_by'] = (int) $row['created_by'];
            $row['geometry'] = json_decode((string) $row['geometry_json'], true) ?: null;
            $row['properties'] = json_decode((string) ($row['properties_json'] ?? '{}'), true) ?: [];
            unset($row['geometry_json'], $row['properties_json']);
        }
        unset($row);
        return $rows;
    }

    public function create(string $title, string $description, array $geometry, int $userId): int
    {
        $geometryJson = json_encode($geometry, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        $stmt = $this->pdo->prepare("INSERT INTO offline_map_features (title,description,feature_type,geometry_json,properties_json,created_by) VALUES (?,?, 'point', ?, '{}', ?)");
        $stmt->execute([$title, $description, $geometryJson, $userId]);
        return (int) $this->pdo->lastInsertId();
    }

    public function delete(int $id, int $userId, bool $canAdmin): bool
    {
        $sql = 'DELETE FROM offline_map_features WHERE id = ?' . ($canAdmin ? '' : ' AND created_by = ?');
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($canAdmin ? [$id] : [$id, $userId]);
        return $stmt->rowCount() > 0;
    }
}

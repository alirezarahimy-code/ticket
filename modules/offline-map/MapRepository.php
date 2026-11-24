<?php
declare(strict_types=1);

/**
 * User-authored GIS features. Base map data is immutable and versioned separately.
 * Geometry is GeoJSON in WGS84 (EPSG:4326).
 */
final class OfflineMapRepository
{
    public function __construct(private PDO $pdo) {}

    public function ready(): bool
    {
        try {
            $this->pdo->query('SELECT 1 FROM offline_map_features LIMIT 1');
            $this->pdo->query('SELECT 1 FROM offline_map_feature_attachments LIMIT 1');
            return true;
        } catch (Throwable) {
            return false;
        }
    }

    public function search(string $query = '', int $limit = 500, ?int $ownerId = null): array
    {
        $limit = max(1, min(1000, $limit));
        $where = [];
        $params = [];
        if ($query !== '') {
            $where[] = '(title LIKE ? OR description LIKE ?)';
            $like = '%' . $query . '%';
            $params[] = $like;
            $params[] = $like;
        }
        if ($ownerId !== null) {
            $where[] = 'created_by = ?';
            $params[] = $ownerId;
        }
        $sql = 'SELECT id,title,description,feature_type,geometry_json,properties_json,created_by,updated_by,created_at,updated_at FROM offline_map_features'
            . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
            . ' ORDER BY updated_at DESC LIMIT ' . $limit;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        foreach ($rows as &$row) {
            $row['id'] = (int) $row['id'];
            $row['created_by'] = (int) $row['created_by'];
            $row['updated_by'] = $row['updated_by'] === null ? null : (int) $row['updated_by'];
            $row['geometry'] = json_decode((string) $row['geometry_json'], true);
            $row['properties'] = json_decode((string) ($row['properties_json'] ?? '{}'), true) ?: [];
            unset($row['geometry_json'], $row['properties_json']);
        }
        unset($row);
        return $rows;
    }

    public function find(int $id): ?array
    {
        // Targeted lookup avoids scanning the feature list.
        $stmt = $this->pdo->prepare('SELECT id,title,description,feature_type,geometry_json,properties_json,created_by,updated_by,created_at,updated_at FROM offline_map_features WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return null;
        $row['id'] = (int) $row['id'];
        $row['created_by'] = (int) $row['created_by'];
        $row['updated_by'] = $row['updated_by'] === null ? null : (int) $row['updated_by'];
        $row['geometry'] = json_decode((string) $row['geometry_json'], true);
        $row['properties'] = json_decode((string) ($row['properties_json'] ?? '{}'), true) ?: [];
        unset($row['geometry_json'], $row['properties_json']);
        return $row;
    }

    public function create(string $title, string $description, array $geometry, array $properties, int $userId): int
    {
        $type = strtolower((string) ($geometry['type'] ?? ''));
        $geometryJson = json_encode($geometry, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        $propertiesJson = json_encode($properties, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        $stmt = $this->pdo->prepare('INSERT INTO offline_map_features (title,description,feature_type,geometry_json,properties_json,created_by,updated_by) VALUES (?,?,?,?,?,?,?)');
        $stmt->execute([$title, $description, $type, $geometryJson, $propertiesJson, $userId, $userId]);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, string $title, string $description, array $geometry, array $properties, int $userId, bool $canAdmin): bool
    {
        $type = strtolower((string) ($geometry['type'] ?? ''));
        $sql = 'UPDATE offline_map_features SET title=?,description=?,feature_type=?,geometry_json=?,properties_json=?,updated_by=? WHERE id=?'
            . ($canAdmin ? '' : ' AND created_by=?');
        $stmt = $this->pdo->prepare($sql);
        $values = [
            $title, $description, $type,
            json_encode($geometry, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
            json_encode($properties, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
            $userId, $id
        ];
        if (!$canAdmin) $values[] = $userId;
        $stmt->execute($values);
        return $stmt->rowCount() > 0;
    }

    public function addAttachment(int $featureId, string $originalName, string $storedName, string $mimeType, int $size, int $userId): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO offline_map_feature_attachments (feature_id,original_name,stored_name,mime_type,size_bytes,uploaded_by) VALUES (?,?,?,?,?,?)');
        $stmt->execute([$featureId, $originalName, $storedName, $mimeType, $size, $userId]);
        return (int) $this->pdo->lastInsertId();
    }

    public function attachments(int $featureId): array
    {
        $stmt = $this->pdo->prepare('SELECT id,feature_id,original_name,mime_type,size_bytes,uploaded_by,created_at FROM offline_map_feature_attachments WHERE feature_id=? ORDER BY created_at DESC');
        $stmt->execute([$featureId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function attachment(int $attachmentId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT a.*, f.created_by AS feature_owner FROM offline_map_feature_attachments a JOIN offline_map_features f ON f.id=a.feature_id WHERE a.id=?');
        $stmt->execute([$attachmentId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function delete(int $id, int $userId, bool $canAdmin): bool
    {
        $sql = 'DELETE FROM offline_map_features WHERE id = ?' . ($canAdmin ? '' : ' AND created_by = ?');
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($canAdmin ? [$id] : [$id, $userId]);
        return $stmt->rowCount() > 0;
    }
}

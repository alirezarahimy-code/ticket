<?php
declare(strict_types=1);

/** Find a union-find root and compress the traversed path. */
function asset_dedupe_find_root(int $id, array &$parent): int
{
    $root = $id;
    while (($parent[$root] ?? $root) !== $root) {
        $root = $parent[$root];
    }
    while ($id !== $root && isset($parent[$id])) {
        $next = $parent[$id];
        $parent[$id] = $root;
        $id = $next;
    }
    return $root;
}

/**
 * Exclude MAC groups whose assets have more than one valid serial identity.
 * Such records may reuse a NIC, but must never be auto-merged silently.
 *
 * @return array{rows:array<int,array<string,mixed>>,conflicts:int,conflict_rows:array<int,array<string,mixed>>}
 */
function asset_dedupe_filter_mac_groups(array $rows): array
{
    $safe = [];
    $conflictRows = [];
    foreach ($rows as $row) {
        if ((int) ($row['serial_variants'] ?? 0) > 1) {
            $conflictRows[] = $row;
            continue;
        }
        $safe[] = $row;
    }
    return ['rows' => $safe, 'conflicts' => count($conflictRows), 'conflict_rows' => $conflictRows];
}

/**
 * Join duplicate-ID groups discovered by independent identity sources.
 *
 * Each source row has an `ids` field containing comma-separated asset IDs,
 * matching MySQL GROUP_CONCAT output. A record may appear in more than one
 * source group; union-find joins the full transitive component exactly once.
 *
 * @return array<int,array{cnt:int,ids:string,keeper:string}>
 */
function asset_dedupe_group_rows(array ...$sourceRows): array
{
    $parent = [];
    foreach ($sourceRows as $rows) {
        foreach ($rows as $row) {
            $rawIds = array_map('intval', explode(',', (string) ($row['ids'] ?? '')));
            $ids = array_values(array_unique(array_filter($rawIds, static fn (int $id): bool => $id > 0)));
            if (count($ids) < 2) {
                continue;
            }
            foreach ($ids as $id) {
                $parent[$id] ??= $id;
            }
            $first = $ids[0];
            foreach (array_slice($ids, 1) as $id) {
                $left = asset_dedupe_find_root($first, $parent);
                $right = asset_dedupe_find_root($id, $parent);
                if ($left !== $right) {
                    // The oldest asset ID is always the keeper/root.
                    $parent[max($left, $right)] = min($left, $right);
                }
            }
        }
    }

    $components = [];
    foreach (array_keys($parent) as $id) {
        $components[asset_dedupe_find_root((int) $id, $parent)][] = (int) $id;
    }

    $groups = [];
    foreach ($components as $ids) {
        if (count($ids) < 2) {
            continue;
        }
        sort($ids, SORT_NUMERIC);
        $groups[] = [
            'cnt' => count($ids),
            'ids' => implode(',', $ids),
            'keeper' => (string) $ids[0],
        ];
    }
    usort($groups, static function (array $a, array $b): int {
        return ($b['cnt'] <=> $a['cnt']) ?: ((int) $a['keeper'] <=> (int) $b['keeper']);
    });
    return $groups;
}

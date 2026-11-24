from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]


def read(path: str) -> str:
    return (ROOT / path).read_text(encoding="utf-8")


def test_offline_map_has_authenticated_entry_and_csrf_protection() -> None:
    entry = read("offline-map.php")
    assert "require __DIR__ . '/bootstrap.php';" in entry
    assert "require_login()" in entry
    assert "user_can($user, 'map.view')" in entry
    assert "require_csrf(false)" in entry


def test_offline_map_has_no_remote_runtime_dependencies() -> None:
    page = read("modules/offline-map/Pages/index.php")
    script = read("modules/offline-map/Assets/offline-map.js")
    assert "https://" not in page
    assert "https://" not in script
    assert "modules/offline-map/vendor/maplibre-gl.js" in page
    assert "modules/offline-map/vendor/pmtiles.js" in page
    assert "modules/offline-map/data/style.json" in script


def test_offline_map_feature_storage_is_migrated_and_seeded() -> None:
    migration = read("migrations/038-offline-map-features.sql")
    schema = read("schema.sql")
    empty_schema = read("install-empty-db.sql")
    assert "CREATE TABLE IF NOT EXISTS offline_map_features" in migration
    assert "offline_map_features" in schema and "offline_map_features" in empty_schema
    assert "038-offline-map-features.sql" in schema
    assert "038-offline-map-features.sql" in empty_schema


def test_offline_map_permissions_are_catalogued_and_integrated() -> None:
    permissions = read("permissions.php")
    navigation = read("lib/render.php")
    assert "'map.view'" in permissions
    assert "'map.features.manage'" in permissions
    assert "'map.admin'" in permissions
    assert "offline-map.php" in navigation


def test_offline_map_php_is_not_directly_web_executable() -> None:
    guard = read("modules/offline-map/.htaccess")
    assert "Require all denied" in guard
    assert r"\.(php|md)" in guard

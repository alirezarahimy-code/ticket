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


def test_offline_map_supports_draw_edit_measure_and_export_controls() -> None:
    page = read("modules/offline-map/Pages/index.php")
    script = read("modules/offline-map/Assets/offline-map.js")
    api = read("offline-map.php")
    for tool in ('data-tool="Point"', 'data-tool="LineString"', 'data-tool="Polygon"', 'data-tool="measure"'):
        assert tool in page
    assert "offline-map-export" in page and "offline-map-print" in page
    assert "distanceKm" in script and "offline-map-draft" in script
    assert "action', 'save'" in script
    assert "'LineString', 'Polygon'" in api


def test_offline_map_search_and_overlays_are_local() -> None:
    api = read("offline-map.php")
    script = read("modules/offline-map/Assets/offline-map.js")
    page = read("modules/offline-map/Pages/index.php")
    builder = read("modules/offline-map/tools/build-search-index.py")
    assert "search.sqlite" in api and "kind === 'places'" in api
    assert "build-search-index.py" in builder
    assert "official-boundaries.geojson" in script
    assert "municipal-districts.geojson" in script
    assert "transit.geojson" in script
    assert "data-official-boundaries" in page


def test_offline_map_private_attachments_have_migration_and_access_guard() -> None:
    migration = read("migrations/039-offline-map-attachments.sql")
    api = read("offline-map.php")
    guard = read("storage/private/offline-map/.htaccess")
    schema = read("schema.sql")
    assert "offline_map_feature_attachments" in migration
    assert "move_uploaded_file" in api and "finfo" in api
    assert "Require all denied" in guard
    assert "offline_map_feature_attachments" in schema
    assert "039-offline-map-attachments.sql" in schema


def test_offline_map_has_local_library_and_glyph_installers() -> None:
    libraries = read("modules/offline-map/tools/Install-OfflineMapLibraries.ps1")
    glyphs = read("modules/offline-map/tools/Install-OfflineMapGlyphs.ps1")
    style = read("modules/offline-map/data/style.json")
    assert "maplibre-gl@6.18.0" in libraries
    assert "pmtiles@4.5.0" in libraries
    assert "Noto Sans Arabic Regular" in glyphs
    assert "./fonts/{fontstack}/{range}.pbf" in style

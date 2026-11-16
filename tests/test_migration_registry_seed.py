from pathlib import Path
import re

ROOT = Path(__file__).resolve().parents[1]
SCHEMAS = ("schema.sql", "install-empty-db.sql")

# Data-only repair migrations are safe to execute on a fresh empty database;
# they are deliberately not pre-marked as applied by the schema baseline.
FRESH_INSTALL_DATA_REPAIR_MIGRATIONS = {
    "033-logging-legacy-context-collation.sql",
}


def _version_tuple(value: str) -> tuple[int, ...]:
    if not re.fullmatch(r"\d+(?:\.\d+){1,3}", value):
        raise AssertionError(f"Invalid migration version: {value!r}")
    parts = tuple(int(part) for part in value.split("."))
    return parts + (0,) * (4 - len(parts))


def test_fresh_install_schemas_seed_every_forward_migration():
    version = (ROOT / "VERSION").read_text(encoding="utf-8").strip()
    current_version = _version_tuple(version)
    expected = {
        p.name for p in (ROOT / "migrations").glob("*.sql")
        if "rollback" not in p.name.lower()
        and p.name not in FRESH_INSTALL_DATA_REPAIR_MIGRATIONS
    }
    assert expected, "No forward migrations found"

    for schema_name in SCHEMAS:
        source = (ROOT / schema_name).read_text(encoding="utf-8")
        match = re.search(
            r"INSERT\s+IGNORE\s+INTO\s+schema_updates\s*"
            r"\(\s*version\s*,\s*migration\s*\)\s*VALUES(.*?);",
            source,
            re.I | re.S,
        )
        assert match, f"{schema_name} does not seed the schema_updates baseline"
        rows = re.findall(r"\('([^']+)'\s*,\s*'([^']+)'\)", match.group(1))
        migration_versions = [row[0] for row in rows]
        migration_names = [row[1] for row in rows]
        assert migration_versions, f"{schema_name} has an empty migration baseline"
        assert all(_version_tuple(item) <= current_version for item in migration_versions), (
            f"{schema_name} records a migration from a future version: {migration_versions}"
        )
        assert len(migration_names) == len(set(migration_names)), (
            f"{schema_name} records a migration more than once"
        )
        actual = set(migration_names)
        assert actual == expected, (
            f"{schema_name} migration registry baseline mismatch; "
            f"missing={sorted(expected - actual)}, extra={sorted(actual - expected)}"
        )

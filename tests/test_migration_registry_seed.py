from pathlib import Path
import re

ROOT = Path(__file__).resolve().parents[1]
SCHEMAS = ("schema.sql", "install-empty-db.sql")

# Data-only repair migrations are safe to execute on a fresh empty database;
# they are deliberately not pre-marked as applied by the schema baseline.
FRESH_INSTALL_DATA_REPAIR_MIGRATIONS = {
    "033-logging-legacy-context-collation.sql",
}


def test_fresh_install_schemas_seed_every_forward_migration():
    version = (ROOT / "VERSION").read_text(encoding="utf-8").strip()
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
        versions = {row[0] for row in rows}
        actual = {row[1] for row in rows}
        assert versions == {version}, f"{schema_name} baseline version mismatch: {versions}"
        assert actual == expected, (
            f"{schema_name} migration registry baseline mismatch; "
            f"missing={sorted(expected - actual)}, extra={sorted(actual - expected)}"
        )


def test_baseline_migration_does_not_pre_register_itself():
    migration_name = "032-schema-updates-baseline.sql"
    source = (ROOT / "migrations" / migration_name).read_text(encoding="utf-8")
    match = re.search(
        r"INSERT\\s+IGNORE\\s+INTO\\s+schema_updates\\s*"
        r"\\(\\s*version\\s*,\\s*migration\\s*\\)\\s*VALUES(.*?);",
        source,
        re.I | re.S,
    )
    assert match, "032 must seed the existing migration registry"
    seeded = {name for name in re.findall(r"'([^']+\\.sql)'", match.group(1))}
    assert migration_name not in seeded, (
        "032 must not register itself: the updater records the migration after "
        "execution, and pre-registering it causes a duplicate-key failure"
    )

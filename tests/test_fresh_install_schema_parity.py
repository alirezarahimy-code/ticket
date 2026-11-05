"""نگهبان نصب تازه: اسکیما باید همیشه با مهاجرت‌ها همگام بماند.

چرا این تست وجود دارد
---------------------
نصب تازه (``install.php``) فقط ``schema.sql`` را اجرا می‌کند و هرگز سراغ
``migrations/`` نمی‌رود. بنابراین هر ستون/جدول/ایندکسی که در یک مهاجرت
اضافه شود ولی به ``schema.sql`` برنگردد، روی نصب‌های تازه **وجود نخواهد
داشت** — در حالی که نصب‌های ارتقایافته سالم‌اند. چنین شکافی خاموش است و
تا اولین اجرای کد مربوطه روی سرور تازه دیده نمی‌شود.

این دقیقاً همان اتفاقی بود که تا نسخهٔ ۱.۶۲.۸۰ افتاده بود: مهاجرت‌های
۰۲۴ تا ۰۳۰ (سامانهٔ لاگ‌گیری مرکزی) ۶ جدول، ۲۴ ستون و ۱۵ ایندکس اضافه
کرده بودند که هیچ‌کدام در ``schema.sql`` نبودند.

قرارداد مستند در ``docs/ARCHITECTURE-FA.md`` می‌گوید هر ستون جدید باید
در هر دو فایل SQL بیاید. این تست همان قرارداد را ماشینی می‌کند.
"""

from __future__ import annotations

import re
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
SCHEMA_FILES = ("schema.sql", "install-empty-db.sql")

# جدول‌هایی که عمداً در اسکیما نیستند: جدول‌های موقتِ خودِ مهاجرت‌ها.
_TRANSIENT_TABLE = re.compile(r"^(tmp_|temp_)|_backup_\d+$|^backup_", re.I)


def _migration_files() -> list[Path]:
    """فایل‌های مهاجرتِ رو‌به‌جلو؛ اسکریپت‌های بازگشت (rollback) کنار می‌روند."""
    return sorted(
        p
        for p in (ROOT / "migrations").glob("*.sql")
        if "rollback" not in p.name.lower()
    )


def _strip_sql_comments(sql: str) -> str:
    sql = re.sub(r"/\*.*?\*/", " ", sql, flags=re.S)
    sql = re.sub(r"(^|\s)--[^\n]*", " ", sql)
    sql = re.sub(r"(^|\s)#[^\n]*", " ", sql)
    return sql


def _schema_text(name: str) -> str:
    return _strip_sql_comments((ROOT / name).read_text(encoding="utf-8"))


def _created_tables(sql: str) -> set[str]:
    return {
        m.group(1).strip("`").lower()
        for m in re.finditer(
            r"CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?`?([A-Za-z_][\w]*)`?", sql, re.I
        )
    }


def _table_bodies(sql: str) -> dict[str, str]:
    """متن داخل پرانتزِ هر ``CREATE TABLE``، با شمارش پرانتزِ متوازن."""
    bodies: dict[str, str] = {}
    for m in re.finditer(
        r"CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?`?([A-Za-z_][\w]*)`?\s*\(", sql, re.I
    ):
        depth, i = 1, m.end()
        while i < len(sql) and depth:
            if sql[i] == "(":
                depth += 1
            elif sql[i] == ")":
                depth -= 1
            i += 1
        bodies[m.group(1).strip("`").lower()] = sql[m.end() : i - 1]
    return bodies


# کلمات کلیدی‌ای که هرگز نام ستون نیستند. شبکهٔ ایمنی در برابر عقب‌گرد regex:
# الگوی ``ADD (COLUMN )?<name>`` روی ``ADD COLUMN IF NOT EXISTS x`` می‌تواند
# بخش اختیاری را خالی بگیرد و خودِ «column» را نام ستون بپندارد.
_NOT_A_COLUMN_NAME = frozenset(
    {
        "column", "if", "index", "key", "unique", "primary", "constraint",
        "foreign", "fulltext", "spatial", "check", "partition",
    }
)

# ``ADD [COLUMN] IF NOT EXISTS <name>`` — شکل خوداصلاح، عمداً نادیده گرفته
# می‌شود چون روی نصب تازه هم بی‌خطر است و نام ستونش قابل اتکا استخراج نمی‌شود.
_ADD_IF_NOT_EXISTS = re.compile(
    r"ADD\s+(?:COLUMN\s+)?IF\s+NOT\s+EXISTS\s+`?[A-Za-z_][\w]*`?", re.I
)


def _added_columns(sql: str) -> set[tuple[str, str]]:
    """ستون‌هایی که ``ALTER TABLE ... ADD COLUMN`` اضافه می‌کند."""
    found: set[tuple[str, str]] = set()
    for stmt in re.split(r";\s*(?:\r?\n|$)", sql):
        m = re.search(r"ALTER\s+TABLE\s+`?([A-Za-z_][\w]*)`?", stmt, re.I)
        if not m:
            continue
        table = m.group(1).strip("`").lower()
        if _TRANSIENT_TABLE.search(table):
            continue
        # اول شکل ``IF NOT EXISTS`` را کامل حذف کن تا regex بعدی نتواند با
        # عقب‌گرد، واژهٔ «column» را به‌جای نام ستون بردارد.
        cleaned = _ADD_IF_NOT_EXISTS.sub(" ", stmt)
        for col in re.finditer(
            r"ADD\s+(?:COLUMN\s+)?`?([A-Za-z_][\w]*)`?\s+", cleaned, re.I
        ):
            name = col.group(1).strip("`").lower()
            if name not in _NOT_A_COLUMN_NAME:
                found.add((table, name))
    return found


def _added_indexes(sql: str) -> set[tuple[str, str]]:
    found: set[tuple[str, str]] = set()
    for stmt in re.split(r";\s*(?:\r?\n|$)", sql):
        m = re.search(r"ALTER\s+TABLE\s+`?([A-Za-z_][\w]*)`?", stmt, re.I)
        if m:
            table = m.group(1).strip("`").lower()
            if _TRANSIENT_TABLE.search(table):
                continue
            for idx in re.finditer(
                r"ADD\s+(?:UNIQUE\s+)?(?:INDEX|KEY)\s+(?!IF\b)`?([A-Za-z_][\w]*)`?", stmt, re.I
            ):
                found.add((table, idx.group(1).strip("`").lower()))
        for idx in re.finditer(
            r"CREATE\s+(?:UNIQUE\s+)?INDEX\s+(?:IF\s+NOT\s+EXISTS\s+)?`?([A-Za-z_][\w]*)`?"
            r"\s+ON\s+`?([A-Za-z_][\w]*)`?",
            stmt,
            re.I,
        ):
            table = idx.group(2).strip("`").lower()
            if not _TRANSIENT_TABLE.search(table):
                found.add((table, idx.group(1).strip("`").lower()))
    return found


def _migration_corpus() -> str:
    return "\n".join(
        _strip_sql_comments(p.read_text(encoding="utf-8")) for p in _migration_files()
    )


# ---------------------------------------------------------------------------
# تست‌ها
# ---------------------------------------------------------------------------


def test_migration_files_exist():
    """اگر پوشهٔ مهاجرت خالی باشد بقیهٔ تست‌ها بی‌معنا سبز می‌شوند."""
    assert len(_migration_files()) >= 20


def test_every_migration_table_exists_in_both_schema_files():
    corpus = _migration_corpus()
    wanted = {t for t in _created_tables(corpus) if not _TRANSIENT_TABLE.search(t)}
    assert wanted, "هیچ CREATE TABLE ای در مهاجرت‌ها پیدا نشد — پارسر خراب است"
    for name in SCHEMA_FILES:
        have = _created_tables(_schema_text(name))
        missing = sorted(wanted - have)
        assert not missing, (
            f"{name} این جدول‌های مهاجرت را ندارد، پس نصب تازه بدون آن‌ها بالا می‌آید: {missing}"
        )


def test_every_migration_column_exists_in_both_schema_files():
    corpus = _migration_corpus()
    wanted = _added_columns(corpus)
    assert wanted, "هیچ ADD COLUMN ای پیدا نشد — پارسر خراب است"
    for name in SCHEMA_FILES:
        text = _schema_text(name)
        bodies = _table_bodies(text)
        known = _created_tables(text)
        missing = []
        for table, column in sorted(wanted):
            if table not in known:
                continue  # شکاف جدول را تست دیگری گزارش می‌کند
            body = bodies.get(table, "")
            if not re.search(rf"^\s*`?{re.escape(column)}`?\s", body, re.I | re.M):
                missing.append(f"{table}.{column}")
        assert not missing, (
            f"{name} این ستون‌های مهاجرت را ندارد، پس نصب تازه بدون آن‌ها بالا می‌آید: {missing}"
        )


def test_every_migration_index_exists_in_both_schema_files():
    corpus = _migration_corpus()
    wanted = _added_indexes(corpus)
    assert wanted, "هیچ ایندکسی در مهاجرت‌ها پیدا نشد — پارسر خراب است"
    for name in SCHEMA_FILES:
        text = _schema_text(name)
        bodies = _table_bodies(text)
        known = _created_tables(text)
        missing = []
        for table, index in sorted(wanted):
            if table not in known:
                continue
            if not re.search(rf"`?{re.escape(index)}`?\s*\(", bodies.get(table, ""), re.I):
                missing.append(f"{table}.{index}")
        assert not missing, (
            f"{name} این ایندکس‌های مهاجرت را ندارد: {missing}"
        )


def test_both_schema_files_define_the_same_tables():
    sets = {name: _created_tables(_schema_text(name)) for name in SCHEMA_FILES}
    a, b = SCHEMA_FILES
    only_a = sorted(sets[a] - sets[b])
    only_b = sorted(sets[b] - sets[a])
    assert not only_a, f"{a} جدول‌هایی دارد که در {b} نیست: {only_a}"
    assert not only_b, f"{b} جدول‌هایی دارد که در {a} نیست: {only_b}"


def test_both_schema_files_define_identical_table_bodies():
    """هر دو فایل باید دقیقاً یک ساختار بسازند (ENUM، پیش‌فرض، nullability)."""
    a, b = SCHEMA_FILES
    bodies_a, bodies_b = _table_bodies(_schema_text(a)), _table_bodies(_schema_text(b))
    mismatched = []
    for table in sorted(set(bodies_a) & set(bodies_b)):
        norm_a = {re.sub(r"\s+", " ", ln).strip().rstrip(",").lower()
                  for ln in bodies_a[table].splitlines() if ln.strip()}
        norm_b = {re.sub(r"\s+", " ", ln).strip().rstrip(",").lower()
                  for ln in bodies_b[table].splitlines() if ln.strip()}
        # ``DEFAULT NULL`` و حذفش هم‌معنا هستند؛ تفاوت نگارشی را نادیده بگیر.
        norm_a = {re.sub(r"\s+default null\b", "", ln) for ln in norm_a}
        norm_b = {re.sub(r"\s+default null\b", "", ln) for ln in norm_b}
        if norm_a != norm_b:
            diff = sorted(norm_a ^ norm_b)[:4]
            mismatched.append(f"{table}: {diff}")
    assert not mismatched, (
        "تعریف جدول بین دو فایل اسکیما فرق دارد، پس نصب تازه بسته به فایل "
        f"انتخابی رفتار متفاوت می‌دهد: {mismatched}"
    )


def test_schema_executes_in_order_without_forward_foreign_keys():
    """``install.php`` دستورها را پشت‌سرِ هم اجرا می‌کند و خطا را پرتاب می‌کند.

    پس هر ``REFERENCES`` باید به جدولی اشاره کند که **پیش‌تر** ساخته شده،
    وگرنه نصب تازه وسط کار می‌ترکد.
    """
    for name in SCHEMA_FILES:
        text = _schema_text(name)
        created: set[str] = set()
        problems = []
        # همان جداسازی‌ای که install.php انجام می‌دهد.
        for stmt in re.split(r";\s*(?:\r?\n|$)", text):
            if not stmt.strip():
                continue
            m = re.search(
                r"CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?`?([A-Za-z_][\w]*)`?", stmt, re.I
            )
            current = m.group(1).strip("`").lower() if m else None
            for ref in re.finditer(r"REFERENCES\s+`?([A-Za-z_][\w]*)`?", stmt, re.I):
                target = ref.group(1).strip("`").lower()
                if target != current and target not in created:
                    problems.append(f"{current or '?'} → {target}")
            if current:
                created.add(current)
        assert not problems, (
            f"{name} به جدولی ارجاع می‌دهد که هنوز ساخته نشده؛ نصب تازه شکست می‌خورد: {problems}"
        )


def test_both_schema_files_seed_the_same_structural_rows():
    """ردیف‌های پیش‌فرضِ ساختاری باید در هر دو فایل باشند.

    بعضی جدول‌ها (مثلاً ``food_ticket_config``) طبق قرارداد کد همیشه دقیقاً
    یک ردیف دارند — ``db_install_fresh_tables()`` در ``lib/db-core.php`` روی
    همین فرض بنا شده. اگر این ردیف فقط در یکی از دو فایل درج شود، نصب تازه
    بسته به مسیر انتخابی رفتار متفاوت می‌دهد.

    دو کلید ``settings`` عمداً فقط در ``install-empty-db.sql`` هستند و نشانهٔ
    همان مسیر نصب‌اند، نه شکاف: ``db_install_token`` خوددرمان است
    (``lib/db-core.php`` اگر نباشد می‌سازد) و ``installed_mode`` را هیچ کدی
    نمی‌خواند.
    """
    seeded_only_in_empty_db = {"settings"}
    pattern = re.compile(r"INSERT\s+(?:IGNORE\s+)?INTO\s+`?([A-Za-z_][\w]*)`?", re.I)
    counts = {}
    for name in SCHEMA_FILES:
        tables: dict[str, int] = {}
        for m in pattern.finditer(_schema_text(name)):
            table = m.group(1).strip("`").lower()
            tables[table] = tables.get(table, 0) + 1
        counts[name] = tables
    a, b = SCHEMA_FILES
    only_a = {t: n for t, n in counts[a].items() if t not in counts[b]}
    only_b = {
        t: n
        for t, n in counts[b].items()
        if t not in counts[a] and t not in seeded_only_in_empty_db
    }
    assert not only_a, f"{a} ردیف پیش‌فرضی دارد که در {b} نیست: {only_a}"
    assert not only_b, f"{b} ردیف پیش‌فرضی دارد که در {a} نیست: {only_b}"
    differing = {
        t: (counts[a][t], counts[b][t])
        for t in set(counts[a]) & set(counts[b])
        if counts[a][t] != counts[b][t] and t not in seeded_only_in_empty_db
    }
    assert not differing, f"شمار ردیف‌های پیش‌فرض بین دو فایل فرق دارد: {differing}"


def test_full_rebuild_drops_every_table_in_both_schema_files():
    """``rebuild-final.sql`` نباید جدولی را جا بیندازد، وگرنه بازسازی ناقص است."""
    rebuild = _strip_sql_comments((ROOT / "rebuild-final.sql").read_text(encoding="utf-8"))
    m = re.search(r"DROP\s+TABLE\s+IF\s+EXISTS(.*?);", rebuild, re.S | re.I)
    assert m is not None, "rebuild-final.sql دستور DROP TABLE IF EXISTS ندارد"
    dropped = {t.strip().strip("`").lower() for t in m.group(1).split(",") if t.strip()}
    for name in SCHEMA_FILES:
        missing = sorted(_created_tables(_schema_text(name)) - dropped)
        assert not missing, f"rebuild-final.sql این جدول‌های {name} را drop نمی‌کند: {missing}"

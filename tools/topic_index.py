#!/usr/bin/env python3
"""ساخت فهرست موضوعی: «نام فارسی بخش» → «آدرس کد».

هدف صریح: وقتی کسی می‌گوید «تنظیمات کاربر»، نباید در ۱۲۰ هزار خط جست‌وجو
شود. یک نگاه به این فهرست باید آدرس دقیق را بدهد.

موضوع‌ها از خودِ سامانه استخراج می‌شوند، نه از حدس:
  * عنوان فارسی صفحه‌ها  ← admin_layout_design_page_label()
  * مجوزها با برچسب فارسی ← permissions.php
  * نگاشت صفحه به مجوز    ← $pagePermissions در index.php
  * جدول‌های پایگاه داده  ← CREATE TABLE در schema.sql

چون منبع، خودِ کد است، فهرست با تغییر کد کهنه می‌شود و تست نگهبان
(tests/test_topic_index_integrity.py) آن را می‌گیرد.
"""
from __future__ import annotations

import json
import re
from collections import defaultdict
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
INDEX = json.loads((ROOT / "docs/system-map/BLOCKS.json").read_text(encoding="utf-8"))


def read(p: str) -> str:
    return (ROOT / p).read_text(encoding="utf-8", errors="replace")


# ---------------------------------------------------------------------------
# استخراج موضوع‌ها از منابع واقعی سامانه
# ---------------------------------------------------------------------------

def page_labels() -> dict[str, str]:
    """نام صفحه ← عنوان فارسی، از تابع برچسب‌گذاری خود سامانه."""
    src = read("admin-layout-design.php")
    m = re.search(r"function admin_layout_design_page_label.*?\$labels = \[(.*?)\];", src, re.S)
    if not m:
        return {}
    labels = {
        k: v for k, v in re.findall(r"'([a-z0-9\-]+)'\s*=>\s*'([^']+)'", m.group(1))
    }
    # صفحه‌هایی که برچسب فارسی ندارند نباید از فهرست بیفتند، وگرنه ادعای
    # «هیچ بخشی جا نمانده» دروغ می‌شود. همان رفتار جایگزین خود سامانه.
    for slug in sorted(set(re.findall(r"\$page\s*===?\s*'([a-z0-9\-]+)'", read("index.php")))
                       | set(re.findall(r"\$page\s*===?\s*'([a-z0-9\-]+)'", src))):
        labels.setdefault(slug, slug.replace("-", " ").replace("_", " "))
    return labels


def permission_catalog() -> list[dict]:
    """فهرست مجوزها با گروه و برچسب فارسی."""
    src = read("permissions.php")
    out: list[dict] = []
    group = ""
    for line in src.split("\n"):
        g = re.search(r"'label'\s*=>\s*'([^']+)'\s*,\s*$", line)
        if g and "'code'" not in line:
            group = g.group(1)
            continue
        c = re.search(r"'code'\s*=>\s*'([a-z0-9_.]+)'\s*,\s*'label'\s*=>\s*'([^']+)'", line)
        if c:
            out.append({"code": c.group(1), "label": c.group(2), "group": group})
    return out


def page_permissions() -> dict[str, str]:
    src = read("index.php")
    m = re.search(r"\$pagePermissions = \[(.*?)\];", src, re.S)
    if not m:
        return {}
    return dict(re.findall(r"'([a-z0-9\-]+)'\s*=>\s*'([a-z0-9_.]+)'", m.group(1)))


def db_tables() -> list[str]:
    names = set()
    for f in INDEX["files"]:
        if f["area"] != "DB":
            continue
        for b in f["blocks"]:
            m = re.match(r"CREATE TABLE ([a-z0-9_]+)$", b["anchor"], re.I)
            if m:
                names.add(m.group(1))
    return sorted(names)


# ---------------------------------------------------------------------------
# نمایهٔ معکوس: واژه ← آدرس‌ها
# ---------------------------------------------------------------------------

QUOTED = re.compile(r"['\"]([A-Za-z0-9_.\-]{2,60})['\"]")
SQL_REF = re.compile(
    r"\b(?:FROM|JOIN|INTO|UPDATE|TABLE|EXISTS)\s+[`'\"]?([a-z0-9_]{3,40})\b", re.I
)


def build_inverted() -> tuple[dict[str, set[str]], dict[str, dict]]:
    """واژه ← مجموعهٔ آدرس‌ها، و جدول اطلاعات هر آدرس."""
    inv: dict[str, set[str]] = defaultdict(set)
    meta: dict[str, dict] = {}
    for f in INDEX["files"]:
        lines = read(f["path"]).split("\n")
        for b in f["blocks"]:
            text = "\n".join(lines[b["line_start"] - 1:b["line_end"]])
            meta[b["id"]] = {
                "path": f["path"], "area": f["area"], "line": b["line_start"],
                "anchor": b["anchor"], "risk": b["risk"]["score"], "lines": b["lines"],
            }
            terms = set(QUOTED.findall(text)) | {t.lower() for t in SQL_REF.findall(text)}
            for t in terms:
                inv[t].add(b["id"])
            # نام فایل هم یک واژهٔ معتبر است (صفحهٔ هم‌نام)
            inv[Path(f["path"]).stem].add(b["id"])
    return inv, meta


def rank(ids: set[str], meta: dict, slug: str) -> tuple[list[str], list[str]]:
    """آدرس‌های «اصلی» را از «مرتبط» جدا می‌کند.

    بدون تفکیک، یک موضوع پرکاربرد ده‌ها آدرس می‌دهد و باز هم باید گشت —
    یعنی فهرست کار خودش را نکرده. امتیازدهی عمداً ساده و قابل توضیح است.
    """
    base = slug.split(".")[0]
    scored: list[tuple[int, str]] = []
    for i in ids:
        m = meta[i]
        stem = Path(m["path"]).stem
        score = 0
        if stem == slug or stem == base:
            score += 12
        elif stem.startswith(base + "-") or stem.startswith(base + "_"):
            score += 8
        if slug in m["anchor"] or base in m["anchor"]:
            score += 6
        # بلوک مسیریابی = جایی که صفحه واقعاً پیاده شده؛ باید اول بیاید،
        # نه ردیف INSERT اسکیما که اتفاقاً هم‌نام است.
        if (m["anchor"].startswith("مسیر:") or m["anchor"].startswith("case:")) and base in m["anchor"]:
            score += 9
        if m["area"] == "ACT" and base in m["path"]:
            score += 5
        if m["area"] == "DB" and base in m["anchor"].lower():
            score += 5 if m["anchor"].upper().startswith("CREATE TABLE") else 1
        if m["area"] == "CORE":
            score += 2
        scored.append((score, i))

    scored.sort(key=lambda x: (-x[0], -meta[x[1]]["risk"], meta[x[1]]["path"], meta[x[1]]["line"]))
    primary = [i for sc, i in scored if sc > 0]
    related = [i for sc, i in scored if sc == 0]
    # اگر هیچ آدرسی امتیاز نگرفت، فهرست اصلی نباید خالی بماند؛ وگرنه موضوع
    # ظاهراً «۷ آدرس دارد» ولی عملاً هیچ‌کدام نشان داده نمی‌شوند.
    if not primary and related:
        primary, related = related[:3], related[3:]
    return primary, related


def main() -> None:
    inv, meta = build_inverted()
    labels, perms, pageperm, tables = page_labels(), permission_catalog(), page_permissions(), db_tables()

    topics = []
    for slug, fa in sorted(labels.items(), key=lambda x: x[1]):
        ids = inv.get(slug, set())
        p, r = rank(ids, meta, slug)
        topics.append({
            "id": f"page:{slug}", "kind": "صفحه", "fa": fa, "key": slug,
            "permission": pageperm.get(slug), "primary": p[:14], "related": r[:10],
            "total": len(ids),
        })

    perm_topics = []
    for item in perms:
        ids = inv.get(item["code"], set())
        p, r = rank(ids, meta, item["code"])
        perm_topics.append({
            "id": f"perm:{item['code']}", "kind": "مجوز", "fa": item["label"],
            "group": item["group"], "key": item["code"],
            "primary": p[:10], "related": r[:6], "total": len(ids),
        })

    table_topics = []
    for t in tables:
        ids = inv.get(t, set())
        p, r = rank(ids, meta, t)
        table_topics.append({
            "id": f"table:{t}", "kind": "جدول", "fa": t, "key": t,
            "primary": p[:10], "related": r[:6], "total": len(ids),
        })

    out = {
        "schema": 1,
        "generated_by": "tools/topic_index.py",
        "pages": topics,
        "permissions": perm_topics,
        "tables": table_topics,
        "address_meta": meta,
    }
    (ROOT / "docs/system-map/TOPICS.json").write_text(
        json.dumps(out, ensure_ascii=False, indent=2), encoding="utf-8"
    )
    empty = [t["id"] for t in topics + perm_topics + table_topics if t["total"] == 0]
    print(f"موضوع: {len(topics)} صفحه، {len(perm_topics)} مجوز، {len(table_topics)} جدول")
    print(f"بدون آدرس: {len(empty)} {empty[:8]}")


if __name__ == "__main__":
    main()

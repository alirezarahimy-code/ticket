#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""جستجوگر سریع نقشهٔ سامانه — «کجا باید دنبال X بگردم؟»

نمونهها:
  python3 tools/find-in-system.py "فیش چاپ نشد"
  python3 tools/find-in-system.py "ثبت دستی حضور"
  python3 tools/find-in-system.py "TENTER"
  python3 tools/find-in-system.py --symbol food_ticket_mark_delivered_on_print
  python3 tools/find-in-system.py --page food-ticket
  python3 tools/find-in-system.py --table food_orders
  python3 tools/find-in-system.py --action save_org_node
  python3 tools/find-in-system.py --stats

منبع داده: docs/system-map/*.tsv (تولیدشده توسط tools/build_system_map.py).
اگر پیام «نقشه پیدا نشد/کهنه است» دیدی، اول آن اسکریپت را اجرا کن.
"""

from __future__ import annotations

import argparse
import csv
import json
import re
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
MAP = ROOT / "docs" / "system-map"

FILES = ["FEATURES.tsv", "SYMBOLS.tsv", "ROUTES.tsv", "TABLES.tsv", "FILES.tsv", "UPDATES.tsv"]


def normalize(text: str) -> str:
    text = text.lower()
    text = text.replace("ي", "ی").replace("ى", "ی").replace("ك", "ک").replace("ة", "ه")
    text = text.replace("أ", "ا").replace("إ", "ا").replace("آ", "ا").replace("ؤ", "و")
    text = re.sub(r"[\u200c\u200d\u200e\u200f]", "", text)  # نیمفاصله و جهت
    text = re.sub(r"[_\-/]+", " ", text)
    text = re.sub(r"\s+", " ", text).strip()
    return text


def load(name: str) -> list[dict[str, str]]:
    path = MAP / name
    if not path.is_file():
        return []
    with path.open(encoding="utf-8") as handle:
        return list(csv.DictReader(handle, delimiter="\t"))


def score(query: str, haystack: str) -> int:
    """امتیاز شباهت ساده: تطابق کامل > عضویت > اشتراک توکنها."""
    q, h = normalize(query), normalize(haystack)
    if not q or not h:
        return 0
    if q == h:
        return 100
    if q in h:
        return 70 - min(20, len(h) - len(q))
    if h in q and len(h) >= 3:
        return 55
    q_tokens = set(q.split())
    h_tokens = set(h.split())
    shared = q_tokens & h_tokens
    if shared:
        return 20 + 10 * len(shared)
    # تطابق جزئی حروف (مثلاً «فیش» در «فیشها»)
    for token in q_tokens:
        if len(token) >= 3 and any(token in ht for ht in h_tokens):
            return 12
    return 0


def top(query: str, items: list[dict], field: str, limit: int = 5, min_score: int = 10) -> list[tuple[int, dict]]:
    scored = [(score(query, item.get(field, "")), item) for item in items]
    scored = [(s, item) for s, item in scored if s >= min_score]
    scored.sort(key=lambda pair: -pair[0])
    # حذف تکراری بر اساس فایل+نام
    seen: set[str] = set()
    out = []
    for s, item in scored:
        key = f"{item.get('file', item.get('path', ''))}:{item.get(field, '')}"
        if key in seen:
            continue
        seen.add(key)
        out.append((s, item))
        if len(out) >= limit:
            break
    return out


def print_features(query: str, limit: int = 3) -> int:
    features = load("FEATURES.tsv")
    hits = []
    for item in features:
        keywords = item["keywords"].split("|")
        best = max((score(query, kw) for kw in keywords), default=0)
        # امتیاز اضافی برای تطابق عنوان
        best = max(best, score(query, item["title"]) + 5)
        if best >= 10:
            hits.append((best, item))
    hits.sort(key=lambda pair: -pair[0])
    hits = hits[:limit]
    if not hits:
        return 0
    print("\n🎯 حوزههای مرتبط (از ایندکس ویژگیها):")
    for _s, item in hits:
        print(f"  ▸ {item['title']}")
        print(f"      فایلها: {item['primary_files']}")
        if item["tables"] and item["tables"] != "—":
            print(f"      جداول: {item['tables']}")
        if item["note"] and item["note"] != "—":
            print(f"      یادداشت: {item['note']}")
    return len(hits)


def print_section(title: str, query: str, filename: str, field: str,
                  formatter, limit: int = 6, min_score: int = 10) -> int:
    hits = top(query, load(filename), field, limit, min_score)
    if not hits:
        return 0
    print(f"\n{title}:")
    for _s, item in hits:
        print("  " + formatter(item))
    return len(hits)


def main() -> int:
    parser = argparse.ArgumentParser(description="جستجوگر نقشهٔ سامانه")
    parser.add_argument("query", nargs="?", default="", help="عبارت فارسی یا انگلیسی")
    parser.add_argument("--symbol", help="جستجوی دقیق نام تابع/کلاس")
    parser.add_argument("--page", help="جستجوی صفحهٔ ?page=")
    parser.add_argument("--action", help="جستجوی اکشن POST")
    parser.add_argument("--api", help="جستجوی endpoint API")
    parser.add_argument("--table", help="جستجوی جدول دیتابیس")
    parser.add_argument("--package", help="جستجوی بستهٔ بهروزرسانی")
    parser.add_argument("--stats", action="store_true", help="نمایش آمار نقشه")
    parser.add_argument("--limit", type=int, default=6, help="حداکثر نتیجه در هر بخش")
    args = parser.parse_args()

    if not MAP.is_dir() or not all((MAP / f).is_file() for f in FILES):
        print("FAIL — ایندکسها کامل نیستند. اجرا کن: python3 tools/build_system_map.py")
        return 1

    if args.stats:
        summary = json.loads((MAP / "summary.json").read_text(encoding="utf-8"))
        print(json.dumps(summary, ensure_ascii=False, indent=2))
        return 0

    # جستجوی بستهها هم روی نام پوشه و هم روی شمارهٔ نسخه انجام میشود.
    if args.package:
        items = load("UPDATES.tsv")
        scored = []
        for item in items:
            best = max(score(args.package, item["package"]),
                       score(args.package, item["version"]) + 5)
            if best >= 50:
                scored.append((best, item))
        scored.sort(key=lambda pair: (-pair[0], pair[1]["package"]))
        exact = [pair for pair in scored if pair[0] >= 95]
        if exact:
            scored = exact
        print(f"📦 بستههای آپدیت برای «{args.package}»:")
        if not scored:
            print("  (نتیجهای یافت نشد — مثلاً --package 162-36 امتحان کن)")
            return 1
        for _s, item in scored[: args.limit * 3]:
            print(f"  {item['package']}  (v{item['version']} ← {item['requires_version']}, "
                  f"{item['files']} فایل / {item['migrations']} migration, {item['date']})")
        return 0

    # حالتهای تکمنظوره
    targeted = [
        ("--symbol", args.symbol, "🔧 نمادها", "SYMBOLS.tsv", "name",
         lambda i: f"{i['name']}  ({i['kind']})  →  {i['file']}:{i['line']}"),
        ("--page", args.page, "📄 صفحات", "ROUTES.tsv", "name",
         lambda i: f"?page={i['name']}  →  {i['file']}:{i['line']}"),
        ("--action", args.action, "⚡ اکشنهای POST", "ROUTES.tsv", "name",
         lambda i: f"action={i['name']}  →  {i['file']}:{i['line']}"),
        ("--api", args.api, "🔌 API", "ROUTES.tsv", "name",
         lambda i: f"api/{i['name']}  →  {i['file']}:{i['line']}"),
        ("--table", args.table, "🗄 جداول", "TABLES.tsv", "table",
         lambda i: f"{i['table']}  →  {i['file']}:{i['line']}"),
    ]
    for flag, value, title, filename, field, fmt in targeted:
        if not value:
            continue
        items = load(filename)
        if flag in ("--page", "--action", "--api"):
            kind = {"--page": "page", "--action": "action", "--api": "api"}[flag]
            items = [i for i in items if i["kind"] == kind]
        hits = top(value, items, field, args.limit * 3, 50)
        # اگر تطابق دقیق وجود دارد، فقط همانها نشان داده شوند تا نویز کم شود.
        exact = [pair for pair in hits if pair[0] >= 95]
        if exact:
            hits = exact
        print(f"{title} برای «{value}»:")
        if not hits:
            print("  (نتیجهای یافت نشد)")
            return 1
        for _s, item in hits[: args.limit * 2]:
            print("  " + fmt(item))
        return 0

    query = args.query.strip()
    if not query:
        parser.print_help()
        return 1

    print(f"🔍 جستجو برای: «{query}»")
    found = 0
    found += print_features(query, limit=3)
    found += print_section("🔧 توابع/کلاسها", query, "SYMBOLS.tsv", "name",
                           lambda i: f"{i['name']}  ({i['kind']})  →  {i['file']}:{i['line']}",
                           args.limit, 20)
    found += print_section("📄 صفحات و روتها", query, "ROUTES.tsv", "name",
                           lambda i: f"{i['kind']}={i['name']}  →  {i['file']}:{i['line']}",
                           args.limit, 20)
    found += print_section("🗄 جداول دیتابیس", query, "TABLES.tsv", "table",
                           lambda i: f"{i['table']}  →  {i['file']}:{i['line']}",
                           args.limit, 20)
    found += print_section("📁 فایلها", query, "FILES.tsv", "path",
                           lambda i: f"{i['path']}  ({i['category']}, {i['lines']} خط)",
                           args.limit, 20)

    if not found:
        print("\n(هیچ نتیجهای یافت نشد — واژهٔ دیگری امتحان کن یا --stats ببین)")
        return 1
    print("\n💡 منبع: docs/system-map/ · بازتولید: python3 tools/build_system_map.py")
    return 0


if __name__ == "__main__":
    sys.exit(main())

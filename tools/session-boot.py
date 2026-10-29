#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""دکمهٔ استارت جلسه — «بهروز شو» / بوت کامل ایجنت کدنویسی.

این اسکریپت در **یک اجرا** تمام زمینهای را که ایجنت برای شروع نیاز دارد فراهم
میکند: مانیفست مهارتها، نقشهٔ سامانه، قواعد ثابت کاربر، ایندکسها، وضعیت گیت،
و نتیجهٔ تستها. خروجی آن مستقیماً بهعنوان «چارچوب عملیاتی جلسه» قابل پذیرش است.

استفاده:
  python3 tools/session-boot.py                # بوت کامل (خودکار نقشه را بهروز میکند)
  python3 tools/session-boot.py --no-refresh   # فقط گزارش، بدون بازتولید خودکار
  python3 tools/session-boot.py --pull         # ابتدا git pull --ff-only (فقط با درخواست صریح)
  python3 tools/session-boot.py --json         # خروجی ماشینی

نکتهٔ صداقت: این اسکریپت حافظهٔ مدل را تغییر نمیدهد؛ بلکه محتوای لازم را
چاپ میکند تا ایجنت آن را در همان جلسه بپذیرد. برای بارگذاری خودکار، فایلهای
AGENTS.md / CLAUDE.md / .github/copilot-instructions.md / .cursor/rules/
به همین دستور ارجاع میدهند.
"""

from __future__ import annotations

import argparse
import json
import re
import subprocess
import sys
from datetime import datetime
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent

MANIFEST = ROOT / "docs" / "SKILL-MANIFEST-FA.md"
MAP_DOC = ROOT / "docs" / "SYSTEM-MAP-FA.md"
AGENTS = ROOT / "AGENTS.md"
HANDOFF = ROOT / "docs" / "HANDOFF-FA.md"
SUMMARY = ROOT / "docs" / "system-map" / "summary.json"
BUILDER = ROOT / "tools" / "build_system_map.py"
BOOT_GUIDE = ROOT / "docs" / "SESSION-BOOT-FA.md"

GUARD_TESTS = [
    ROOT / "tests" / "test_skill_manifest_static.py",
    ROOT / "tests" / "test_system_map_static.py",
    ROOT / "tests" / "test_cache_pin_consistency_static.py",
    ROOT / "tests" / "test_session_boot_static.py",
]


def read(path: Path) -> str:
    try:
        return path.read_text(encoding="utf-8")
    except OSError:
        return ""


def run(args: list[str], timeout: int = 180) -> tuple[int, str]:
    try:
        proc = subprocess.run(
            args, cwd=ROOT, capture_output=True, text=True, timeout=timeout
        )
        return proc.returncode, (proc.stdout + proc.stderr).strip()
    except (OSError, subprocess.TimeoutExpired) as exc:
        return 1, str(exc)


def git_info() -> dict[str, str]:
    def q(*args: str) -> str:
        rc, out = run(["git", *args], timeout=30)
        return out if rc == 0 else "?"

    branch = q("rev-parse", "--abbrev-ref", "HEAD")
    head = q("rev-parse", "--short", "HEAD")
    status = q("status", "--porcelain")
    rc_up, upstream = run(["git", "rev-parse", "--short", "@{u}"], timeout=30)
    remote = upstream.strip() if rc_up == 0 else "(بدون upstream)"
    last_log = q("log", "-1", "--format=%h %ad %s", "--date=short")
    return {
        "branch": branch,
        "head": head,
        "upstream": remote,
        "dirty_files": str(len([x for x in status.splitlines() if x.strip()])),
        "last_commit": last_log,
        "version": read(ROOT / "VERSION").strip(),
    }


def extract_section(text: str, heading_prefix: str, next_prefix: str = "## ") -> str:
    """بخشی از یک فایل markdown را بر اساس تیتر استخراج میکند."""
    lines = text.splitlines()
    start = None
    for i, line in enumerate(lines):
        if line.startswith(heading_prefix):
            start = i
            break
    if start is None:
        return ""
    end = len(lines)
    for j in range(start + 1, len(lines)):
        if lines[j].startswith(next_prefix):
            end = j
            break
    return "\n".join(lines[start:end]).strip()


def skill_rows(manifest: str) -> list[tuple[str, str, str]]:
    """ردیفهای جدول مانیفست → (ID, نام مهارت, وضعیت)."""
    out = []
    for line in manifest.splitlines():
        m = re.match(r"^\|\s*([SC]\d{2})\s*\|\s*([^|]+?)\s*\|\s*([^|]*?)\s*\|", line)
        if m:
            out.append((m.group(1), m.group(2).strip(), m.group(3).strip()))
    return out


def bullets(section: str) -> list[str]:
    return [re.sub(r"^[-*]\s*", "", l).strip()
            for l in section.splitlines() if re.match(r"^\s*[-*]\s+", l)]


def refresh_map_if_stale(allow_refresh: bool) -> dict[str, str]:
    rc, out = run([sys.executable, str(BUILDER), "--check"], timeout=180)
    if rc == 0:
        return {"state": "PASS", "action": "نیازی به بازتولید نبود", "detail": out.splitlines()[0] if out else ""}
    if not allow_refresh:
        return {"state": "STALE", "action": "بازتولید انجام نشد (--no-refresh)", "detail": out.splitlines()[0] if out else ""}
    rc2, out2 = run([sys.executable, str(BUILDER)], timeout=180)
    if rc2 != 0:
        return {"state": "FAIL", "action": "بازتولید ناموفق", "detail": out2[-300:]}
    rc3, out3 = run([sys.executable, str(BUILDER), "--check"], timeout=180)
    return {
        "state": "REFRESHED" if rc3 == 0 else "FAIL",
        "action": "ایندکسها خودکار بازتولید و تأیید شدند",
        "detail": out3.splitlines()[0] if out3 else "",
    }


def run_guard_tests() -> list[tuple[str, int, str]]:
    results = []
    for test in GUARD_TESTS:
        if not test.is_file():
            results.append((test.name, 1, "فایل تست وجود ندارد"))
            continue
        rc, out = run([sys.executable, str(test)], timeout=180)
        last = out.splitlines()[-1] if out else "(بدون خروجی)"
        results.append((test.name, rc, last[:160]))
    return results


def build_context(args: argparse.Namespace, skip_tests: bool = False) -> dict:
    if args.pull:
        run(["git", "pull", "--ff-only"], timeout=120)

    info = git_info()
    manifest = read(MANIFEST)
    agents = read(AGENTS)
    handoff = read(HANDOFF)
    map_doc = read(MAP_DOC)

    summary = {}
    if SUMMARY.is_file():
        try:
            summary = json.loads(SUMMARY.read_text(encoding="utf-8"))
        except json.JSONDecodeError:
            summary = {}

    refresh = refresh_map_if_stale(allow_refresh=not args.no_refresh)
    # پس از بازتولید، آمار را دوباره بخوان
    if SUMMARY.is_file():
        try:
            summary = json.loads(SUMMARY.read_text(encoding="utf-8"))
        except json.JSONDecodeError:
            pass

    skills = skill_rows(manifest)
    rules_section = extract_section(agents, "## قواعد همیشگی")
    workflow_section = extract_section(agents, "## گردشکار الزامی")
    standing_section = extract_section(handoff, "## ۱. قواعد ثابت کار")
    map_intro = extract_section(map_doc, "## ۲. نمای کلی معماری")[:1200]

    return {
        "generated_at": datetime.now().strftime("%Y-%m-%d %H:%M:%S"),
        "git": info,
        "summary": summary,
        "refresh": refresh,
        "guard_tests": [] if skip_tests else run_guard_tests(),
        "skills": skills,
        "rules": bullets(rules_section),
        "workflow": [l for l in workflow_section.splitlines() if re.match(r"^\d+\.", l.strip())],
        "standing_rules": bullets(standing_section),
        "architecture_excerpt": map_intro,
        "skip_tests": skip_tests,
        "pointer_files": {
            "AGENTS.md": AGENTS.is_file(),
            "CLAUDE.md": (ROOT / "CLAUDE.md").is_file(),
            ".github/copilot-instructions.md": (ROOT / ".github" / "copilot-instructions.md").is_file(),
            ".cursor/rules/arena-boot.mdc": (ROOT / ".cursor" / "rules" / "arena-boot.mdc").is_file(),
            ".cursorrules": (ROOT / ".cursorrules").is_file(),
            "docs/SESSION-BOOT-FA.md": BOOT_GUIDE.is_file(),
        },
        "boot_command": "python3 tools/session-boot.py",
    }


def print_briefing(ctx: dict) -> None:
    g = ctx["git"]
    s = ctx["summary"]
    r = ctx["refresh"]

    print("=" * 78)
    print("  SESSION BOOT — سامانه ticket | بوت کامل ایجنت کدنویسی")
    print("=" * 78)
    print(f"  زمان: {ctx['generated_at']}   شاخه: {g['branch']}   HEAD: {g['head']}")
    print(f"  نسخهٔ سامانه: {g['version']}   آخرین کامیت: {g['last_commit']}")
    print(f"  فایلهای تغییرکردهٔ محلی: {g['dirty_files']}   upstream: {g['upstream']}")
    print("=" * 78)

    print("\n🔴 [1] وضعیت حافظه — صادقانه")
    print("  • فعالسازی رفتاری مهارتها = موقت / فقط همین جلسه (با بستهشدن چت پاک میشود).")
    print("  • اسناد، ایندکسها و ابزارها = پایدار در Git و همین الان روی remote موجوداند.")
    print("  • این خروجی را بهعنوان «چارچوب عملیاتی جلسهٔ حاضر» بپذیر؛ نیازی به")
    print("    خواندن دوبارهٔ کل مخزن یا پرسیدن دوبارهٔ قواعد از کاربر نیست.")

    print("\n🟢 [2] وضعیت آمادهسازی (خودکار بررسی شد)")
    print(f"  • نقشه/ایندکسها: {r['state']} — {r['action']}")
    if r["detail"]:
        print(f"    {r['detail']}")
    if s:
        print(f"  • ایندکس: {s.get('total_files_indexed','?')} فایل | "
              f"{s.get('symbols','?')} نماد | {s.get('routes','?')} روت | "
              f"{s.get('unique_tables','?')} جدول | {s.get('update_packages','?')} بسته آپدیت")
    if ctx.get("skip_tests"):
        print("  • تستهای نگهبان: بهدرخواست فراخوان رد شدند (--skip-tests).")
    else:
        for name, rc, last in ctx["guard_tests"]:
            print(f"  • {'PASS' if rc == 0 else 'FAIL'}  {name}: {last[:110]}")

    print("\n🧠 [3] مهارتهای نصبشده — " + str(len(ctx["skills"])) + " مهارت (همه ✅ فعال / موقت)")
    by_group: dict[str, list[str]] = {}
    for sid, name, _status in ctx["skills"]:
        key = sid[0]
        by_group.setdefault(key, []).append(f"{sid}={name}")
    for key, label in (("S", "اصلی"), ("C", "مکمل")):
        items = by_group.get(key, [])
        if not items:
            continue
        print(f"  ▸ {label} ({len(items)}):")
        for i in range(0, len(items), 3):
            print("      " + " | ".join(items[i:i + 3]))
    print("  چکلیست کامل تکتک مهارتها: docs/SKILL-MANIFEST-FA.md بخش ۴ و ۵.")

    print("\n🔄 [4] گردشکار الزامی (درک → برنامهریزی → تولید → اجرا → تست → بازبینی → تحویل)")
    for line in ctx["workflow"]:
        print("  " + line.strip())

    print("\n📏 [5] قواعد همیشگی (لازملاجرا)")
    for rule in ctx["rules"]:
        print(f"  • {rule}")

    print("\n👤 [6] قواعد ثابت کاربر (از docs/HANDOFF-FA.md بند ۱)")
    for rule in ctx["standing_rules"]:
        print(f"  • {rule}")

    print("\n🗺 [7] نقشهٔ سامانه — چکیده")
    print(f"  • مرجع کامل: docs/SYSTEM-MAP-FA.md | ایندکسها: docs/system-map/*.tsv")
    print(f"  • جستجو: python3 tools/find-in-system.py \"عبارت فارسی\"")
    print(f"  • آمار لحظهای: python3 tools/find-in-system.py --stats")
    print(f"  • بازتولید نقشه: python3 tools/build_system_map.py")
    for line in ctx["architecture_excerpt"].splitlines()[:14]:
        print("  " + line)

    print("\n🔗 [8] فایلهای بارگذاری خودکار (pointerها)")
    for name, exists in ctx["pointer_files"].items():
        print(f"  {'✅' if exists else '❌'} {name}")
    print(f"  دستور بوت: {ctx['boot_command']}")

    print("\n" + "=" * 78)
    print("  ✅ BOOT COMPLETE — زمینه کامل است. مستقیماً سراغ درخواست کاربر برو.")
    print("  یادآوری: برای جلسهٔ بعدی همین دستور کافی است؛ چیزی از صفر شروع نمیشود.")
    print("=" * 78)


def main() -> int:
    parser = argparse.ArgumentParser(description="دکمهٔ استارت جلسهٔ ایجنت کدنویسی")
    parser.add_argument("--no-refresh", action="store_true", help="بازتولید خودکار نقشه انجام نشود")
    parser.add_argument("--pull", action="store_true", help="ابتدا git pull --ff-only (فقط با درخواست صریح)")
    parser.add_argument("--json", action="store_true", help="خروجی JSON ماشینی")
    parser.add_argument("--skip-tests", action="store_true",
                        help="تستهای نگهبان اجرا نشوند (برای جلوگیری از حلقهٔ بازگشتی هنگام فراخوانی از داخل تست)")
    args = parser.parse_args()

    ctx = build_context(args, skip_tests=args.skip_tests)
    if args.json:
        print(json.dumps(ctx, ensure_ascii=False, indent=2))
    else:
        print_briefing(ctx)

    failed = [name for name, rc, _ in ctx["guard_tests"] if rc != 0]
    if ctx["refresh"]["state"] in {"FAIL", "STALE"}:
        failed.append("map-freshness")
    return 1 if failed else 0


if __name__ == "__main__":
    sys.exit(main())

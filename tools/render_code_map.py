#!/usr/bin/env python3
"""ساخت docs/CODE-MAP-FA.md از docs/code-index.json.

نقشهٔ خوانا عمداً «تولیدشده» است نه دست‌نویس: نقشهٔ دست‌نویس ظرف چند
هفته از کد عقب می‌افتد و اعتماد کاذب می‌سازد.
"""
import json
from collections import Counter
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
idx = json.loads((ROOT / "docs/code-index.json").read_text(encoding="utf-8"))
ledger = json.loads((ROOT / "docs/code-audit-ledger.json").read_text(encoding="utf-8"))
reviewed = ledger.get("reviewed", {})

AREA_FA = {
    "CORE": "موتور مشترک (lib/)", "ACT": "کنش‌ها (actions/)", "PAGE": "صفحات (ریشه)",
    "CRON": "زمان‌بندها (cron/)", "API": "رابط برنامه (api/)",
    "ASSET": "دارایی‌های ایستا (assets/)", "WEB": "پنل وب غذا (food-ticket-web/)",
}
FLAG_FA = {
    "silent_catch": "catch بی‌صدا", "sql_concat": "الحاق در SQL", "raw_input": "ورودی خام",
    "unescaped_echo": "چاپ بدون فرار", "error_suppression": "سرکوب @",
    "dangerous_call": "فراخوانی خطرناک", "write_no_tx": "نوشتن بدون تراکنش",
    "very_long": ">۳۰۰ خط", "long": ">۱۲۰ خط",
}
KIND_FA = {"function": "تابع", "method": "متد", "class": "کلاس", "interface": "واسط",
           "trait": "trait", "region": "ناحیه", "block": "بلوک"}

blocks = [(f, b) for f in idx["files"] for b in f["blocks"]]
total_lines = sum(f["lines"] for f in idx["files"])
done = sum(1 for f, b in blocks if b["id"] in reviewed)
done_lines = sum(b["lines"] for f, b in blocks if b["id"] in reviewed)

out = []
w = out.append
w("# نقشهٔ کد سامانه — فهرست آدرس‌دهی\n")
w("> این فایل **تولید می‌شود**. دستی ویرایشش نکنید.\n>")
w("> بازتولید: `php tools/code_index.php > docs/code-index.json`"
  " سپس `python tools/render_code_map.py`\n")
w("## وضعیت بازبینی\n")
w("| سنجه | مقدار |")
w("|---|---|")
w(f"| فایل‌های کد تولیدی | {len(idx['files'])} |")
w(f"| خطوط | {total_lines:,} |")
w(f"| آدرس‌ها (بلوک) | {len(blocks):,} |")
w(f"| بازبینی‌شده | {done:,} آدرس ({done/len(blocks)*100:.1f}%) — {done_lines:,} خط ({done_lines/total_lines*100:.1f}%) |")
w(f"| باقی‌مانده | {len(blocks)-done:,} آدرس |\n")

w("## چطور از این فهرست استفاده کنم؟\n")
w("هر آدرس شکل `ناحیه-شمارهٔ‌فایل.شمارهٔ‌بلوک` دارد؛ مثلاً `CORE-008.14`.\n")
w("- **`CORE`** ناحیه است (جدول زیر).")
w("- **`008`** شمارهٔ فایل در آن ناحیه.")
w("- **`14`** شمارهٔ بلوک داخل فایل، به ترتیب خط.\n")
w("برای رفتن به یک آدرس، در همین فایل `Ctrl+F` بزنید و شناسه را بچسبانید؛"
  " مسیر فایل و شمارهٔ خط کنارش نوشته شده.\n")
w("**لنگر پایدار، شمارهٔ خط کمکی:** ستون «لنگر» نام تابع یا عنوان بلوک است"
  " و با جابه‌جا شدن کد عوض نمی‌شود. شمارهٔ خط فقط برای پرش سریع است و با"
  " هر بازتولید تازه می‌شود.\n")

w("## ناحیه‌ها\n")
w("| ناحیه | معنی | فایل | بلوک | خط |")
w("|---|---|---|---|---|")
ac, ab, al = Counter(), Counter(), Counter()
for f in idx["files"]:
    ac[f["area"]] += 1
    ab[f["area"]] += len(f["blocks"])
    al[f["area"]] += f["lines"]
for a, c in ac.most_common():
    w(f"| `{a}` | {AREA_FA.get(a, a)} | {c} | {ab[a]:,} | {al[a]:,} |")
w("")

w("## نشانه‌های ریسک\n")
w("امتیاز ریسک فقط **ترتیب بازبینی** را تعیین می‌کند؛ قضاوت نهایی نیست."
  " یک بلوک پرامتیاز می‌تواند کاملاً سالم باشد و یک بلوک کم‌امتیاز باگ حیاتی داشته باشد.\n")
w("| نشانه | معنی | بلوک |")
w("|---|---|---|")
fl = Counter(k for f, b in blocks for k in b["risk"]["flags"])
for k, c in fl.most_common():
    w(f"| `{k}` | {FLAG_FA.get(k, k)} | {c:,} |")
w("")

w("## ترتیب بازبینی — ۴۰ آدرس پرخطر\n")
w("| # | آدرس | مسیر:خط | لنگر | ریسک | نشانه‌ها | وضعیت |")
w("|---|---|---|---|---|---|---|")
for i, (f, b) in enumerate(sorted(blocks, key=lambda x: -x[1]["risk"]["score"])[:40], 1):
    fg = "، ".join(FLAG_FA.get(k, k) for k in b["risk"]["flags"])
    st = "✅ بازبینی‌شده" if b["id"] in reviewed else "⬜ در نوبت"
    w(f"| {i} | `{b['id']}` | `{f['path']}:{b['line_start']}` | {b['anchor'][:38]} "
      f"| {b['risk']['score']} | {fg} | {st} |")
w("")

w("## فهرست کامل آدرس‌ها\n")
for area, _ in ac.most_common():
    w(f"\n### {AREA_FA.get(area, area)} — `{area}`\n")
    for f in [x for x in idx["files"] if x["area"] == area]:
        rv = sum(1 for b in f["blocks"] if b["id"] in reviewed)
        w(f"\n<details>\n<summary><b>{f['id']}</b> — <code>{f['path']}</code> "
          f"({f['lines']:,} خط، {len(f['blocks'])} آدرس، بازبینی‌شده {rv}/{len(f['blocks'])})</summary>\n")
        w("| آدرس | خط | نوع | لنگر | ریسک | وضعیت |")
        w("|---|---|---|---|---|---|")
        for b in f["blocks"]:
            st = "✅" if b["id"] in reviewed else "⬜"
            r = b["risk"]["score"]
            rs = f"**{r}**" if r >= 8 else str(r)
            anchor = b["anchor"].replace("|", "\\|")[:50]
            w(f"| `{b['id']}` | {b['line_start']}–{b['line_end']} | {KIND_FA.get(b['kind'], b['kind'])} "
              f"| `{anchor}` | {rs} | {st} |")
        w("\n</details>")
w("")

(ROOT / "docs/CODE-MAP-FA.md").write_text("\n".join(out) + "\n", encoding="utf-8")
print(f"نوشته شد: docs/CODE-MAP-FA.md ({len(out)} خط)")

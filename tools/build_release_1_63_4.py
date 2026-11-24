#!/usr/bin/env python3
"""Build the refreshed 1.63.4 full-install and incremental update packages."""
from __future__ import annotations
import hashlib, json, subprocess, zipfile
from pathlib import Path
ROOT=Path(__file__).resolve().parents[1]
VERSION='1.63.4'; REQUIRES='1.63.3'; DATE=(2026,11,19,12,0,0)
EXCLUDED=('downloads/','updates/','patches/','preview/','storage/','.git/','node_modules/','vendor/','.venv/','coverage/','build/','dist/')
MIGRATIONS=('041-food-monthly-prices-and-leftover-allocations.sql','042-food-invoice-finalization-state.sql')
REQUIRED_FILES=(
    'VERSION','assets/food-menu.css','assets/food-menu.js',
    'downloads/CHANGE-REPORT-FA-1.63.4-r3.md','food-reconciliation.php','food-ticket.php',
    'food-ticket-web/assets/food-menu.css','food-ticket-web/assets/food-menu.js','food-ticket-web/index.html',
    'install-empty-db.sql','lib/release-notes.php','schema.sql',
    'tests/test_food_absent_lunch_backend_static.py','tests/test_food_absent_lunch_tab_static.py',
    'tests/test_food_reconciliation_1_63_4_static.py','tools/build_release_1_63_4.py',
)

def tracked_paths(args):
    raw=subprocess.run(['git',*args],cwd=ROOT,check=True,capture_output=True).stdout.split(b'\0')
    return [x.decode() for x in raw if x]
def allowed(n): return not n.startswith(EXCLUDED) and not n.endswith(('.zip','.sha256','.pyc')) and '__pycache__' not in n.split('/')
def digest(b): return hashlib.sha256(b).hexdigest()
def zip_write(path, entries):
    path.parent.mkdir(parents=True,exist_ok=True)
    with zipfile.ZipFile(path,'w',zipfile.ZIP_DEFLATED,compresslevel=9) as z:
        for name,data in sorted(entries.items()):
            info=zipfile.ZipInfo(name,DATE);info.compress_type=zipfile.ZIP_DEFLATED;info.external_attr=0o644<<16;z.writestr(info,data)
    sha=digest(path.read_bytes());path.with_suffix(path.suffix+'.sha256').write_text(f'{sha}  {path.name}\n',encoding='ascii')
    return sha

def main():
    if (ROOT/'VERSION').read_text().strip()!=VERSION: raise SystemExit('VERSION must remain 1.63.4')
    tracked=tracked_paths(['ls-files','-z'])
    full={}
    for n in tracked:
        if allowed(n) and (ROOT/n).is_file(): full[n]=(ROOT/n).read_bytes()
    for n in tracked_paths(['ls-files','--others','--exclude-standard','-z']):
        if allowed(n) and (ROOT/n).is_file(): full[n]=(ROOT/n).read_bytes()
    note='''بستهٔ سورس کامل ۱.۶۳.۴ — نصب تازه

شامل سورس جاری برنامه، اسکیما و مهاجرت‌های ۰۴۱ و ۰۴۲ برای قیمت‌های ماهانه، تخصیص فردی غذای مازاد، ممیزی تغییرها و تشخیص صریح نهایی‌شدن فاکتور است. پنجرهٔ مانده و گزارش غایبین اصلاح شده‌اند؛ نمودار گزارش قابلیت چاپ دارد و درصد غایبین با کل سفارش‌های بازه مقایسه می‌شود. فایل config.php و داده‌های storage عمداً در بسته نیستند. پیش از نصب از دیتابیس و فایل‌ها نسخهٔ پشتیبان تهیه کنید.

نصب: ZIP را در htdocs استخراج کنید، سپس install.php را از خود سرور اجرا کنید.
'''
    full['00-FULL-PACKAGE-1.63.4-FA.txt']=note.encode()
    report=ROOT/'downloads/CHANGE-REPORT-FA-1.63.4-r3.md'
    if report.is_file(): full['downloads/CHANGE-REPORT-FA-1.63.4-r3.md']=report.read_bytes()
    out=ROOT/'downloads'; full_path=out/'ticket-full-1.63.4-r3-absence-range-chart-print-20261119.zip'
    full_sha=zip_write(full_path,{'ticket/'+k:v for k,v in full.items()})
    changed=set(tracked_paths(['diff','--name-only','HEAD','-z']))|set(tracked_paths(['ls-files','--others','--exclude-standard','-z']))
    changed={n for n in changed if allowed(n) and (ROOT/n).is_file()}
    changed.update(REQUIRED_FILES)
    files={n:(ROOT/n).read_bytes() for n in sorted(changed) if (ROOT/n).is_file()}
    migration_bytes={name:Path('migrations/'+name).read_bytes() for name in MIGRATIONS}
    for name,data in migration_bytes.items(): files['migrations/'+name]=data
    files['VERSION']=b'1.63.4\n'
    manifest={'name':'food-absence-range-chart-print-leftover-ui-r3','version':VERSION,'requires_version':REQUIRES,'date':'2026-11-19','title':'اصلاحات ۱.۶۳.۴: پنجرهٔ مانده، مقایسهٔ غایبین با سفارش‌های بازه و چاپ نمودار','migrations':list(MIGRATIONS),'files':sorted(files),'sha256':{n:digest(files[n]) for n in sorted(files)},'description':'Refreshes version 1.63.4 without changing its number. Aligns leftover personnel names and checkboxes, normalizes absent report field controls, adds ordered-person and ordered-portion range denominators and bold chart ratios, and includes the enabled chart in print output.'}
    update={'manifest.json':(json.dumps(manifest,ensure_ascii=False,indent=2)+'\n').encode()}
    update.update({'migrations/'+name:data for name,data in migration_bytes.items()})
    update.update({'files/'+n:b for n,b in files.items()})
    upd_path=out/'ticket-update-1.63.4-r3-absence-range-chart-print-20261119.zip'
    upd_sha=zip_write(upd_path,update)
    print(f'FULL {full_path.name} files={len(full)} bytes={full_path.stat().st_size} sha256={full_sha}')
    print(f'UPDATE {upd_path.name} files={len(files)} bytes={upd_path.stat().st_size} sha256={upd_sha}')
if __name__=='__main__': main()

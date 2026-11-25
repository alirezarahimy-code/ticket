#!/usr/bin/env python3
"""Build the combined 1.63.4 full-install and incremental update packages."""
from __future__ import annotations
import hashlib, json, subprocess, zipfile
from pathlib import Path
ROOT=Path(__file__).resolve().parents[1]
VERSION='1.63.4'; REQUIRES='1.63.3'; DATE=(2026,11,19,12,0,0)
SLUG='r7-combined-deputy-scope'
REPORT='downloads/CHANGE-REPORT-FA-1.63.4-r7-combined.md'
EXCLUDED=('downloads/','updates/','patches/','preview/','storage/','.git/','node_modules/','vendor/','.venv/','coverage/','build/','dist/')
MIGRATIONS=('041-food-monthly-prices-and-leftover-allocations.sql','042-food-invoice-finalization-state.sql')
REQUIRED_FILES=(
    'VERSION','assets/food-menu.css','assets/food-menu.js','assets/cd-dvd.js','cd-dvd.php',
    'downloads/CHANGE-REPORT-FA-1.63.4-r6.md',REPORT,
    'food-reconciliation.php','food-ticket.php','food-ticket-order-source.php','permissions.php',
    'food-ticket-web/assets/food-menu.css','food-ticket-web/assets/food-menu.js','food-ticket-web/index.html',
    'install-empty-db.sql','lib/release-notes.php','schema.sql',
    'tests/test_cd_dvd_exit_sheet_required_static.py',
    'tests/test_cd_dvd_incoming_and_permissions_static.py',
    'tests/test_cd_dvd_reusable_serial_and_history_separator.py',
    'tests/test_food_absent_lunch_backend_static.py',
    'tests/test_food_absent_lunch_deputy_scope_static.py',
    'tests/test_food_absent_lunch_tab_static.py',
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
    note='''بستهٔ سورس کامل و تجمیعی ۱.۶۳.۴ — نصب تازه

این بسته اصلاحات گزارش‌ها و فاکتور، رابط تطبیق، CD/DVD و دامنهٔ سروری گزارش غایبین ناهار را یکجا دارد. شماره نسخه تغییر نکرده است. دامنهٔ مدیر معاونت از چارت جاری سرور ساخته می‌شود؛ گزارش گذشته نیز بر اساس چارت فعلی است. آزمون static موفق است، اما آزمون integration نقش‌ها باید پیش از بهره‌برداری در staging انجام شود. فایل‌های config.php و داده‌های runtime/storage عمداً بسته‌بندی نشده‌اند. پیش از نصب از دیتابیس و فایل‌ها پشتیبان بگیرید.

نصب: ZIP را در مسیر نصب برنامه استخراج کنید و مراحل استاندارد نصب تازه را از خود سرور اجرا کنید.
'''
    full['00-FULL-PACKAGE-1.63.4-FA.txt']=note.encode()
    report=ROOT/REPORT
    if report.is_file(): full[REPORT]=report.read_bytes()
    out=ROOT/'downloads'; full_path=out/f'ticket-full-1.63.4-{SLUG}-20261119.zip'
    full_sha=zip_write(full_path,{'ticket/'+k:v for k,v in full.items()})
    changed=set(tracked_paths(['diff','--name-only','HEAD','-z']))|set(tracked_paths(['ls-files','--others','--exclude-standard','-z']))
    changed={n for n in changed if allowed(n) and (ROOT/n).is_file()}
    changed.update(REQUIRED_FILES)
    files={n:(ROOT/n).read_bytes() for n in sorted(changed) if (ROOT/n).is_file()}
    for name in MIGRATIONS: files['migrations/'+name]=Path('migrations/'+name).read_bytes()
    files['VERSION']=b'1.63.4\n'
    manifest={'name':'food-1.63.4-'+SLUG,'version':VERSION,'requires_version':REQUIRES,'date':'2026-11-19','title':'بستهٔ تجمیعی ۱.۶۳.۴: اصلاحات رابط، CD/DVD و دامنهٔ معاونت غایبین ناهار','migrations':list(MIGRATIONS),'files':sorted(files),'sha256':{n:digest(files[n]) for n in sorted(files)},'description':'Combined 1.63.4 update: retains the existing food reconciliation/invoice and CD/DVD changes, adds server-enforced active deputy-tree scoping for absent-lunch APIs, aggregate output, charts, export and print. Historical dates use the current organization chart. Role runtime integration testing remains required in staging.'}
    update={'manifest.json':(json.dumps(manifest,ensure_ascii=False,indent=2)+'\n').encode()}
    update.update({'migrations/'+name:data for name,data in ((n,Path('migrations/'+n).read_bytes()) for n in MIGRATIONS)})
    update.update({'files/'+n:b for n,b in files.items()})
    upd_path=out/f'ticket-update-1.63.4-{SLUG}-20261119.zip'
    upd_sha=zip_write(upd_path,update)
    print(f'FULL {full_path.name} files={len(full)} bytes={full_path.stat().st_size} sha256={full_sha}')
    print(f'UPDATE {upd_path.name} files={len(files)} bytes={upd_path.stat().st_size} sha256={upd_sha}')
if __name__=='__main__': main()

#!/usr/bin/env python3
"""Build deterministic 1.63.5 full-install and incremental update archives."""
from __future__ import annotations
import hashlib, json, subprocess, zipfile
from pathlib import Path
ROOT=Path(__file__).resolve().parents[1]
VERSION='1.63.5'; REQUIRES='1.63.4'; DATE=(2026,11,19,12,0,0)
EXCLUDED=('downloads/','updates/','patches/','preview/','storage/','.git/','node_modules/','vendor/','.venv/','coverage/','build/','dist/')

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
    if (ROOT/'VERSION').read_text().strip()!=VERSION: raise SystemExit('Set VERSION to 1.63.5 before building')
    tracked=tracked_paths(['ls-files','-z'])
    full={}
    for n in tracked:
        if allowed(n) and (ROOT/n).is_file(): full[n]=(ROOT/n).read_bytes()
    for n in tracked_paths(['ls-files','--others','--exclude-standard','-z']):
        if allowed(n) and (ROOT/n).is_file(): full[n]=(ROOT/n).read_bytes()
    note='''بستهٔ سورس کامل ۱.۶۳.۵ — نصب تازه

شامل سورس جاری برنامه، اسکیما و مهاجرت ۰۴۲ برای تفکیک فاکتور نهایی از رکورد قدیمی، اصلاح قفل ماه، تخصیص مطمئن غذای باقیمانده و جمع مبلغ گزارش غایبین است. فایل config.php و داده‌های storage عمداً در بسته نیستند. پیش از نصب از دیتابیس و فایل‌ها نسخهٔ پشتیبان تهیه کنید.

نصب: ZIP را در htdocs استخراج کنید، سپس install.php را از خود سرور اجرا کنید.
'''
    full['00-FULL-PACKAGE-1.63.5-FA.txt']=note.encode()
    report=ROOT/'downloads/CHANGE-REPORT-FA-1.63.5.md'
    if report.is_file(): full['downloads/CHANGE-REPORT-FA-1.63.5.md']=report.read_bytes()
    out=ROOT/'downloads'; full_path=out/'ticket-full-1.63.5-20261119.zip'
    full_sha=zip_write(full_path,{'ticket/'+k:v for k,v in full.items()})
    changed=set(tracked_paths(['diff','--name-only','HEAD','-z']))|set(tracked_paths(['ls-files','--others','--exclude-standard','-z']))
    changed={n for n in changed if allowed(n) and (ROOT/n).is_file()}
    changed.add('downloads/CHANGE-REPORT-FA-1.63.5.md')
    files={n:(ROOT/n).read_bytes() for n in sorted(changed) if (ROOT/n).is_file()}
    mig=Path('migrations/042-food-invoice-finalization-state.sql').read_bytes()
    files['migrations/042-food-invoice-finalization-state.sql']=mig
    files['VERSION']=b'1.63.5\n'
    manifest={'name':'food-final-invoice-lock-leftover-and-absent-totals','version':VERSION,'requires_version':REQUIRES,'date':'2026-11-19','title':'اصلاح قفل نهایی فاکتور، پنجرهٔ تخصیص مانده و جمع مبلغ غایبین','migrations':['042-food-invoice-finalization-state.sql'],'files':sorted(files),'sha256':{n:digest(files[n]) for n in sorted(files)},'description':'Separates finalized invoices from legacy rows; finalizes old invoice rows and monthly prices transactionally, preserves leftover allocations, fixes modal layout, and sums absent meal amounts by report and department.'}
    update={'manifest.json':(json.dumps(manifest,ensure_ascii=False,indent=2)+'\n').encode(), 'migrations/042-food-invoice-finalization-state.sql':mig}
    update.update({'files/'+n:b for n,b in files.items()})
    upd_path=out/'ticket-update-1.63.4-to-1.63.5-food-invoice-leftover-absent-20261119.zip'
    upd_sha=zip_write(upd_path,update)
    print(f'FULL {full_path.name} files={len(full)} bytes={full_path.stat().st_size} sha256={full_sha}')
    print(f'UPDATE {upd_path.name} files={len(files)} bytes={upd_path.stat().st_size} sha256={upd_sha}')
if __name__=='__main__': main()

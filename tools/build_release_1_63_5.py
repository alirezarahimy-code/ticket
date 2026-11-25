#!/usr/bin/env python3
"""Build cumulative 1.63.5 update and full-source ZIPs from the current tree."""
from __future__ import annotations
import hashlib
import json
import subprocess
import zipfile
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
VERSION = '1.63.5'
REQUIRES = '1.63.3'
DATE = (2026, 11, 19, 12, 0, 0)
SLUG = 'r1-cumulative-cd-dvd-serial'
BASE_PACKAGE = ROOT / 'downloads/ticket-update-1.63.4-r7-combined-deputy-scope-20261119.zip'
REPORT = 'downloads/CHANGE-REPORT-FA-1.63.5-r1-cumulative.md'
MIGRATIONS = ('041-food-monthly-prices-and-leftover-allocations.sql', '042-food-invoice-finalization-state.sql')
EXCLUDED = ('downloads/', 'updates/', 'patches/', 'preview/', 'storage/', '.git/', 'node_modules/', 'vendor/', '.venv/', 'coverage/', 'build/', 'dist/')
ADDITIONS = (
    'README.md', 'VERSION', 'lib/release-notes.php', 'cd-dvd.php', 'assets/cd-dvd.js',
    'tests/test_cd_dvd_exit_sheet_required_static.py',
    'tests/test_cd_dvd_reusable_serial_and_history_separator.py',
    'tools/build_release_1_63_5.py', REPORT,
)

def sha256(data: bytes) -> str:
    return hashlib.sha256(data).hexdigest()

def git_paths(*args: str) -> list[str]:
    result = subprocess.run(['git', *args], cwd=ROOT, check=True, capture_output=True).stdout
    return [item.decode() for item in result.split(b'\0') if item]

def allowed(path: str) -> bool:
    return (not path.startswith(EXCLUDED)
            and not path.endswith(('.zip', '.sha256', '.pyc'))
            and '__pycache__' not in path.split('/'))

def zip_write(path: Path, entries: dict[str, bytes]) -> str:
    path.parent.mkdir(parents=True, exist_ok=True)
    with zipfile.ZipFile(path, 'w', zipfile.ZIP_DEFLATED, compresslevel=9) as archive:
        for name, content in sorted(entries.items()):
            info = zipfile.ZipInfo(name, DATE)
            info.compress_type = zipfile.ZIP_DEFLATED
            info.external_attr = 0o644 << 16
            archive.writestr(info, content)
    digest = sha256(path.read_bytes())
    path.with_suffix(path.suffix + '.sha256').write_text(f'{digest}  {path.name}\n', encoding='ascii')
    return digest

def main() -> None:
    if (ROOT / 'VERSION').read_text(encoding='ascii').strip() != VERSION:
        raise SystemExit(f'ROOT VERSION must be {VERSION}')
    if not BASE_PACKAGE.is_file():
        raise SystemExit(f'cumulative base package missing: {BASE_PACKAGE.name}')
    with zipfile.ZipFile(BASE_PACKAGE) as archive:
        base_manifest = json.loads(archive.read('manifest.json'))
    base_files = set(base_manifest['files'])
    file_names = sorted(base_files | set(ADDITIONS) | {f'migrations/{name}' for name in MIGRATIONS})
    files: dict[str, bytes] = {}
    for name in file_names:
        path = ROOT / name
        if not path.is_file():
            raise SystemExit(f'required cumulative update file missing: {name}')
        files[name] = path.read_bytes()
    files['VERSION'] = (VERSION + '\n').encode('ascii')
    if not base_files <= set(files):
        raise SystemExit('1.63.4 cumulative payload was not fully carried forward')

    manifest = {
        'name': f'ticket-update-1.63.5-{SLUG}',
        'version': VERSION,
        'requires_version': REQUIRES,
        'date': '2026-11-19',
        'title': 'به‌روزرسانی تجمیعی ۱٫۶۳٫۵: گزارش‌های ۱٫۶۳٫۴ و اصلاح ثبت سریال CD/DVD برگشتی',
        'migrations': list(MIGRATIONS),
        'release_status': 'RELEASE',
        'files': sorted(files),
        'sha256': {name: sha256(files[name]) for name in sorted(files)},
        'description': 'Cumulative 1.63.5 update carries forward all files from the verified 1.63.4-r7 package, including the 041/042 migrations and deputy-scoped absent-lunch report. Fixes CD/DVD serial typing, resolves short numeric incoming identifiers to an existing P86 serial, prevents silent next-number substitution on edit, and serializes writes with database locks.',
    }
    update_entries = {'manifest.json': (json.dumps(manifest, ensure_ascii=False, indent=2) + '\n').encode('utf-8')}
    for migration in MIGRATIONS:
        update_entries[f'migrations/{migration}'] = files[f'migrations/{migration}']
    for name, content in files.items():
        update_entries[f'files/{name}'] = content
    output = ROOT / 'downloads'
    update_path = output / f'ticket-update-1.63.5-{SLUG}-20261119.zip'
    update_sha = zip_write(update_path, update_entries)

    source_paths = set(git_paths('ls-files', '-z'))
    source_paths.update(git_paths('ls-files', '--others', '--exclude-standard', '-z'))
    full: dict[str, bytes] = {}
    for name in sorted(source_paths):
        path = ROOT / name
        if allowed(name) and path.is_file():
            full[name] = path.read_bytes()
    full[REPORT] = (ROOT / REPORT).read_bytes()
    full['00-FULL-PACKAGE-1.63.5-FA.txt'] = (
        'بستهٔ کامل و تجمیعی ۱.۶۳.۵ برای نصب تازه. شامل همهٔ فایل‌های برنامه، '
        'مهاجرت‌های موجود، اصلاح گزارش‌های ۱.۶۳.۴، دامنهٔ معاونت غایبین و اصلاح سریال CD/DVD. '
        'نسخهٔ config.php و storage در بسته نیستند. پیش از نصب پشتیبان تهیه کنید.\n'
    ).encode('utf-8')
    full_path = output / f'ticket-full-1.63.5-{SLUG}-20261119.zip'
    full_sha = zip_write(full_path, {f'ticket/{name}': content for name, content in full.items()})
    print(f'UPDATE {update_path.name} entries={len(update_entries)} files={len(files)} bytes={update_path.stat().st_size} sha256={update_sha}')
    print(f'FULL {full_path.name} files={len(full)} bytes={full_path.stat().st_size} sha256={full_sha}')

if __name__ == '__main__':
    main()

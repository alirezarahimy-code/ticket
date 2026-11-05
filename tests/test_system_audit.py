"""تست خودِ ابزار ممیزی.

یک ابزار ممیزی که نتواند خراب شدن را تشخیص دهد، بدتر از نبودنش است: حس
امنیت کاذب می‌دهد. این تست‌ها مطمئن می‌شوند ابزار واقعاً کار می‌کند و
معیارهایش از خط مبنا عقب نیفتاده‌اند.
"""

from __future__ import annotations

import json
import re
import sys
from pathlib import Path

import pytest

ROOT = Path(__file__).resolve().parents[1]
sys.path.insert(0, str(ROOT / "tools"))

import system_audit  # noqa: E402


def test_every_check_returns_a_well_formed_result():
    for check in system_audit.CHECKS:
        result = check()
        assert result.key, f"{check.__name__} کلید ندارد"
        assert result.title, f"{check.__name__} عنوان ندارد"
        assert result.severity in system_audit.SEVERITY_ORDER, result.severity
        assert result.rationale, f"{check.__name__} توضیح «چرا مهم است» ندارد"
        assert result.metric >= 0
        assert isinstance(result.findings, list)


def test_check_keys_are_unique():
    keys = [check().key for check in system_audit.CHECKS]
    assert len(keys) == len(set(keys)), f"کلید تکراری: {keys}"


def test_baseline_covers_every_check():
    """بررسی بدون خط مبنا یعنی پس‌رفتش دیده نمی‌شود."""
    baseline = system_audit.load_baseline()
    missing = [check().key for check in system_audit.CHECKS if check().key not in baseline]
    assert not missing, (
        f"این بررسی‌ها در tools/audit-baseline.json نیستند: {missing} — "
        "با «python3 tools/system_audit.py --update-baseline» ثبتشان کنید"
    )


def test_baseline_has_no_unknown_keys():
    """کلید یتیم یعنی بررسی حذف شده ولی خط مبنا تمیز نشده."""
    keys = {check().key for check in system_audit.CHECKS}
    orphan = sorted(set(system_audit.load_baseline()) - keys)
    assert not orphan, f"کلید بدون بررسی متناظر در خط مبنا: {orphan}"


def test_structural_checks_stay_at_zero():
    """بررسی‌های ساختاری که الان پاک‌اند باید پاک بمانند.

    این‌ها بودجه‌پذیر نیستند: هر مقدار غیرصفر یعنی نصب تازه خراب است.
    """
    must_be_zero = {
        "schema_parity",
        "code_table_references",
        "migration_guards",
        "release_consistency",
        "secrets",
        "filesystem_safety",
    }
    for check in system_audit.CHECKS:
        result = check()
        if result.key in must_be_zero:
            detail = [f"{f.where}: {f.detail}" for f in result.findings[:5]]
            assert result.metric == 0, f"{result.title} باید صفر بماند — {detail}"


def test_no_metric_regressed_against_baseline():
    baseline = system_audit.load_baseline()
    worse = []
    for check in system_audit.CHECKS:
        result = check()
        base = baseline.get(result.key)
        if base is not None and result.metric > base:
            worse.append(f"{result.title}: {base} → {result.metric}")
    assert not worse, (
        "معیارها نسبت به خط مبنا بدتر شده‌اند: " + "; ".join(worse) +
        " — یا علت را برطرف کنید یا خط مبنا را عمداً به‌روز کنید"
    )


def test_audit_detects_an_injected_regression(tmp_path, monkeypatch):
    """اثبات اینکه ممیزی واقعاً گیر می‌اندازد، نه اینکه همیشه سبز باشد."""
    clean = system_audit.check_silent_error_swallowing().metric

    original_tracked = system_audit._tracked
    injected = tmp_path / "injected.php"
    injected.write_text(
        "<?php\ntry { boom(); } catch (Throwable) {}\n", encoding="utf-8"
    )

    def fake_tracked(pattern: str):
        files = original_tracked(pattern)
        return files + [injected] if pattern == "*.php" else files

    monkeypatch.setattr(system_audit, "_tracked", fake_tracked)
    dirty = system_audit.check_silent_error_swallowing().metric
    assert dirty == clean + 1, (
        f"ممیزی catch بی‌صدای تزریق‌شده را ندید ({clean} → {dirty})"
    )


def test_check_mode_exits_nonzero_on_regression(tmp_path, monkeypatch):
    """حالت --check باید با کد غیرصفر خارج شود، وگرنه CI قرمز نمی‌شود."""
    fake = {k: 0 for k in (check().key for check in system_audit.CHECKS)}
    monkeypatch.setattr(system_audit, "load_baseline", lambda: fake)
    code = system_audit.main(["--markdown", str(tmp_path / "r.md")])
    assert code == 0, "بدون --check نباید شکست بدهد"
    code = system_audit.main(["--check", "--markdown", str(tmp_path / "r2.md")])
    assert code == 1, "با خط مبنای همه-صفر باید پس‌رفت گزارش کند"


def test_json_output_is_machine_readable(tmp_path):
    out = tmp_path / "audit.json"
    system_audit.main(["--json", str(out), "--markdown", str(tmp_path / "r.md")])
    data = json.loads(out.read_text(encoding="utf-8"))
    assert set(data) == {"metrics", "checks"}
    assert len(data["checks"]) == len(system_audit.CHECKS)
    for entry in data["checks"]:
        assert {"key", "title", "severity", "metric", "summary", "findings"} <= set(entry)


def test_workflow_runs_the_audit_on_a_schedule():
    workflow = (ROOT / ".github/workflows/periodic-audit.yml").read_text(encoding="utf-8")
    assert "schedule:" in workflow, "اجرای زمان‌بندی‌شده تعریف نشده"
    assert "cron:" in workflow
    assert "workflow_dispatch:" in workflow, "امکان اجرای دستی لازم است"
    assert "system_audit.py" in workflow
    assert "--check" in workflow, "بدون --check شکست گزارش نمی‌شود"


@pytest.mark.parametrize("doc", ["docs/ARCHITECTURE-FA.md"])
def test_audit_tool_is_documented(doc):
    text = (ROOT / doc).read_text(encoding="utf-8")
    assert "system_audit.py" in text, f"{doc} به ابزار ممیزی اشاره نمی‌کند"

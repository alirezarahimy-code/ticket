#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""نگهبان استاتیک قرارداد «تحویل فقط با لینک متنی مستقیم از GitHub».

قاعدهٔ کاربر (۲۰۲۶-۱۱-۱۹): تمام نسخهها و هر فایل تحویلی، الزاماً فقط با
همان روش — لینک متنی خام مستقیم از GitHub، پس از push — رسانده شود.
"""
from __future__ import annotations

from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
DOC = ROOT / 'docs' / 'DELIVERY-LINK-CONTRACT-FA.md'

ENTRYPOINTS = [
    'docs/HANDOFF-FA.md',
    'AGENTS.md',
    '.cursorrules',
    'CLAUDE.md',
    '.github/copilot-instructions.md',
    '.cursor/rules/arena-boot.mdc',
]


def read(rel: str) -> str:
    return (ROOT / rel).read_text(encoding='utf-8')


def section(text: str, heading: str) -> str:
    start = text.index(heading)
    nxt = text.find('\n## ', start + len(heading))
    end = text.find('\n---', start + len(heading))
    cuts = [x for x in (nxt, end) if x != -1]
    return text[start:min(cuts)] if cuts else text[start:]


def test_contract_document_is_complete():
    t = DOC.read_text(encoding='utf-8')
    assert 'غیرقابل مذاکره' in t
    assert 'لینک متنی' in t and 'URL خام' in t
    assert 'raw.githubusercontent.com' in t
    assert 'github.com/alirezarahimy-code/ticket/raw/refs/heads/' in t
    assert 'gh api' in t and '.sha256' in t
    assert 'git push origin' in t and 'push --force' in t  # force فقط بهعنوان ممنوعیت
    for banned in ['e2b.app', 'present_file', 'data:', 'Markdown', 'downloads/index.html']:
        assert banned in t, f'ممنوعیت ذکر نشده: {banned}'


def test_all_agent_entrypoints_reference_the_contract():
    for rel in ENTRYPOINTS:
        assert 'DELIVERY-LINK-CONTRACT-FA.md' in read(rel), rel


def test_boot_prints_the_rule_as_a_standing_user_rule():
    handoff = section(read('docs/HANDOFF-FA.md'), '## ۱. قواعد ثابت کار')
    assert 'لینک متنی خام' in handoff
    assert 'GitHub' in handoff
    agents = section(read('AGENTS.md'), '## قواعد همیشگی')
    assert 'قرارداد تحویل لینک' in agents


def test_existing_publishing_policy_stays_in_force():
    # قرارداد تازه جایگزین الزامات انتشار قدیمی نمیشود.
    t = DOC.read_text(encoding='utf-8')
    assert 'DOWNLOAD-PUBLISHING-POLICY-FA.md' in t
    assert 'جایگزین الزامات انتشار نمیشود' in t
    assert (ROOT / 'docs' / 'DOWNLOAD-PUBLISHING-POLICY-FA.md').is_file()

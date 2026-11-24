#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""نگهبان استاتیک نسخهٔ نهایی ۱٫۶۳٫۳ — تب «غایبین ناهار»."""
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
HTML = (ROOT / 'food-ticket-web/index.html').read_text(encoding='utf-8')
PHP = (ROOT / 'food-ticket.php').read_text(encoding='utf-8')
GUIDE = (ROOT / 'help-guide.php').read_text(encoding='utf-8')
BOT = (ROOT / 'chatbot.php').read_text(encoding='utf-8')
# فقط استایل/ماژول تزریقشدهٔ ۱٫۶۳٫۳ ملاک است
import re
_CSS_START = HTML.index('/* ۱٫۶۳٫۳')
_INJECTED = HTML[_CSS_START:HTML.index('</style>', _CSS_START)]
_MODULE = next((m for m in re.findall(r'<script>([\s\S]*?)</script>', HTML) if 'window.AbsentLunchReport=' in m), '')
BUNDLE = _INJECTED + _MODULE


def test_tab_is_inside_reports_page():
    assert "['absent-lunch','غایبین ناهار']" in HTML
    assert "tab==='absent-lunch')body=window.AbsentLunchReport?window.AbsentLunchReport.html()" in HTML
    assert "window.AbsentLunchReport.load(false)" in HTML
    assert "['absent-report','غایبین ناهار']" not in HTML
    assert "state.reportTab='absent-lunch'" in HTML


def test_pages_are_separate():
    assert 'alr-classic-chips' in HTML
    assert HTML.count('data-classic-chip=') >= 5
    assert 'alr-chips' in HTML


def test_panel_versions_match():
    assert "PANEL_VERSION='1.37.62'" in HTML
    assert "FOOD_TICKET_PANEL_VERSION = '1.37.62'" in PHP


def test_three_subviews_and_fields():
    for title in ('فهرست یکجا', 'بر اساس معاونت', 'تفکیک و جزئیات'):
        assert title in BUNDLE
    assert "detail:['row','pc','first','last','dep','food','jal','status','amount','priceState']" in BUNDLE
    assert "flat:['row','pc','first','last','dep','count','amountTotal','status']" in BUNDLE
    assert "dept:['row','pc','first','last','dep','count','amountTotal','status']" in BUNDLE
    assert "label:'وعده'" not in BUNDLE
    assert "label:'نوع غذا'" in BUNDLE
    assert 'alr-group-head h4 b' in BUNDLE


def test_multi_deputy_checkboxes():
    assert 'data-alr-all' in BUNDLE
    assert 'data-alr-dep-check=' in BUNDLE
    assert 'data-alr-dep-toggle=' in BUNDLE
    assert 'function syncIndeterminate(' in BUNDLE
    assert '.alr-row.off{opacity:.38}' in BUNDLE
    assert 'همه معاونتها' in HTML


def test_chart_is_compact_and_explicit():
    assert '<svg' not in BUNDLE
    assert '.alr-plot{border:1px solid' in BUNDLE
    assert 'alr-grid' in BUNDLE and 'alr-mean' in BUNDLE
    assert 'شروع بازه: ' in BUNDLE and 'پایان بازه: ' in BUNDLE
    assert 'alr-legend' in BUNDLE


def test_print_is_pure_and_reversible():
    # قانون چاپ باید به کلاس body.printing-absent-lunch محدود باشد تا چاپ سایر گزارش‌ها سفید نشود
    assert 'body.printing-absent-lunch>*:not(#alrPrint){display:none!important}' in BUNDLE
    assert 'body>*:not(#alrPrint)' not in BUNDLE
    assert 'printing-absent-lunch' in BUNDLE
    assert "'afterprint'" in BUNDLE
    assert 'data-no-sort' in BUNDLE
    assert '<h1>گزارش غایبین ناهار</h1>' in BUNDLE
    assert 'beforeprint' in BUNDLE
    assert 'width:100%!important' in BUNDLE and 'table-layout:fixed' in BUNDLE
    assert 'function percents(' in BUNDLE


def test_xlsx_and_docs():
    assert 'export_view_xlsx' in BUNDLE
    assert 'تفکیک و جزئیات' in GUIDE and 'همه معاونتها' in GUIDE
    assert "'food_absent_lunch_report'" in BOT


def test_absent_amount_totals_appear_in_flat_and_department_views():
    assert "amountValue:it.amount_irr==null?null:Number(it.amount_irr)" in BUNDLE
    assert "function amountSummary()" in BUNDLE
    assert "S.tab==='flat'||S.tab==='dept'?amountSummary()" in BUNDLE
    assert "مبلغ غذا: '+amountLabel(st)" in BUNDLE
    assert "قیمت نامشخص برای" in BUNDLE


def test_flat_and_department_show_per_person_absence_amount_totals_without_dates():
    assert "key:'amountTotal',label:'مجموع مبلغ (ریال)'" in BUNDLE
    assert "u.amountValue+=r.amountValue" in BUNDLE
    assert "u.amountMissingCount++" in BUNDLE
    assert "u.amountTotal=fa(u.amountValue)" in BUNDLE
    fields = BUNDLE[BUNDLE.index("var TAB_FIELDS="):BUNDLE.index("var FKEY=")]
    flat = fields.split("flat:[", 1)[1].split("]", 1)[0]
    dept = fields.split("dept:[", 1)[1].split("]", 1)[0]
    assert 'jal' not in flat and 'jal' not in dept
    assert 'amountTotal' in flat and 'amountTotal' in dept


def test_chart_has_range_ratios_and_prints_when_enabled():
    assert 'function chartSummaryHtml()' in BUNDLE
    assert "مجموع نفرات غایب = <b>" in BUNDLE
    assert "مجموع پرس غذا = <b>" in BUNDLE
    assert 'total_ordered_people' in BUNDLE and 'total_ordered_portions' in BUNDLE
    assert "(S.chart?'<section class=\"alr-chart alr-print-chart\"" in BUNDLE
    assert '.alr-chart-summary b' in HTML or '.alr-chart-summary b' in (ROOT / 'assets/food-menu.css').read_text()

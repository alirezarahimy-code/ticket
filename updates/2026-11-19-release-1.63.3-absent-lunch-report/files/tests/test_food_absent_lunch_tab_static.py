#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""نگهبان استاتیک ۱٫۶۳٫۳ — تب «غایبین ناهار» داخل صفحهٔ گزارشها."""
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
HTML = (ROOT / 'food-ticket-web/index.html').read_text(encoding='utf-8')
PHP = (ROOT / 'food-ticket.php').read_text(encoding='utf-8')
GUIDE = (ROOT / 'help-guide.php').read_text(encoding='utf-8')
BOT = (ROOT / 'chatbot.php').read_text(encoding='utf-8')


def test_tab_is_inside_reports_page():
    assert "['absent-lunch','غایبین ناهار']" in HTML
    assert "tab==='absent-lunch')body=window.AbsentLunchReport?window.AbsentLunchReport.html()" in HTML
    assert "window.AbsentLunchReport.load(false)" in HTML
    assert "['absent-report','غایبین ناهار']" not in HTML
    assert "state.reportTab='absent-lunch'" in HTML


def test_legacy_report_type_removed_and_redirected():
    assert 'option value="absent"' not in HTML
    assert "state.report.type==='absent'" in HTML


def test_panel_versions_match():
    assert "PANEL_VERSION='1.37.57'" in HTML
    assert "FOOD_TICKET_PANEL_VERSION = '1.37.57'" in PHP


def test_blocks_full_width_and_no_food_field():
    assert 'data-absent-lunch-block="' in HTML and '[1,2,3].map(function(n)' in HTML
    assert 'data-absent-lunch-field="food"' not in HTML
    assert 'width:100%!important' in HTML and 'table-layout:fixed' in HTML
    assert 'function percents(' in HTML and 'width:100%!important' in HTML
    assert 'function mapped(items,orgMap)' in HTML
    assert 'map=state.__orgMap||null' in HTML and 'window.FoodOrgGroups.groupOf(map,r)' in HTML


def test_print_is_scoped_to_this_tab():
    assert 'body.absent-lunch-printing' in HTML
    assert '#absentLunchPrint h1' in HTML
    assert 'گزارش غایبین ناهار' in HTML


def test_xlsx_and_permission():
    assert 'ITSM_LIST_SORT' in HTML
    assert "'reports' => $fpCan('food.reports')" in PHP
    assert 'export_view_xlsx' in (ROOT / 'index.php').read_text(encoding='utf-8')


def test_help_and_chatbot():
    assert 'گزارش مستقل غایبین ناهار' in GUIDE
    assert 'بر اساس معاونت' in GUIDE
    assert "'food_absent_lunch_report'" in BOT
    assert "'رفتن به گزارش غایبین ناهار'" in BOT

#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""نگهبان بکاند ۱٫۶۳٫۳ — بدون تغییر نسبت به بستهٔ اولیه، فقط ناهار."""
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
PHP = (ROOT / 'food-ticket.php').read_text(encoding='utf-8')


def test_backend_identity_and_declared_absence():
    start = PHP.index('function food_ticket_api_absent(')
    end = PHP.index('\nfunction ', start + 10)
    fn = PHP[start:end]
    assert "'nat:' . $nat" in fn and "'pc:' . $pc" in fn
    assert 'SELECT id FROM users WHERE employee_number = ?' in fn
    assert "'declared_absent'" in fn and "'status'" in fn
    assert "'full_name'" in fn
    assert "COALESCE(meal_type, 'lunch') = 'lunch'" in fn
    assert 'DROP' not in fn and 'DELETE' not in fn.upper()


def test_route_and_permission_unchanged():
    assert "$route === 'absent'" in PHP
    assert "'absent' => 'food.reports'" in PHP
    assert "FOOD_TICKET_PANEL_VERSION = '1.37.60'" in PHP

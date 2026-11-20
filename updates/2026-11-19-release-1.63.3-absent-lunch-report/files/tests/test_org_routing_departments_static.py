#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""نگاشت پایدار «معاونت سازمانی → ردیف رسیدگی (departments)».

- migration 040 ستون org_root_unit_id + ایندکس را idempotent اضافه می‌کند.
- شناسایی ردیف رسیدگی از این به بعد با شناسهٔ واحد چارت است (نه هم‌نام بودن) تا تغییر نام چارت/شرکت
  ردیف/گزارش/OLA را نشکند.
- نام ردیف رسیدگی شرکت همکار با پیشوند «نام شرکت.نام سرشاخه» است تا با معاونت هم‌نام شرکت اصلی تداخل نکند.
- شرکت اصلی بدون تغییر می‌ماند (همان نام چارت).
"""
import unittest
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent


def read(rel: str) -> str:
    return (ROOT / rel).read_text(encoding='utf-8')


class Migration040Test(unittest.TestCase):
    @classmethod
    def setUpClass(cls):
        cls.sql = read('migrations/040-org-routing-departments.sql')

    def test_ddl_is_idempotent(self):
        self.assertIn('ADD COLUMN IF NOT EXISTS org_root_unit_id', self.sql)
        self.assertIn('CREATE INDEX IF NOT EXISTS idx_departments_org_root_unit_id', self.sql)

    def test_no_destructive_statements(self):
        upper = self.sql.upper()
        for token in ('DROP TABLE', 'DROP COLUMN', 'DELETE FROM', 'TRUNCATE'):
            self.assertNotIn(token, upper)


class RoutingCodeTest(unittest.TestCase):
    @classmethod
    def setUpClass(cls):
        cls.org = read('organization.php')

    def test_map_lookup_comes_first(self):
        block = self.org[self.org.index('function org_unit_routing_department_id'):]
        map_pos = block.index('org_root_unit_id = ? LIMIT 1')
        fallback_pos = block.index('$candidates =')
        self.assertLess(map_pos, fallback_pos)

    def test_partner_prefix_rule(self):
        self.assertIn("function org_routing_department_name", self.org)
        self.assertIn("$companyName . '.' . $display", self.org)
        # سرشاخهٔ شرکت همکار خودِ شرکت است و پیشوند نمی‌گیرد
        self.assertIn("'company_manager') {\n        return $companyName;", self.org.replace('\r\n', '\n'))

    def test_partner_company_manager_legacy_claim(self):
        self.assertIn("'شرکت همکار'", self.org)

    def test_claim_is_scoped_and_best_effort_rename(self):
        self.assertIn('UPDATE departments SET org_root_unit_id = ? WHERE id = ? AND org_root_unit_id IS NULL', self.org)
        self.assertIn('if ($candidate !== $routingName)', self.org)

    def test_create_persists_map(self):
        self.assertIn('INSERT INTO departments (name, code, is_active, org_root_unit_id) VALUES (?, ?, 1, ?)', self.org)

    def test_map_availability_is_cached_and_fail_safe(self):
        self.assertIn('function org_routing_map_available', self.org)
        self.assertIn('SELECT org_root_unit_id FROM departments LIMIT 1', self.org)


if __name__ == '__main__':
    unittest.main()

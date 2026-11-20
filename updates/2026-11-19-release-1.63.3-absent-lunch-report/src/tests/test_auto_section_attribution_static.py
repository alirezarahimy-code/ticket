#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""قفل «بخش سازمانی / معاونت / بخش» از چارت سازمانی — ثبت درخواست و CD/DVD.

فقط نقش‌های ثبت به‌جای دیگران (agent/supervisor/support_manager/primary_admin)
می‌توانند بخش را دستی انتخاب کنند؛ بقیه — حتی مدیر — مقدار از چارت خوانده می‌شود.
مقدار نهایی سمت سرور دوباره محاسبه می‌شود؛ HTML فقط نمایش است.
"""
import unittest
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
OVERRIDE_CODES = "['agent', 'supervisor', 'support_manager', 'primary_admin']"


def read(rel: str) -> str:
    return (ROOT / rel).read_text(encoding='utf-8')


class OrgAttributionHelperTest(unittest.TestCase):
    @classmethod
    def setUpClass(cls):
        cls.org = read('organization.php')

    def test_override_role_list(self):
        self.assertIn('org_section_override_allowed', self.org)
        self.assertIn(OVERRIDE_CODES, self.org)

    def test_user_org_root_id_helper(self):
        self.assertIn('function user_org_root_id', self.org)
        self.assertIn('org_nearest_root_id($unitId, true)', self.org)


class TicketCreateLockTest(unittest.TestCase):
    @classmethod
    def setUpClass(cls):
        cls.create = read('modules/request-center/Pages/create.php')
        cls.actions = read('modules/request-center/Actions/ticket-actions.php')

    def test_form_readonly_for_non_override_roles(self):
        self.assertIn('readonly><input type="hidden" name="requesting_unit_id"', self.create)
        self.assertIn('به‌صورت خودکار از جایگاه شما در چارت سازمانی ثبت می‌شود', self.create)
        # انتخاب دستی فقط در شاخهٔ override نگه داشته شده است
        self.assertIn("'requesting_unit_id'", self.create)  # سرچسب فیلد مخفی

    def test_server_recomputes_from_chart_for_non_override(self):
        self.assertIn('org_section_override_allowed', self.actions)
        self.assertIn('org_nearest_root_id($myOrgUnitId, true)', self.actions)
        self.assertIn('بدون جایگاه', self.actions)

    def test_unplaced_user_not_blocked_but_override_still_required(self):
        # غیرستادی بدون جایگاه: درخواست با NULL ثبت می‌شود (نه خطا)
        self.assertIn("$requestingUnitId = ($myOrgUnitId > 0 && function_exists('org_nearest_root_id'))", self.actions)
        # استثناها همچنان انتخاب الزامی دارند
        self.assertIn("انتخاب «بخش سازمانی درخواست‌کننده» الزامی است.", self.actions)


class CdDvdAttributionTest(unittest.TestCase):
    @classmethod
    def setUpClass(cls):
        cls.source = read('cd-dvd.php')

    def test_override_helper_and_root_helper(self):
        self.assertIn('function cd_dvd_section_override_allowed', self.source)
        self.assertIn('function cd_dvd_user_root_id', self.source)
        self.assertIn(OVERRIDE_CODES, self.source)

    def test_shared_department_field_renderer(self):
        self.assertIn('function cd_dvd_department_field_html', self.source)
        self.assertIn('cd_dvd_department_field_html($departments, $form, $user, $editRecord, false)', self.source)
        self.assertIn('cd_dvd_department_field_html($departments, $form, $user, $editRecord, true)', self.source)

    def test_validation_accepts_all_six_roots_not_only_deputy(self):
        block_start = self.source.index('function cd_dvd_validate_payload')
        window = self.source[block_start:]
        self.assertIn('org_root_types', window)
        # قفل قدیمی «فقط deputy» نباید باقی مانده باشد
        self.assertNotIn("!== 'deputy'", window[:window.index('بازده نهایی') if 'بازده نهایی' in window else len(window)])

    def test_edit_by_non_override_keeps_stored_department(self):
        block_start = self.source.index('function cd_dvd_validate_payload')
        window = self.source[block_start:block_start + 6000]
        self.assertIn('keepDepartmentIdParam', window)
        self.assertIn('$keepDepartmentId !== null', window)
        # ویرایش غیرستادی: مقدار ذخیره‌شده بدون هیچ اعتبارسنجی مجددی حفظ می‌شود
        self.assertIn("$finalDepartmentId = $keepDepartmentId > 0 ? $keepDepartmentId : null;", window)

    def test_create_by_non_override_computes_from_chart(self):
        block_start = self.source.index('function cd_dvd_validate_payload')
        window = self.source[block_start:block_start + 6000]
        self.assertIn('cd_dvd_user_root_id($user)', window)

    def test_update_passes_stored_department_for_keep(self):
        self.assertIn("cd_dvd_validate_payload($_POST, $user, $id, (string) ($record['direction'] ?? ''), (int) ($record['department_id'] ?? 0))", self.source)


class GuideMentionsLockTest(unittest.TestCase):
    @classmethod
    def setUpClass(cls):
        cls.guide = read('help-guide.php')

    def test_guide_explains_automatic_section(self):
        self.assertIn('به‌صورت خودکار از جایگاه شما در چارت سازمانی ثبت می‌شود', self.guide)
        self.assertIn('کارشناس، سوپروایزر، مدیر پشتیبانی و ادمین اصلی', self.guide)


if __name__ == '__main__':
    unittest.main()

from pathlib import Path
import unittest

ROOT = Path(__file__).resolve().parents[1]


class TicketLifecycleGuideStaticTests(unittest.TestCase):
    @classmethod
    def setUpClass(cls) -> None:
        cls.guide = (ROOT / "help-guide.php").read_text(encoding="utf-8")

    def test_role_guide_is_linked_from_the_toc(self) -> None:
        self.assertIn('href="#ticket-roles"', self.guide)
        self.assertIn('id="ticket-roles"', self.guide)
        self.assertIn("از ۰ تا ۱۰۰", self.guide)

    def test_lifecycle_explains_roles_and_closure_path(self) -> None:
        for role in (
            "ثبت‌کننده / درخواست‌کننده",
            "manager",
            "support_manager",
            "primary_admin",
            "agent",
            "supervisor",
            "inspector",
        ):
            self.assertIn(role, self.guide)
        for step in ("۰ تا ۱۰٪", "۱۰ تا ۲۰٪", "۲۰ تا ۳۰٪", "۳۰ تا ۸۰٪", "۹۵ تا ۱۰۰٪"):
            self.assertIn(step, self.guide)
        for state in ("در انتظار بررسی مدیر واحد", "در انتظار پاسخ کاربر", "حل‌شده", "بسته‌شده"):
            self.assertIn(state, self.guide)
        self.assertIn("ادمین اصلی و در پشتیبانی مدیر پشتیبانی", self.guide)
        self.assertIn("مهلت تنظیم‌شده امتیاز ندهید", self.guide)

    def test_resolved_ticket_instructions_use_the_actual_reopen_action(self) -> None:
        self.assertIn("پاسخ عادی در این وضعیت تیکت را باز نمی‌کند", self.guide)
        self.assertNotIn("همان درخواست را پاسخ دهید تا دوباره باز شود", self.guide)


if __name__ == "__main__":
    unittest.main()

from pathlib import Path
import unittest

ROOT = Path(__file__).resolve().parents[1]


def read(path: str) -> str:
    return (ROOT / path).read_text(encoding="utf-8")


class CdDvdIncomingAndPermissionTests(unittest.TestCase):
    def test_outgoing_choice_and_default_follow_role_permission(self):
        source = read("cd-dvd.php")
        self.assertIn("$canOut = cd_dvd_can_submit_out($user);", source)
        self.assertIn("($canOut ? 'OUT' : ($canIn ? 'IN' : 'OUT'))", source)
        self.assertIn("if ($canOut): ?><option value=\"OUT\"", source)
        self.assertIn("$registerLink = cd_dvd_can_submit_out($user) || cd_dvd_can_register_in($user)", source)
        self.assertIn("$record['direction'] ?? ''", source)
        self.assertIn("cd_dvd_can_register_in($user)", source)
        self.assertIn("cd_dvd_can_submit_out($user)", source)
        self.assertIn("$direction === 'OUT' && !cd_dvd_can_submit_out($user)", source)

    def test_incoming_form_has_internal_external_selection_and_hides_outgoing_fields(self):
        source = read("cd-dvd.php")
        script = read("assets/cd-dvd.js")
        css = read("assets/style.css")
        self.assertIn('name="brought_by_type" value="internal"', source)
        self.assertIn('name="brought_by_type" value="external"', source)
        self.assertIn("کاربر داخل ساختمان", source)
        self.assertIn("cd_dvd_active_users()", source)
        self.assertIn("u.auth_source <> 'food'", source)
        self.assertIn('class="cd-dvd-outgoing-field"', source)
        self.assertIn("outgoingFields.forEach", script)
        self.assertIn("broughtByExternal.required = isIncoming && externalBroughtBy", script)
        self.assertIn("cd-dvd-outgoing-field[hidden]", css)

    def test_external_person_is_required_and_internal_identity_is_validated_server_side(self):
        source = read("cd-dvd.php")
        self.assertIn("if ($broughtByType === 'external')", source)
        self.assertIn("نام شخص واردکننده از بیرون سازمان را وارد کنید.", source)
        self.assertIn("کاربر واردکننده انتخاب‌شده فعال یا معتبر نیست.", source)


if __name__ == "__main__":
    unittest.main()

from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]


def test_traffic_phone_has_fixed_09_and_requires_exactly_nine_user_entered_digits():
    source = (ROOT / "traffic-control.php").read_text()
    script = (ROOT / "assets/traffic-control.js").read_text()
    assert "traffic_phone_digits" in source
    assert "traffic_phone_normalized" in source
    assert "preg_match('/^09[0-9]{9}$/', $phone)" in source
    assert "شماره تلفن همراه باید دقیقاً ۱۱ رقم باشد" in source
    assert 'class="traffic-phone-prefix" aria-label="پیش‌شمارهٔ ثابت">09</span>' in source
    assert 'data-phone-tail-for="traffic-phone"' in source
    assert 'name="phone" id="traffic-phone"' in source
    assert 'minlength="9" maxlength="9" required' in source
    assert "data-phone-tail-for" in script
    assert "digits.length === 11 && digits.indexOf('09') === 0" in script


def test_inspection_only_reminder_is_scheduled_daily_at_eleven_and_names_current_user():
    source = (ROOT / "index.php").read_text()
    script = (ROOT / "assets/inspection-food-reminder.js").read_text()
    assert "in_array($role, ['inspector', 'guard'], true)" in source
    assert "user_is_key_role($user, 'inspection')" in source
    assert "setTime(11, 0, 0)" in source
    assert "آقای «' . e($name) . '» لطفا استعلام غایبین گروه های غذایی رو انجام بدید برای ثبت در گروه غذایی." in source
    assert "assets/inspection-food-reminder.js?v=1" in source
    assert "localStorage" in script
    assert "schedule(timestamp + 86400)" in script
    assert "playAlarm();" in script


def test_reminder_visual_is_centered_and_alarm_styled():
    style = (ROOT / "assets/style.css").read_text()
    assert ".inspection-food-reminder { position: fixed; inset: 0;" in style
    assert "place-items: center" in style
    assert "inspection-reminder-pulse" in style
    assert ".traffic-phone-prefix" in style


def test_only_primary_admin_can_toggle_reminder_below_auto_close_setting():
    source = (ROOT / "index.php").read_text()
    assert "if (user_is_primary_admin($user))" in source
    assert "settings_section') === 'inspection_alarm'" in source
    assert "require_primary_admin()" in source
    assert "inspection_food_absence_alarm_enabled" in source
    assert "setting('inspection_food_absence_alarm_enabled', '1') !== '0'" in source
    assert "if ($name === '')" in source and "$user['username']" in source
    close_box = source.index('id=\"ticket-auto-close\"')
    alarm_box = source.index('id=\"inspection-alarm-settings\"')
    general_box = source.index('اطلاعات سامانه', alarm_box)
    assert close_box < alarm_box < general_box
    assert "grid-column: 1 / -1" in (ROOT / "assets/style.css").read_text()

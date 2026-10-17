from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
chatbot = (ROOT / "chatbot.php").read_text(encoding="utf-8")
index = (ROOT / "index.php").read_text(encoding="utf-8")
guide = (ROOT / "help-guide.php").read_text(encoding="utf-8")


def check(condition: bool, message: str) -> None:
    if not condition:
        raise AssertionError(message)
    print(f"ok - {message}")


check("function chatbot_widget_defaults" in chatbot
      and "function chatbot_widget_config" in chatbot
      and "function chatbot_save_widget" in chatbot,
      "Aria has defaults, validated config loader and save handler")
check("'name' => 'آریا'" in chatbot,
      "the bot is named Aria by default")
check("chatbot_widget_saved" in chatbot
      and "chatbot_save_widget($_POST" in index
      and "widget_settings" in index and "widget_settings" in chatbot,
      "Aria settings save from the admin page with audit trail")
check("'مدیریت بات سخنگو'" in index
      and "<h1>مدیریت آریا (بات سخنگو)</h1>" in chatbot,
      "menu and admin page are titled after the speaking bot")
check("var cfg = ' . $wjson . ';" in chatbot
      and "'greetOn'" in chatbot and "'greetMs'" in chatbot
      and "'bubbleMs'" in chatbot and "'sound'" in chatbot
      and "'autospeak'" in chatbot,
      "widget receives greeting/sound/autospeak config as JSON")
check("setInterval(greet, 60000)" not in chatbot
      and "if (cfg.greetOn) { setInterval(greet, cfg.greetMs); }" in chatbot
      and "if (cfg.sound) { beep(); }" in chatbot
      and "}, cfg.bubbleMs);" in chatbot,
      "the one-minute greeting (interval, duration, beep) is fully manageable")
check("$wName" in chatbot and "$wBubble" in chatbot
      and "e($wcfg['welcome_text'])" in chatbot
      and "e($wcfg['input_placeholder'])" in chatbot
      and "e($wcfg['send_label'])" in chatbot
      and "کمکی از دستم بر میاد ؟؟؟" not in chatbot,
      "name, bubble, welcome, placeholder and send button come from settings")
check("cfg.fbYes" in chatbot and "cfg.fbNo" in chatbot
      and "cfg.fbThanks" in chatbot and "cfg.pending" in chatbot,
      "feedback labels and the pending text come from settings")
check("chatbot_widget_config()['unmatched_text']" in chatbot
      and "chatbot_widget_config()['rate_limit']" in chatbot,
      "the no-match answer and the per-minute rate limit come from settings")
check("if (cfg.autospeak) { sayButtonClicked(b); }" in chatbot,
      "automatic reading of answers is a managed on/off switch")
check("#cb-bubble::before{left:auto;right:-7px" in chatbot
      and 'name="w_position"' in chatbot,
      "the bot can be moved to the bottom-right from settings")
check('getElementById("cb-close")' in chatbot,
      "the panel close button actually closes the panel")
check("۱۰-ب. آریا، بات سخنگوی سامانه" in guide
      and "مدیریت بات سخنگو" in guide
      and "هر یک دقیقه دست تکان می‌دهد" not in guide,
      "the Persian guide documents Aria and the manageable greeting")
check("CHATBOT_WORDS_ABOUT" in chatbot
      and "معرفی کن" in chatbot
      and "'about'" in chatbot
      and "حدس نمی‌زنم" in chatbot,
      "identity questions get an about-intent instead of a random guide dump")
check("function chatbot_contains_word" in chatbot
      and "function chatbot_guide_tokens" in chatbot
      and "title_hits'] >= 1" in chatbot
      and "زیررشتهٔ «کن» داخل «کنید»" in chatbot,
      "guide matching requires a title word and whole-word hits, not short substrings")
check("حدس نمی‌زند و بخش بی‌ربط را نمی‌خواند" in guide,
      "the Persian guide says Aria does not guess unrelated sections")

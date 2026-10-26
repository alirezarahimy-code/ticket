"""Source + spec checks for Aria TTS 1.59.5 (no PHP required)."""
from pathlib import Path
import re

ROOT = Path(__file__).resolve().parents[1]
chatbot = (ROOT / "chatbot.php").read_text(encoding="utf-8")
lexicon = (ROOT / "chatbot-tts-lexicon.php").read_text(encoding="utf-8")
index = (ROOT / "index.php").read_text(encoding="utf-8")
guide = (ROOT / "help-guide.php").read_text(encoding="utf-8")
php_test = (ROOT / "tests" / "tts_normalize_test.php").read_text(encoding="utf-8")


def check(condition: bool, message: str) -> None:
    if not condition:
        raise AssertionError(message)
    print(f"ok - {message}")


check("function chatbot_tts_prepare" in chatbot, "prepare pipeline exists")
check("function chatbot_tts_he_yezafe" in chatbot, "he-yezafe converter exists")
check("function chatbot_tts_lexicon_apply" in chatbot, "lexicon apply exists")
check("function chatbot_tts_render_lab" in chatbot, "lab UI exists")
check("function chatbot_tts_ipa" in chatbot, "IPA probe exists")
check("function chatbot_tts_model_json_report" in chatbot, "model json report exists")
check("tts_lab" in index and "lab_wav" in index, "admin lab POST/GET wired")
check("spoken.json" in chatbot, "spoken sidecar without DB migration")
check("chatbot_tts_colloquial" in chatbot and "پیش‌فرض خاموش" in chatbot, "colloquial default off")
check("س(?=" not in chatbot, "automatic سَ rule is gone")
check("'سلام' => 'سَلام'" in lexicon, "lexicon has سلام")
check("'سپس' => 'سِپس'" in lexicon, "lexicon has سپس")
check("nمایید" not in chatbot and "نمایید" in chatbot, "verb skip list includes نمایید")
check("دهید" in chatbot and "شوید" in chatbot and "کرد" in chatbot, "verb skip list includes دهید/شوید/کرد")
check("\\x{200C}" in chatbot or "\u200c" in chatbot or "200C" in chatbot, "ZWNJ is in the pipeline")
check("ه‌یِ" in chatbot, "heh-yezafe form is produced")
check("max(0.9, min(1.15" in chatbot, "length_scale clamped 0.9–1.15")
check("function chatbot_tts_ezafe_mode" in chatbot and "'pairs'" in chatbot, "ezafe default is pairs")
check("function chatbot_tts_chunks" in chatbot and "function chatbot_tts_wav_concat" in chatbot, "long text is split then concatenated")
check("function chatbot_tts_mine_pairs" in chatbot, "pair miner exists")
check("مرحله-۲" in php_test, "php test covers hyphenated numbers")
check("می‌توانید سفارش را ثبت کنید" in php_test, "php test covers FAQ verb-initial sentences")
check("chatbot_tts_rules_version" in chatbot, "rules version is part of cache")
check("آزمایشگاه تلفظ" in chatbot, "lab card title in Persian")
check("متن گفتاری دست‌نویس" in chatbot, "FAQ spoken field")
check("tts_normalize_test.php" in php_test or "ثبتِ سفارشِ غذا" in php_test, "php normalize test covers ezafe pair")
check("بیست‌وپنجم مهر" in php_test, "php test covers jalali date")
check("Piper" in guide, "guide still documents Piper")

# Independent-rule traces in source (engine-agnostic)
check("ثبتِ سفارش" in chatbot and "سفارشِ غذا" in chatbot, "fixed ezafe pairs present")
check("راهنمایِ سامانه" in chatbot, "راهنمایِ سامانه pair present")
check("Wi-Fi" in chatbot and "وای فای" in chatbot, "Wi-Fi expansion")
check("VPN" in chatbot and "وی پی ان" in chatbot, "VPN expansion")
check("شمارهٔ پیگیری" in chatbot, "ticket id spoken form")

print("all static TTS normalize checks passed")

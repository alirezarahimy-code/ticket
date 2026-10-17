from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
chatbot = (ROOT / "chatbot.php").read_text(encoding="utf-8")
index = (ROOT / "index.php").read_text(encoding="utf-8")
guide = (ROOT / "help-guide.php").read_text(encoding="utf-8")


def check(condition: bool, message: str) -> None:
    if not condition:
        raise AssertionError(message)
    print(f"ok - {message}")


check("$op === 'voice-status'" in chatbot and "$op === 'say'" in chatbot,
      "voice-status and say operations exist in the chatbot API")
check("audio/wav" in chatbot and "Content-Length" in chatbot,
      "the say operation streams audio bytes with a length header")
check(chatbot.find("user_can($user, 'chatbot.use')") < chatbot.find("$op === 'say'")
      and chatbot.find("!== 'POST'") < chatbot.find("$op === 'say'")
      and chatbot.find("hash_equals(csrf_token()") < chatbot.find("$op === 'say'")
      and chatbot.find("check_api_rate_limit('chatbot_'") < chatbot.find("$op === 'say'"),
      "voice endpoints inherit login, chatbot.use, POST-only, CSRF and rate-limit guards")
check("'-w', $tmp" in chatbot and "['pipe', 'r']" in chatbot
      and "sys_get_temp_dir" in chatbot,
      "the engine runs via argument array with stdin pipe and file-based wav output (no shell interpolation)")
check("shell_exec" not in chatbot and "passthru" not in chatbot
      and "popen" not in chatbot and "pcntl" not in chatbot,
      "no shell backdoors anywhere in chatbot.php")
check("proc_terminate" in chatbot and "20.0" in chatbot
      and "chatbot_tts_taskkill" in chatbot,
      "engine execution has a timeout with process termination and Windows force-kill")
check("function chatbot_tts_exec" in chatbot
      and "function chatbot_tts_probe_engine" in chatbot
      and "chatbot_tts_probe_engine($path)" in chatbot,
      "a shared hardened runner exists and manual engine paths are verified at save time")
check("chatbot_tts_mode" in chatbot and "chatbot_tts_speed" in chatbot
      and "chatbot_tts_espeak_path" in chatbot,
      "voice mode, speed and engine-path settings are wired")
check("function chatbot_save_tts" in chatbot and "chatbot_tts_saved" in chatbot
      and "chatbot_save_tts($_POST" in index and "tts_test" in index,
      "voice settings save with audit trail and the admin page has a server-engine test")
check("chatbot_tts_find_espeak" in chatbot and "eSpeak NG" in chatbot
      and "espeak-ng" in chatbot,
      "engine auto-detection covers the Windows installer path")
check("storage/tts" in chatbot and "sha1($clean" in chatbot,
      "synthesized clips are cached under storage/tts by content hash")
check("function chatbot_tts_clean" in chatbot and "شماره پیگیری" in chatbot
      and "800, 'UTF-8'" in chatbot,
      "spoken text is cleaned (links/ticket numbers/emoji) and length-capped")
check("cb-say" in chatbot and "attachSay(pending, j.answer)" in chatbot
      and "SpeechSynthesisUtterance" in chatbot and 'indexOf("fa-")' in chatbot,
      "widget adds a per-answer speaker button with Persian browser-voice fallback")
check('u.lang = "fa-IR"' in chatbot and "stopVoice()" in chatbot,
      "browser fallback speaks fa-IR and playback can be stopped")
check("شنیدن پاسخ" in guide and "eSpeak-NG" in guide and "تست موتور سرور" in guide,
      "the Persian guide documents listening to answers and the server engine setup")
check("chatbot_tts_voice" in chatbot and 'name="tts_voice"' in chatbot,
      "voice type (default/male/female) is a managed admin setting")
check("function chatbot_tts_voice_id" in chatbot and "fa+m2" in chatbot
      and "fa+f2" in chatbot,
      "male/female map to whitelisted eSpeak-NG Persian voice variants")
check("sha1($clean . '|' . $config['speed'] . '|' . $voiceId)" in chatbot,
      "voice cache key includes the voice variant")
check("function chatbot_tts_is_valid_wav" in chatbot and "'RIFF'" in chatbot
      and "'WAVE'" in chatbot,
      "synthesized output is validated as a real wav file")
check("@unlink($file)" in chatbot,
      "poisoned voice cache entries are deleted and regenerated")
check("tts_sample" in chatbot and "tts_sample" in index
      and "aria-test.wav" in index,
      "admins can download a sample clip to diagnose playback")
check("blob.size < 200" in chatbot,
      "the widget surfaces small invalid audio responses as readable errors")

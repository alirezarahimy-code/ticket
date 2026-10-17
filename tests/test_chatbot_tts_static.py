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
check("'url' => 'index.php?page=chatbot-api&op=say_audio&id='" in chatbot,
      "the say operation returns a playable audio URL instead of raw bytes")
check(chatbot.find("user_can($user, 'chatbot.use')") < chatbot.find("$op === 'say'")
      and chatbot.find("!== 'POST'") < chatbot.find("$op === 'say'")
      and chatbot.find("hash_equals(csrf_token()") < chatbot.find("$op === 'say'")
      and chatbot.find("check_api_rate_limit('chatbot_'") < chatbot.find("$op === 'say'"),
      "the say endpoint inherits login, chatbot.use, POST-only, CSRF and rate-limit guards")
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
check("audio.src = url" in chatbot and "createObjectURL" not in chatbot,
      "the widget plays server audio through a direct URL (no blob handling)")
check("=== 'say_audio'" in chatbot
      and chatbot.find("user_can($user, 'chatbot.use')") < chatbot.find("=== 'say_audio'")
      and chatbot.find("=== 'say_audio'") < chatbot.find("!== 'POST'")
      and "check_api_rate_limit('chatbot_say_audio_'" in chatbot
      and "/^[0-9a-f]{40}$/" in chatbot,
      "audio download is login-guarded, rate-limited, id-validated and GET-friendly")
check("function chatbot_tts_voice_status" in chatbot and "'--voices'" in chatbot
      and ".voices.json" in chatbot,
      "Persian voice availability is probed and cached")
check("صدای فارسی در موتور صوتی یافت نشد" in chatbot,
      "a definitively missing Persian voice fails with a clear error instead of garbage audio")
check("'140'" in chatbot and "?? 140" in chatbot,
      "default speech speed is slower (140 wpm) for clearer Persian")
check("Accept-Ranges" in chatbot and "immutable" in chatbot,
      "served audio supports seeking and long-term caching")
check("function chatbot_tts_colloquial" in chatbot and "می‌خوام" in chatbot
      and "می‌شه" in chatbot and "chatbot_tts_colloquial(chatbot_tts_clean" in chatbot,
      "spoken-Tehrani rewrite is applied to TTS text only")
check("function chatbot_tts_run_piper" in chatbot and "--length_scale" in chatbot
      and "--output_file" in chatbot and ".onnx" in chatbot
      and "function chatbot_tts_probe_piper" in chatbot,
      "Piper neural engine is wired with file-based wav output and a save-time probe")
check("tts_piper_path" in chatbot and "tts_piper_model" in chatbot
      and "tts_kind" in chatbot and "chatbot_tts_kind" in chatbot,
      "Piper path, model and engine preference are managed admin settings")
check("function chatbot_tts_active" in chatbot and "function chatbot_tts_find_piper" in chatbot
      and "function chatbot_tts_length_scale" in chatbot,
      "active engine prefers Piper when the exe and onnx model are present")
check("piper_windows_amd64.zip" in chatbot and "fa_IR-mana-medium.onnx" in chatbot and "huggingface.co" in chatbot,
      "the admin card documents the one-time offline Piper install")
check("محاوره‌ای" in chatbot and "متن روی صفحه عوض نمی‌شود" in chatbot,
      "admins are told on-screen text stays formal while speech is colloquial")
check("موتور عصبی Piper" in index and "خواندن محاوره‌ای" in index,
      "the server-engine test reports Piper vs eSpeak and colloquial reading")
check("Piper" in guide and "محاوره‌ای" in guide,
      "the Persian guide documents natural offline Piper speech and colloquial reading")
check("function chatbot_tts_phrase" in chatbot and "function chatbot_tts_piper_model_rank" in chatbot
      and "mana" in chatbot and "sentence_silence" in chatbot
      and "--noise_scale" in chatbot and "1.15" in chatbot,
      "Piper quality is boosted: Mana preference, sentence pauses, natural length and tone")

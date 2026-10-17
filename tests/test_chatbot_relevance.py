"""Relevance of Aria answers: identity must not dump a ticket-guide section."""
from pathlib import Path
import re
import sys

ROOT = Path(__file__).resolve().parents[1]
chatbot = (ROOT / "chatbot.php").read_text(encoding="utf-8")
guide = (ROOT / "help-guide.php").read_text(encoding="utf-8")

STOP = {
    "من", "را", "به", "از", "در", "که", "و", "یک", "یه", "این", "آن", "چطور", "چگونه",
    "چه", "چی", "است", "هست", "می", "توانم", "بتوانم", "باید", "برای", "با", "شما",
    "کنم", "کنیم", "نمی", "ام", "هم", "تا", "رو", "های", "میشه", "میشود",
}
GUIDE_STOP = {
    "کجا", "کی", "کدوم", "کدام", "چی", "چیه", "چیست", "چند", "چرا", "آیا", "چطوره",
    "چطورن", "چطوریه", "امروز", "فردا", "دیروز", "الان", "هوا", "ساعت", "لطفا",
    "لطفاً", "کمک", "بگو", "بگید", "بگین", "میشه", "بشه", "میخوام", "می‌خوام",
    "خوبه", "چنده", "کجاست", "خودت", "خودم", "شما", "کامل", "بکن", "کنم", "کنید",
    "میکنم", "هستی", "هستم", "باشه", "میخواد",
}


def normalize(text: str) -> str:
    for a, b in (("ي", "ی"), ("ى", "ی"), ("ك", "ک"), ("ة", "ه"), ("ۀ", "ه")):
        text = text.replace(a, b)
    text = re.sub(r"[^\w\s]", " ", text, flags=re.UNICODE)
    return re.sub(r"\s+", " ", text).strip()


def tokens(question: str) -> list[str]:
    out = []
    for w in normalize(question).split():
        if len(w) < 3 or w in STOP or w in GUIDE_STOP:
            continue
        out.append(w)
    return out


def contains_word(hay: str, needle: str) -> bool:
    return bool(re.search(rf"(?:^| ){re.escape(needle)}(?: |$)", hay))


def guide_index() -> list[dict]:
    html = re.sub(r"<style.*?</style>", "", guide, flags=re.S | re.I)
    parts = re.split(r"(<h[234][^>]*>.*?</h[234]>)", html, flags=re.S | re.I)
    sections = []
    h2 = ""
    cur = None
    for part in parts:
        m = re.match(r"^<h([234])([^>]*)>(.*?)</h[234]>$", part, flags=re.S | re.I)
        if m:
            if cur:
                sections.append(cur)
            title = re.sub(r"<[^>]+>", "", m.group(3))
            title = re.sub(r"\s+", " ", title).strip()
            if m.group(1) == "2":
                h2 = title
            kw = ""
            km = re.search(r'data-kw="([^"]*)"', m.group(2))
            if km:
                kw = km.group(1)
            cur = {"title": title, "h2": h2, "kw": kw, "html": ""}
        elif cur is not None:
            cur["html"] += part
    if cur:
        sections.append(cur)
    out = []
    for sec in sections:
        if sec["title"] in ("", "فهرست مطالب"):
            continue
        text = re.sub(r"<[^>]+>", " ", sec["html"])
        text = re.sub(r"\s+", " ", text).strip()
        sec["title_norm"] = normalize(sec["title"] + " " + sec["kw"])
        sec["body_norm"] = normalize(sec["h2"] + " " + text)
        out.append(sec)
    return out


def search(question: str):
    words = tokens(question)
    if not words:
        return None
    best = None
    best_score = 0
    for sec in guide_index():
        title_hits = sum(1 for w in words if contains_word(sec["title_norm"], w))
        if title_hits < 1:
            continue
        body_hits = sum(
            1 for w in words
            if not contains_word(sec["title_norm"], w) and contains_word(sec["body_norm"], w)
        )
        score = title_hits * 3 + body_hits
        if score > best_score:
            best_score = score
            best = sec["title"]
    return best


def check(cond: bool, msg: str) -> None:
    if not cond:
        raise AssertionError(msg)
    print(f"ok - {msg}")


ABOUT = [
    "خودت را معرفی", "خودت رو معرفی", "معرفی کن", "تو کی هستی", "آریا کی",
]
check(all(w in chatbot for w in ABOUT), "identity phrases exist in chatbot.php")
check("CHATBOT_WORDS_ABOUT" in chatbot, "about intent constant exists")

about_q = "خودت رو کامل معرفی کن"
check(search(about_q) is None, "introduce-yourself does not match any guide title")
check("ثبت درخواست" not in (search(about_q) or ""), "introduce-yourself does not dump ticket steps")

how_q = "چطور فیش غذا را چاپ کنم"
hit = search(how_q)
check(hit is not None, "how-to print food ticket still finds a guide section")
check("فیش" in (hit or "") or "چاپ" in (hit or "") or "غذا" in (hit or ""),
      f"food/print how-to hits a related title, got {hit!r}")

print("all relevance checks passed")

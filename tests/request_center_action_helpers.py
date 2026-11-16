"""Resolve legacy action-file shims to their current Request Center owners."""
from __future__ import annotations

import re
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
_BRIDGE = re.compile(
    r"require\s+dirname\(__DIR__\)\s*\.\s*['\"]"
    r"/modules/request-center/Actions/([^'\"]+)['\"]\s*;"
)


def action_source(relative: str) -> tuple[str, Path]:
    """Return implementation text/path, following a one-hop legacy bridge."""
    path = ROOT / relative
    source = path.read_text(encoding="utf-8")
    match = _BRIDGE.search(source)
    if match is None:
        return source, path
    target = ROOT / "modules/request-center/Actions" / match.group(1)
    return target.read_text(encoding="utf-8"), target

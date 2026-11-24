#!/usr/bin/env python3
"""Build the local Persian place/road search index from an OSM PBF extract.

Requires: Python 3.10+ and pyosmium (pip install osmium).
The script is an offline preparation tool; it never contacts an external service.
"""
from __future__ import annotations
import argparse
import hashlib
import json
import os
import re
import sqlite3
import sys
import time
import unicodedata
from pathlib import Path

try:
    import osmium
except ImportError:
    print("Missing dependency: install pyosmium with: python -m pip install osmium", file=sys.stderr)
    raise SystemExit(2)

IRAN_BOUNDS = (44.0, 24.0, 64.0, 40.0)
DIGITS = str.maketrans("۰۱۲۳۴۵۶۷۸۹٠١٢٣٤٥٦٧٨٩", "01234567890123456789")
CHAR_MAP = str.maketrans({"ي": "ی", "ى": "ی", "ك": "ک", "ۀ": "ه", "ة": "ه", "ؤ": "و", "إ": "ا", "أ": "ا", "ٱ": "ا"})

def normalize(value: str) -> str:
    value = value.translate(CHAR_MAP).translate(DIGITS)
    value = unicodedata.normalize("NFKC", value)
    value = "".join(ch for ch in value if unicodedata.category(ch) not in ("Mn", "Me") and ch != "\u0640")
    value = re.sub(r"[\u200c\u200d\s]+", " ", value).strip().lower()
    return value

def tags_dict(tags) -> dict[str, str]:
    return {tag.k: tag.v for tag in tags}

def classify(tags: dict[str, str]) -> tuple[str, str, int] | None:
    if tags.get("highway"):
        highway = tags["highway"]
        if highway in {"motorway", "trunk", "primary", "secondary", "tertiary", "unclassified", "residential", "living_street", "service", "pedestrian", "footway", "path", "track", "road"}:
            return ("road", highway, 90 if highway in {"motorway", "trunk", "primary"} else 65)
    for key, kind in (("place", "place"), ("amenity", "amenity"), ("shop", "shop"), ("office", "office"),
                      ("healthcare", "healthcare"), ("tourism", "tourism"), ("leisure", "leisure"),
                      ("public_transport", "public_transport"), ("railway", "railway"), ("natural", "natural"),
                      ("historic", "historic"), ("landuse", "landuse"), ("building", "building")):
        if tags.get(key):
            value = tags[key]
            rank = 95 if key == "place" else 75
            return (kind, value, rank)
    if tags.get("railway") in {"station", "halt", "tram_stop", "subway_entrance"}:
        return ("railway", tags["railway"], 85)
    if tags.get("waterway"):
        return ("waterway", tags["waterway"], 55)
    return None

def in_iran(lon: float, lat: float) -> bool:
    west, south, east, north = IRAN_BOUNDS
    return west <= lon <= east and south <= lat <= north

class SearchIndexer(osmium.SimpleHandler):
    def __init__(self, connection: sqlite3.Connection):
        super().__init__()
        self.db = connection
        self.inserted = 0
        self.skipped = 0
        self.insert = connection.cursor()

    def save(self, osm_id: int, tags: dict[str, str], lon: float, lat: float, source: str) -> None:
        name = tags.get("name") or tags.get("name:fa") or tags.get("official_name") or tags.get("alt_name")
        name_fa = tags.get("name:fa") or tags.get("name:per") or (name if any("\u0600" <= c <= "\u06ff" for c in (name or "")) else "")
        if not name or len(name.strip()) < 2 or not in_iran(lon, lat):
            self.skipped += 1
            return
        category = classify(tags)
        if not category:
            self.skipped += 1
            return
        kind, value, rank = category
        address_parts = [tags.get(k, "") for k in ("addr:street", "addr:suburb", "addr:district", "addr:city")]
        search_name = " ".join(x for x in [name, name_fa, *address_parts] if x)
        self.insert.execute(
            "INSERT OR REPLACE INTO places(id,name,name_fa,normalized,kind,category,lat,lon,rank,source) VALUES(?,?,?,?,?,?,?,?,?,?)",
            (f"{source}/{osm_id}", name[:500], name_fa[:500], normalize(search_name), kind, value, lat, lon, rank, source)
        )
        self.inserted += 1

    def node(self, node) -> None:
        if not node.location.valid():
            return
        tags = tags_dict(node.tags)
        if not tags:
            return
        self.save(node.id, tags, node.location.lon, node.location.lat, "node")

    def way(self, way) -> None:
        tags = tags_dict(way.tags)
        if not tags or not (tags.get("name") or tags.get("name:fa") or tags.get("official_name")):
            return
        coords = []
        try:
            for node in way.nodes:
                if node.location.valid():
                    coords.append((node.lon, node.lat))
        except Exception:
            return
        if not coords:
            return
        # A representative point is used for search navigation, not as a replacement geometry.
        lon = sum(p[0] for p in coords) / len(coords)
        lat = sum(p[1] for p in coords) / len(coords)
        self.save(way.id, tags, lon, lat, "way")

def sha256(path: Path) -> str:
    h = hashlib.sha256()
    with path.open("rb") as stream:
        for chunk in iter(lambda: stream.read(1024 * 1024), b""):
            h.update(chunk)
    return h.hexdigest()

def main() -> int:
    parser = argparse.ArgumentParser(description="Create offline Iran OSM search.sqlite")
    parser.add_argument("--pbf", required=True, help="Path to iran-latest.osm.pbf")
    parser.add_argument("--output", default=str(Path(__file__).resolve().parents[1] / "data" / "search.sqlite"))
    parser.add_argument("--manifest", default=str(Path(__file__).resolve().parents[1] / "data" / "manifest.json"))
    args = parser.parse_args()
    pbf = Path(args.pbf).resolve()
    output = Path(args.output).resolve()
    manifest_path = Path(args.manifest).resolve()
    if not pbf.is_file() or pbf.stat().st_size < 1024:
        print(f"Invalid or missing PBF: {pbf}", file=sys.stderr)
        return 2
    output.parent.mkdir(parents=True, exist_ok=True)
    temp = output.with_suffix(output.suffix + ".tmp")
    if temp.exists():
        temp.unlink()
    db = sqlite3.connect(temp)
    try:
        db.execute("PRAGMA journal_mode=OFF")
        db.execute("PRAGMA synchronous=OFF")
        db.execute("PRAGMA temp_store=MEMORY")
        db.execute("""CREATE TABLE places (
            id TEXT PRIMARY KEY, name TEXT NOT NULL, name_fa TEXT NOT NULL DEFAULT '',
            normalized TEXT NOT NULL, kind TEXT NOT NULL, category TEXT NOT NULL,
            lat REAL NOT NULL, lon REAL NOT NULL, rank INTEGER NOT NULL, source TEXT NOT NULL
        )""")
        indexer = SearchIndexer(db)
        started = time.time()
        indexer.apply_file(str(pbf), locations=True, idx="flex_mem")
        db.commit()
        db.execute("CREATE INDEX idx_places_normalized ON places(normalized)")
        db.execute("CREATE INDEX idx_places_rank ON places(rank DESC)")
        db.execute("CREATE INDEX idx_places_category ON places(category)")
        try:
            db.execute("CREATE VIRTUAL TABLE places_fts USING fts5(name, name_fa, normalized, content='places', content_rowid='rowid', tokenize='unicode61 remove_diacritics 2')")
            db.execute("INSERT INTO places_fts(rowid,name,name_fa,normalized) SELECT rowid,name,name_fa,normalized FROM places")
        except sqlite3.OperationalError:
            pass
        db.commit()
        count = db.execute("SELECT COUNT(*) FROM places").fetchone()[0]
        db.execute("PRAGMA optimize")
        db.close()
        os.replace(temp, output)
        result = {
            "file": output.name, "bytes": output.stat().st_size, "sha256": sha256(output),
            "records": count, "created_at_utc": time.strftime("%Y-%m-%dT%H:%M:%SZ", time.gmtime()),
            "builder": "build-search-index.py", "source_pbf": pbf.name, "source_sha256": sha256(pbf),
            "elapsed_seconds": round(time.time() - started, 2),
            "note": "Road and POI search centroids are navigation anchors; they are not address interpolation or official postal addresses."
        }
        manifest = {}
        if manifest_path.is_file():
            try: manifest = json.loads(manifest_path.read_text(encoding="utf-8-sig"))
            except Exception: manifest = {}
        manifest.setdefault("schema_version", 1)
        manifest["search_index"] = result
        manifest.setdefault("not_included_or_not_verified", [])
        for note in ("official postal addresses and house numbers", "official municipal district boundaries", "completeness of all named roads and POIs"):
            if note not in manifest["not_included_or_not_verified"]:
                manifest["not_included_or_not_verified"].append(note)
        manifest_path.parent.mkdir(parents=True, exist_ok=True)
        manifest_path.write_text(json.dumps(manifest, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")
        print(json.dumps({"status": "ok", **result}, ensure_ascii=False, indent=2))
    except Exception:
        db.close()
        if temp.exists(): temp.unlink()
        raise
    return 0

if __name__ == "__main__":
    raise SystemExit(main())

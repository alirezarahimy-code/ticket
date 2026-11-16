from __future__ import annotations

import hashlib
import re
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
VERSION = "1.63.1"
MANIFEST = ROOT / "downloads" / f"RELEASE-ASSET-MANIFEST-{VERSION}.txt"
EXPECTED = {
    "ticket-update-1.63-to-1.63.1-r14-org-profile-20261115.zip",
    "ticket-full-1.63.1-r14-20261115.zip",
}


def test_release_asset_manifest_is_safe_complete_and_hash_verified() -> None:
    entries = [
        line.strip()
        for line in MANIFEST.read_text(encoding="utf-8").splitlines()
        if line.strip() and not line.lstrip().startswith("#")
    ]
    assert set(entries) == EXPECTED
    assert len(entries) == len(EXPECTED)

    for name in entries:
        assert re.fullmatch(r"[A-Za-z0-9._-]+\.zip", name)
        assert ".." not in name
        archive = ROOT / "downloads" / name
        sidecar = ROOT / "downloads" / f"{name}.sha256"
        assert archive.is_file(), f"missing release asset: {name}"
        assert sidecar.is_file(), f"missing SHA-256 sidecar: {sidecar.name}"
        expected_hash = sidecar.read_text(encoding="utf-8").split()[0].lower()
        actual_hash = hashlib.sha256(archive.read_bytes()).hexdigest()
        assert re.fullmatch(r"[0-9a-f]{64}", expected_hash)
        assert actual_hash == expected_hash, f"SHA-256 mismatch: {name}"

    workflow = (ROOT / ".github" / "workflows" / "publish-release.yml").read_text(encoding="utf-8")
    assert workflow.count("RELEASE-ASSET-MANIFEST-$RELEASE_VERSION.txt") == 3
    assert 'git rev-parse "$RELEASE_TAG^{commit}"' in workflow
    assert 'gh release edit "$RELEASE_TAG" --title "$TITLE" --notes-file "$NOTES"' in workflow

    # Manual dispatch must not bypass release/source integrity checks.
    assert 'INPUT_RELEASE_TAG: ${{ inputs.release_tag }}' in workflow
    assert 'RELEASE_TAG="${INPUT_RELEASE_TAG:?release_tag input is required}"' in workflow
    assert 'RELEASE_VERSION="${RELEASE_TAG#v}"' in workflow
    assert 'echo "version=$RELEASE_VERSION" >> "$GITHUB_OUTPUT"' in workflow
    assert 'if git ls-remote --exit-code --tags origin "refs/tags/$RELEASE_TAG" >/dev/null 2>&1; then' in workflow
    assert 'if [ "${{ github.event_name }}" = "push" ] && git ls-remote' not in workflow
    assert 'if [ "$release_commit" != "$GITHUB_SHA" ]; then' in workflow
    assert 'TITLE="نسخهٔ ${RELEASE_VERSION}"' in workflow

    # The release must publish and report only manifest-selected ZIPs and their checksums.
    assert 'ATTACH+=("downloads/$line" "downloads/$line.sha256")' in workflow
    assert 'ATTACH+=("$f" "$f.sha256")' in workflow
    assert 'RELEASE_ASSETS+=("$line" "$line.sha256")' in workflow
    assert 'for name in "${RELEASE_ASSETS[@]}"; do' in workflow

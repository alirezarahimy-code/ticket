from pathlib import Path
import os
import subprocess

ROOT = Path(__file__).resolve().parents[1]
WORKFLOW = ROOT / ".github" / "workflows" / "publish-release.yml"


def test_release_dispatch_input_is_not_interpolated_into_shell() -> None:
    workflow = WORKFLOW.read_text(encoding="utf-8")
    assert "INPUT_RELEASE_TAG: ${{ inputs.release_tag }}" in workflow
    assert 'RELEASE_TAG="${INPUT_RELEASE_TAG:?release_tag input is required}"' in workflow
    assert 'RELEASE_TAG="${{ inputs.release_tag }}"' not in workflow
    assert 'if [ "$GITHUB_EVENT_NAME" = "workflow_dispatch" ]; then' in workflow
    assert '[[ ! "$RELEASE_TAG" =~ ^v[0-9]+(\\.[0-9]+){1,2}$ ]]' in workflow


def test_existing_release_tag_must_match_the_publish_source_commit() -> None:
    workflow = WORKFLOW.read_text(encoding="utf-8")
    assert 'git ls-remote --exit-code --tags origin "refs/tags/$RELEASE_TAG"' in workflow
    assert 'git fetch --no-tags origin "refs/tags/$RELEASE_TAG:refs/tags/$RELEASE_TAG"' in workflow
    assert 'release_commit=$(git rev-parse "$RELEASE_TAG^{commit}")' in workflow
    assert 'if [ "$release_commit" != "$GITHUB_SHA" ]; then' in workflow
    assert 'echo "version=$RELEASE_VERSION" >> "$GITHUB_OUTPUT"' in workflow



def _run_tag_probe(tmp_path: Path, mode: str, tag_commit: str = "abc123") -> subprocess.CompletedProcess[str]:
    workflow = WORKFLOW.read_text(encoding="utf-8")
    start = workflow.index("# Without --exit-code, a missing tag is a successful empty result.")
    end = workflow.index('echo "برچسب Release:', start)
    probe = workflow[start:end]

    fake_bin = tmp_path / "bin"
    fake_bin.mkdir()
    fake_git = fake_bin / "git"
    fake_git.write_text(
        """#!/usr/bin/env python3
import os
import sys

command = sys.argv[1] if len(sys.argv) > 1 else ""
mode = os.environ.get("FAKE_GIT_MODE", "absent")
if command == "ls-remote":
    if mode == "error":
        print("simulated remote transport failure", file=sys.stderr)
        raise SystemExit(128)
    if mode == "present":
        print("0123456789abcdef refs/tags/v1.2.3")
    raise SystemExit(0)
if command == "fetch":
    raise SystemExit(0)
if command == "rev-parse":
    print(os.environ.get("FAKE_TAG_COMMIT", "abc123"))
    raise SystemExit(0)
raise SystemExit(99)
""",
        encoding="utf-8",
    )
    fake_git.chmod(0o755)

    env = os.environ.copy()
    env.update(
        {
            "PATH": str(fake_bin) + os.pathsep + env.get("PATH", ""),
            "FAKE_GIT_MODE": mode,
            "FAKE_TAG_COMMIT": tag_commit,
        }
    )
    script = "set -euo pipefail\\nRELEASE_TAG=v1.2.3\\nGITHUB_SHA=abc123\\n" + probe
    return subprocess.run(
        ["bash", "-c", script],
        check=False,
        capture_output=True,
        text=True,
        env=env,
    )


def test_release_tag_probe_fails_closed_on_remote_errors(tmp_path: Path) -> None:
    result = _run_tag_probe(tmp_path, "error")
    assert result.returncode != 0
    assert "simulated remote transport failure" in result.stderr


def test_release_tag_probe_accepts_absent_or_matching_tag_and_rejects_mismatch(tmp_path: Path) -> None:
    assert _run_tag_probe(tmp_path / "absent", "absent").returncode == 0
    assert _run_tag_probe(tmp_path / "matching", "present", "abc123").returncode == 0
    assert _run_tag_probe(tmp_path / "mismatch", "present", "different").returncode != 0


def test_release_upload_and_summary_follow_manifest_and_include_checksums() -> None:
    workflow = WORKFLOW.read_text(encoding="utf-8")
    upload = workflow.split("      - name: ساخت Release و پیوست فایل‌ها", 1)[1].split(
        "      - name: گزارش لینک‌های مستقیم", 1
    )[0]
    summary = workflow.split("      - name: گزارش لینک‌های مستقیم", 1)[1]

    assert 'ATTACH+=("$sidecar")' in upload
    assert 'ASSETS+=("$line" "$line.sha256")' in summary
    assert 'for name in "${ASSETS[@]}"' in summary
    assert 'for f in downloads/*.zip' not in summary
    assert 'if [ ! -f "$path" ]; then' in summary

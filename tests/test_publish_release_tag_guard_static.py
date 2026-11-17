from pathlib import Path

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

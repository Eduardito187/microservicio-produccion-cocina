#!/usr/bin/env bash
set -euo pipefail

if ! command -v gh >/dev/null 2>&1; then
  echo "[error] GitHub CLI (gh) is required."
  exit 1
fi

if ! gh auth status >/dev/null 2>&1; then
  echo "[error] gh is not authenticated. Run: gh auth login"
  exit 1
fi

REPO="${1:-}"
if [[ -z "$REPO" ]]; then
  REPO="$(gh repo view --json nameWithOwner -q .nameWithOwner)"
fi

protect_branch() {
  local branch="$1"

  echo "Applying protection to $REPO:$branch"

  gh api \
    --method PUT \
    -H "Accept: application/vnd.github+json" \
    "repos/$REPO/branches/$branch/protection" \
    -f required_linear_history=true \
    -f allow_force_pushes=false \
    -f allow_deletions=false \
    -f block_creations=false \
    -f required_conversation_resolution=true \
    -f enforce_admins=true \
    -F required_status_checks.strict=true \
    -F required_status_checks.contexts[]='Laravel CI / build' \
    -F required_pull_request_reviews.dismiss_stale_reviews=true \
    -F required_pull_request_reviews.require_code_owner_reviews=false \
    -F required_pull_request_reviews.required_approving_review_count=1 \
    -F restrictions=
}

protect_branch "main"
protect_branch "develop"

echo "Branch protection applied successfully to main and develop."

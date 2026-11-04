#!/usr/bin/env bash
set -Eeuo pipefail
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
COMPOSE_FILE="$ROOT/tests/logging-acceptance/compose.yaml"
command -v docker >/dev/null || { echo 'BLOCKED: Docker CLI is required.' >&2; exit 20; }
php -r 'exit(extension_loaded("pdo_mysql") ? 0 : 1);' || { echo 'BLOCKED: PHP pdo_mysql extension is required.' >&2; exit 21; }
cleanup() { docker compose -f "$COMPOSE_FILE" down --volumes --remove-orphans >/dev/null 2>&1 || true; }
trap cleanup EXIT INT TERM
docker compose -f "$COMPOSE_FILE" up -d --wait
export ITSM_TEST_ALLOW_DESTRUCTIVE=YES
export ITSM_TEST_DSN='mysql:host=127.0.0.1;port=33306;dbname=itsm_logging_test;charset=utf8mb4'
export ITSM_TEST_DB_USER=root
export ITSM_TEST_DB_PASS=test-root-password
php "$ROOT/tests/test_logging_mysql_integration.php"

#!/usr/bin/env bash
# NOTE: This script only toggles plugin state for Gutenberg E2E variants.
# It does not run tests. Use bin/run-e2e-tests-default.sh for a full run.
# Requires the environment to be up.
set -euo pipefail

export DOCKER_FILE=${DOCKER_FILE:-docker-compose.ci.yml}
docker compose -f $DOCKER_FILE run --rm -u root cli bash -c "wp --allow-root plugin deactivate classic-editor"

#!/usr/bin/env bash
# NOTE: This script only toggles plugin state for Gutenberg E2E variants.
# It does not run tests. Use bin/run-e2e-tests-default.sh for a full run.
# Requires the environment to be up.
set -euo pipefail

npx wp-env run tests-cli wp plugin deactivate classic-editor.latest-stable

#!/usr/bin/env bash
set -euo pipefail

export DOCKER_FILE=${DOCKER_FILE:-docker-compose.ci.yml}

npm run env:up
npm run test:e2e:playwright
npm run env:down

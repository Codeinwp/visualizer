#!/usr/bin/env bash
set -euo pipefail

: "${DOCKER_FILE:=docker-compose.ci.yml}"

docker compose -f "$DOCKER_FILE" down
docker volume prune --force

#!/usr/bin/env bash
set -euo pipefail

npm run env:up
npm run test:e2e:playwright
npm run env:down

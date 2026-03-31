# Run E2E Tests (Visualizer Free)

Run the Playwright end-to-end test suite for the Visualizer free plugin. The environment uses Docker (MariaDB + WordPress on port 8889).

## Pre-flight checks

1. Make sure Docker is running: `docker info`
2. Check for port conflicts on 8889 and 3306 — if anything is using them, stop those services first (e.g. `brew services stop mariadb`).

## Commands

```bash
# 1. Install dependencies (skip if already done)
npm ci
npx playwright install --with-deps chromium
composer install --no-dev

# 2. Boot WordPress environment (Docker + WP install + plugin activation)
DOCKER_FILE=docker-compose.ci.yml bash bin/wp-init.sh

# 3a. Run the full Playwright suite
npm run test:e2e:playwright

# 3b. OR run a single spec file (replace the path as needed)
# npx wp-scripts test-playwright --config tests/e2e/playwright.config.js tests/e2e/specs/gutenberg-editor.spec.js

# 4. Tear down when done
DOCKER_FILE=docker-compose.ci.yml bash bin/wp-down.sh
```

## Environment

- WordPress: http://localhost:8889
- Credentials: `admin` / `password`
- `TI_E2E_TESTING=true` is set in `wp-config.php` by the setup script

## Instructions

1. Run the pre-flight checks.
2. If `wp-init.sh` fails due to a port conflict, identify and stop the conflicting service, then retry.
3. Run the tests. Show output as it streams.
4. After tests complete (pass or fail), always run the tear-down command.
5. Report a summary: how many tests passed, failed, and any error messages from failures.

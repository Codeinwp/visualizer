# Run E2E Tests (Visualizer Free)

Run the Playwright end-to-end test suite for the Visualizer free plugin. The environment is managed by [`@wordpress/env`](https://www.npmjs.com/package/@wordpress/env) (Docker under the hood) and serves the test instance on port 8889.

## Pre-flight checks

1. Make sure Docker is running: `docker info`
2. Check for port conflicts on 8888 and 8889 — if anything is using them, stop those services first.

## Commands

```bash
# 1. Install dependencies (skip if already done)
npm ci
npx playwright install --with-deps chromium
composer install --no-dev

# 2. Boot WordPress environment (wp-env: WP install, plugin/theme activation, constants)
npm run env:up

# 3a. Run the full Playwright suite
npm run test:e2e:playwright

# 3b. OR run a single spec file (replace the path as needed)
# npx wp-scripts test-playwright --config tests/e2e/playwright.config.js tests/e2e/specs/gutenberg-editor.spec.js

# 4. Tear down when done
npm run env:down
```

## Environment

- WordPress (tests instance): http://localhost:8889
- Credentials: `admin` / `password`
- `TI_E2E_TESTING=true` is set via `.wp-env.json` so the plugin loads E2E-specific hooks
- Run wp-cli against the test instance with: `npx wp-env run tests-cli wp <command>`

## Instructions

1. Run the pre-flight checks.
2. If `npm run env:up` fails due to a port conflict, identify and stop the conflicting service, then retry.
3. Run the tests. Show output as it streams.
4. After tests complete (pass or fail), always run the tear-down command.
5. Report a summary: how many tests passed, failed, and any error messages from failures.

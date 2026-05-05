## E2E Testing using Playwright

### How to run the E2E tests

To run the E2E tests you will to install playwright using the following command:

```bash
npx playwright install
```

> [!NOTE]
> You also need to use Node.js 18 or later, along with Docker (used by `wp-env` under the hood).

Create the testing instance using the following command:

```bash
npm run env:up
```

This boots a WordPress instance via [`@wordpress/env`](https://www.npmjs.com/package/@wordpress/env). The tests instance runs on port `8889` and uses `admin` / `password` credentials. The development instance runs on `8888`; only `8889` is used by the E2E suite.

For the _headless_ mode, use the following command:

```bash
npm run test:e2e:playwright
```

Tear down the environment when finished:

```bash
npm run env:down
```

For _debug_ mode (which will open the browser along with Playwright Editor), use the following command:

```bash
npm run test:e2e:playwright:debug
```

> [!NOTE]
> When writing a test, using the debug mode is recommended since it will allow you to see the browser and the test in action.

### Running wp-cli against the test instance

Use the `tests-cli` service to run commands against the WordPress instance the tests use:

```bash
npx wp-env run tests-cli wp plugin list
```

### Build requirements

If you change any sources under `classes/Visualizer/**/src`, run the matching build before E2E:

- `npm run gutenberg:build`
- `npm run chartbuilder:build`
- `npm run d3renderer:build`

### Environment configuration

The E2E environment is defined in `.wp-env.json` at the repository root. It installs `classic-editor` and `elementor`, activates `twentytwentyone`, and sets `TI_E2E_TESTING=true` so the plugin loads its E2E-specific hooks.

### Learn more about E2E testing

Resources:

- [Playwright Documentation](https://playwright.dev/docs/intro)
- https://github.com/WordPress/gutenberg/blob/trunk/packages/e2e-test-utils-playwright/README.md
- https://developer.wordpress.org/block-editor/contributors/code/testing-overview/

To see more examples of E2E tests, check the Gutenberg repository: https://github.com/WordPress/gutenberg/tree/trunk/test/e2e

> [!NOTE]
> If you are out of ideas on who to test, check the Gutenberg repository. It has a lot of examples of E2E tests that you can use as a reference.

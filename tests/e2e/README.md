## E2E Testing using Playwright

### How to run the E2E tests

To run the E2E tests you will to install playwright using the following command:

```bash
npx playwright install
```

> [!NOTE]
> You also need to use Node.js 18 or later, along with Docker and Docker Compose.

Create the testing instance using the following command:

```bash
npm run env:up
```

This will create a WordPress instance. The port is `8889` and the user is `admin` and the password is `password` (the same values used by `wp-env` testing instance).
If you see port `8080`, ignore it for E2E; the test environment is expected at `http://localhost:8889`.

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

For database connection, the credentials are:

- Host: `localhost` or `127.0.0.1`
- Database: `wordpress`
- User: `admin`
- Password: `password`
- Port: `3306`

### Build requirements

If you change any sources under `classes/Visualizer/**/src`, run the matching build before E2E:

- `npm run gutenberg:build`
- `npm run chartbuilder:build`
- `npm run d3renderer:build`

### Docker file

The E2E environment uses `docker-compose.ci.yml` (via `npm run env:up`).

> [!NOTE]
> You can modify the credentials in the `docker-compose.yml` file.

### Learn more about E2E testing

Resources:

- [Playwright Documentation](https://playwright.dev/docs/intro)
- https://github.com/WordPress/gutenberg/blob/trunk/packages/e2e-test-utils-playwright/README.md
- https://developer.wordpress.org/block-editor/contributors/code/testing-overview/

To see more examples of E2E tests, check the Gutenberg repository: https://github.com/WordPress/gutenberg/tree/trunk/test/e2e

> [!NOTE]
> If you are out of ideas on who to test, check the Gutenberg repository. It has a lot of examples of E2E tests that you can use as a reference.

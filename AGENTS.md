# Agent Workflow

## Project Overview

Visualizer is a WordPress plugin for creating interactive charts and tables. It uses Google Visualization API, Chart.js, and DataTables.net as rendering libraries. Charts are stored as a custom post type (`visualizer`) with post meta prefixed `visualizer-*`. The plugin has both a classic PHP/jQuery dashboard editor and a React-based Gutenberg block editor — changes to chart settings UI must be synchronized in both.

## Commands

### PHP
```bash
composer install                    # Install PHP dependencies
composer lint                       # Run PHPCS (WordPress coding standards)
composer format                     # Auto-fix PHPCS issues
composer phpstan                    # Run PHPStan static analysis (level 6)
./vendor/bin/phpunit                # Run all PHPUnit tests
./vendor/bin/phpunit tests/test-export.php  # Run a single test file
```

### Gutenberg Block (from `classes/Visualizer/Gutenberg/`)
```bash
npm install                         # Install JS dependencies
npm run build                       # Production build
npm run dev                         # Watch mode for development
```

### E2E Tests & Environment
```bash
npm install                         # Install root JS dependencies (Playwright, etc.)
npm run test:env:start              # Start wp-env WordPress environment
npm run test:env:stop               # Stop wp-env
npm run test:e2e:playwright         # Run Playwright E2E tests
npm run test:e2e:playwright:debug   # Run Playwright tests with UI
```

## Architecture

### Module System
The plugin uses a module-based architecture rooted in `classes/Visualizer/`. Each module extends `Visualizer_Module`:

- **Module/Admin** — Admin dashboard, chart library page
- **Module/Frontend** — Frontend rendering, shortcode handling, asset enqueueing
- **Module/Chart** — Chart CRUD operations
- **Module/Sources** — Data source management
- **Module/Wizard** — First-time setup wizard
- **Module/AMP** — AMP compatibility

### Data Flow
1. Chart markup is placed on page via shortcode (`[visualizer id="123"]`) or Gutenberg block (`gutenberg_block_callback` in `Block.php`)
2. Chart data is loaded into `window.visualizer` global object (also available via REST endpoint)
3. `js/render-facade.js` scans for chart markup and dispatches rendering to the appropriate library
4. Library-specific renderers: `js/render-google.js`, `js/render-chartjs.js`, `js/render-datatables.js`

### Two Editor Systems
- **Classic editor**: PHP templates in `classes/Visualizer/Render/` with jQuery interactions in `js/frame.js` and `js/library.js`
- **Gutenberg block**: React components in `classes/Visualizer/Gutenberg/src/`, built with Webpack to `classes/Visualizer/Gutenberg/build/`

Import UI lives in `classes/Visualizer/Render/Layout.php` (classic) and `classes/Visualizer/Gutenberg/src/Components/Import` (Gutenberg).

### Data Sources
Source handlers in `classes/Visualizer/Source/` parse CSV, JSON, database queries, and post meta. Sample data files are in `samples/`.

### Pro Version
The plugin checks for a companion `visualizer-pro` plugin. Pro features (database import, WooCommerce reports, advanced chart types) are in the separate `visualizer-pro` repository at `inc/addon.php`.

## Key Configuration
- **Plugin constants and post type**: `classes/Visualizer/Plugin.php`
- **Plugin entry point and autoloader**: `index.php`
- **PHPCS rules**: `phpcs.xml` (WordPress-Core + WordPress-Docs)
- **PHPStan**: `phpstan.neon` (level 6, baseline in `phpstan-baseline.neon`)
- **Playwright config**: `tests/e2e/playwright.config.js`
- **Semantic release**: `.releaserc.yml`

## Coding Standards
- Follow WordPress Coding Standards (enforced by PHPCS)
- PHP 7.4 minimum compatibility
- Text domain: `visualizer`
- Escape all output (`esc_html()`, `esc_attr()`, `wp_kses_post()`)
- Sanitize all input (`sanitize_text_field()`, etc.)
- Prepare all database queries with `$wpdb->prepare()`
- Use WordPress nonces for form submissions

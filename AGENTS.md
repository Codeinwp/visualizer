# Agent Workflow — Visualizer (Free Plugin)

## What This Plugin Does

**Visualizer** is a WordPress plugin that lets users create interactive, responsive charts and data tables and embed them in posts/pages via a shortcode (`[visualizer id="123"]`) or a native Gutenberg block. Charts are stored as a custom post type and rendered in the browser using one of three JavaScript libraries: **Google Visualization API**, **Chart.js**, or **DataTables.net**.

**Supported chart types (free):** Pie, Line, Bar, Column, Area, Scatter, Bubble, Gauge, Candlestick, Geo, Table (tabular)

**Data sources:** Manual entry (spreadsheet), CSV upload, Remote CSV URL, JSON endpoint, WordPress database query

**Export formats:** CSV, Excel (XLSX via OpenSpout), printable HTML, image screenshot

The companion **visualizer-pro** plugin (separate repo) adds: additional chart types (Combo, Timeline, Polar Area, Radar, DataTable), remote database import (MySQL, PostgreSQL, MS SQL, etc.), scheduled data sync, per-chart user/role permissions, WooCommerce report integration, and an inline frontend data editor.

---

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

### Gutenberg Block (run from `classes/Visualizer/Gutenberg/`)
```bash
npm install                         # Install JS dependencies
npm run build                       # Production build → build/block.js
npm run dev                         # Watch mode for development
```

### E2E Tests & Environment
```bash
npm install                         # Install root-level JS dependencies
npm run test:env:start              # Start wp-env WordPress environment
npm run test:env:stop               # Stop wp-env
npm run test:e2e:playwright         # Run Playwright E2E tests
npm run test:e2e:playwright:debug   # Playwright UI debug mode
```

---

## Architecture Overview

### Bootstrapping (`index.php`)
1. Registers a PSR-4-style autoloader: class `Visualizer_Module_Admin` → `classes/Visualizer/Module/Admin.php`
2. Defines constants: `VISUALIZER_BASEFILE`, `VISUALIZER_ABSPATH`, `VISUALIZER_REST_VERSION` (=1)
3. Creates singleton `Visualizer_Plugin` (holds plugin-wide constants and the version)
4. On `plugins_loaded`: registers modules in this order:
   - `Visualizer_Module_Utility` → `Visualizer_Module_Setup` → `Visualizer_Module_Sources` → `Visualizer_Module_Chart` → `Visualizer_Module_Admin` (admin only) → `Visualizer_Module_Frontend` → `Visualizer_Module_AMP` → `Visualizer_Module_Wizard`
5. Registers the Gutenberg block (`visualizer/chart`) via `Visualizer_Gutenberg_Block`

### Module System
All modules extend `Visualizer_Module`, which provides hook registration helpers (`_addAction`, `_addFilter`, `_addAjaxAction`, `_addShortcode`) and shared utilities (CSV/XLS/HTML/image export, revision management, chart data retrieval).

| Module | Responsibility |
|--------|---------------|
| `Module/Setup` | Registers the `visualizer` CPT, activation/deactivation hooks, WP-Cron schedules, text domain |
| `Module/Admin` | Admin menu, chart library page, editor asset enqueueing, screen options, review notices |
| `Module/Chart` | All chart CRUD AJAX endpoints, sidebar filter, data upload/export |
| `Module/Sources` | Intercepts `visualizer-get-chart-series` / `visualizer-get-chart-data` filters; dynamically loads the correct source handler from post meta |
| `Module/Frontend` | Shortcode `[visualizer]`, frontend asset enqueueing, REST action endpoint, lazy-load support |
| `Module/Utility` | Color utilities (hex → RGBA), default 20-color palette |
| `Module/AMP` | AMP-compatible chart rendering |
| `Module/Wizard` | First-run setup wizard |

---

## Data Model

### Custom Post Type: `visualizer`
Charts are stored as WordPress posts of type `visualizer`. The post title is the chart name; the actual chart data and configuration live in post meta.

### Key Post Meta Fields (`visualizer-*`)
| Meta Key (constant) | Stores |
|---------------------|--------|
| `CF_CHART_TYPE` (`visualizer-chart-type`) | Chart type slug: `pie`, `line`, `bar`, `column`, `area`, etc. |
| `CF_CHART_LIBRARY` (`visualizer-chart-library`) | Rendering library: `GoogleCharts`, `ChartJS`, `DataTable` |
| `CF_SOURCE` (`visualizer-source`) | Source class name, e.g. `Visualizer_Source_Csv`, `Visualizer_Source_Json` |
| `CF_SERIES` (`visualizer-series`) | PHP-serialized array of series definitions: `[{label, type}, ...]` where `type` is `string\|number\|boolean\|date\|datetime\|timeofday` |
| `CF_DEFAULT_DATA` (`visualizer-default-data`) | PHP-serialized 2D array of chart data rows (row 0 = header labels) |
| `CF_SETTINGS` (`visualizer-settings`) | JSON-encoded chart display settings (title, colors, axes, legend, etc.) |
| `CF_JSON_URL` | JSON endpoint URL |
| `CF_JSON_ROOT` | Dot-notation path into JSON response (e.g. `data.results`) |
| `CF_JSON_SCHEDULE` | Refresh interval in hours |
| `CF_JSON_PAGING` | Paging config for paginated JSON APIs |
| `CF_JSON_HEADERS` | Custom HTTP headers for authenticated JSON endpoints |
| `CF_CHART_URL` | Remote CSV URL |
| `CF_CHART_SCHEDULE` | Remote CSV refresh interval (hours) |
| `CF_DB_QUERY` (`visualizer-db-query`) | SQL SELECT query text |
| `CF_DB_SCHEDULE` | DB query refresh interval |
| `CF_REMOTE_DB_PARAMS` | Remote database connection params (pro only) |
| `CF_CHART_CACHE` | Cached chart data (transient key suffix) |
| `CF_PERMISSIONS` | Per-chart user/role read+edit permissions (pro only) |
| `CF_IS_WOOCOMMERCE` | Boolean flag marking chart as WooCommerce data source |

---

## Data Sources (`classes/Visualizer/Source/`)

All sources extend `Visualizer_Source` and implement:
- `fetch()` — Parse and load data into `$this->_series` and `$this->_data`
- `getSeries()` — Return series definitions array
- `getData()` — Return serialized data
- `repopulateSeries($series, $chart_id)` — Refresh series from external source
- `repopulateData($data, $chart_id)` — Refresh data from external source

| Source Class | What it does |
|--------------|-------------|
| `Source/Csv.php` | Reads a local CSV file. Row 1 = column labels, row 2 = data types (`string`, `number`, `date`, etc.), rows 3+ = data. Respects `VISUALIZER_CSV_DELIMITER` and `VISUALIZER_CSV_ENCLOSURE` constants. Converts to UTF-8. |
| `Source/Csv/Remote.php` | Fetches CSV from an external URL via `wp_remote_get()`. Same parsing as local CSV. |
| `Source/Json.php` | Fetches JSON from a URL. Navigates to the root node via `CF_JSON_ROOT`. Handles pagination with `CF_JSON_PAGING`. Sends custom headers from `CF_JSON_HEADERS`. |
| `Source/Query.php` | Executes a SQL SELECT against the WordPress database (`$wpdb`). Strips SQL comments. Maps column types from MySQL schema. Supports remote databases via `CF_REMOTE_DB_PARAMS` (pro). |
| `Source/Query/Params.php` | Builds dynamic queries using a table/column mapping UI — used by the WordPress post-type query builder in Pro. |

`Module/Sources` intercepts the data filters and calls the appropriate source class based on `CF_SOURCE` post meta, allowing any external source to override chart data at render time.

---

## Rendering Pipeline: Database → Browser

### Backend (shortcode/block → HTML)
1. `[visualizer id="123"]` is processed by `Module/Frontend::renderChart()`
2. Post meta is loaded for chart ID 123
3. Series and data pass through filters:
   - `apply_filters('visualizer-get-chart-series', $series, $chart_id)`
   - `apply_filters('visualizer-get-chart-data', $data, $chart_id)`
   - `apply_filters('visualizer-get-chart-settings', $settings, $chart_id)`
4. A `<div>` container is output with `data-*` attributes carrying chart type, library, series JSON, data JSON, and settings JSON
5. Frontend scripts are enqueued (library-specific JS + `render-facade.js`)

### Frontend (JS → Rendered Chart)
1. **`js/render-facade.js`** scans the page for chart containers, reads `data-*` attributes, and dispatches to the correct renderer. Also initializes action buttons (export CSV/XLS, print, copy image).
2. **`js/render-google.js`** — Creates a `google.visualization.DataTable`, populates rows, instantiates the chart type, and calls `.draw()`. Handles responsive resize.
3. **`js/render-chartjs.js`** — Converts series/data into Chart.js dataset format, applies color/axis settings, calls `new Chart(ctx, config)`.
4. **`js/render-datatables.js`** — Populates an HTML `<table>` then initializes DataTables.net with sorting, pagination, and search.

Charts support **lazy loading**: the container is observed with `IntersectionObserver` and only rendered when scrolled into view (controlled by `visualizer_lazy_by_default` filter).

---

## Editor Systems

There are two fully independent editors that must be kept in sync for any chart settings UI changes.

### 1. Classic Editor (PHP + jQuery)
Used in the WordPress admin chart library and in TinyMCE/classic block contexts.

- **Chart library page** (`Render/Library.php`): Lists all `visualizer` posts with filtering by type/date/search. Uses a media-frame-style iframe modal.
- **Chart creation wizard**: Multi-step flow via AJAX — type selection → data import → settings → save
- **Render/Page/** classes handle each wizard step:
  - `Page/Types.php` — Chart type and library selection
  - `Page/Data.php` — Data input (file upload, URL, manual spreadsheet)
  - `Page/Settings.php` — Settings sidebar rendered by `Render/Sidebar/Type/{Library}/{Type}.php` classes
  - `Page/Update.php` — Save handler
- **`js/frame.js`** — Initializes all tabs, opens correct panels, wires up type selection radio buttons, initializes the Handsontable spreadsheet editor for manual data entry
- **`js/library.js`** — Media library integration, thumbnail sizing, chart selection frame, filtering

Sidebar classes under `Render/Sidebar/Type/` are organized by library (`GoogleCharts/`, `ChartJS/`, `DataTable/`) and chart type. Each renders the PHP HTML form controls for that chart's specific settings.

### 2. Gutenberg Block Editor (React)
Registered in `Gutenberg/Block.php` as `visualizer/chart` with server-side render callback.

- **Source:** `classes/Visualizer/Gutenberg/src/` → compiled to `build/block.js` via Webpack
- **Entry point:** `src/index.js` → `src/Editor.js`
- **Key React components:**
  - `Components/ChartEditor.js` — Master editor wrapper; manages state
  - `Components/ChartSelect.js` — Chart type selection dropdown
  - `Components/DataTable.js` — Handsontable spreadsheet for manual data
  - `Components/Sidebar.js` — Inspector Controls sidebar (type-specific settings panels)
  - `Components/ChartRender.js` — Live preview rendering
  - `Components/ChartPermissions.js` — User/role permission controls (pro)
  - `Components/Import/` — Import type components:
    - `FileImport.js` — CSV file upload
    - `RemoteImport.js` — Remote CSV URL
    - `JSONImport.js` — JSON endpoint config
    - `SQLEditor.js` — Database query editor
    - `ManualData.js` — Spreadsheet editing
    - `ChartImport.js` — Clone from existing chart

---

## REST API Endpoints

All endpoints are registered in `Module/Frontend` and `Gutenberg/Block.php` under namespace `visualizer/v1`.

### Frontend Action Endpoint
`GET|POST /wp-json/visualizer/v1/action/{chart_id}/{type}/`
- Handles chart data export and custom actions
- `type` values: `csv`, `xls`, `print`, `image`, or any custom action string
- Requires nonce: `visualizer-export-data` + VERSION
- Requires capability: `manage_posts` (or `visualizer_pro_show_chart` for read)

### Gutenberg Block Endpoints
| Route | Method | Purpose |
|-------|--------|---------|
| `/visualizer/v1/get-query-data` | POST | Execute SQL query, return results |
| `/visualizer/v1/get-json-root` | GET | Extract root path options from a JSON URL |
| `/visualizer/v1/get-json-data` | GET | Fetch and parse JSON from a URL |
| `/visualizer/v1/set-json-data` | POST | Save JSON endpoint configuration |
| `/visualizer/v1/update-chart` | POST | Save chart data and settings |
| `/visualizer/v1/upload-data` | POST | Process uploaded CSV/file data |
| `/visualizer/v1/get-permission-data` | GET | Fetch users/roles list for permissions UI |

---

## AJAX Endpoints (Classic Editor)

All are `wp_ajax_*` (authenticated only), verified with nonce and `current_user_can()`.

| Action | Handler | Purpose |
|--------|---------|---------|
| `visualizer-get-charts` | `Module/Admin` | Fetch paginated chart list |
| `visualizer-create-chart` | `Module/Chart` | Render chart creation wizard |
| `visualizer-edit-chart` | `Module/Chart` | Render chart edit wizard |
| `visualizer-clone-chart` | `Module/Chart` | Duplicate a chart |
| `visualizer-delete-chart` | `Module/Chart` | Delete a chart |
| `visualizer-upload-data` | `Module/Chart` | Parse and store uploaded data |
| `visualizer-export-data` | `Module/Chart` | Export chart data (CSV/XLS/etc.) |
| `visualizer-fetch-db-data` | `Module/Chart` | Execute a DB query and return results |
| `visualizer-save-db-query` | `Module/Chart` | Save a database query to post meta |
| `visualizer-json-get-roots` | `Module/Chart` | Extract JSON root path options |
| `visualizer-json-get-data` | `Module/Chart` | Fetch data from JSON URL |
| `visualizer-json-set-data` | `Module/Chart` | Save JSON endpoint config |
| `visualizer-json-set-schedule` | `Module/Chart` | Set JSON refresh schedule |
| `visualizer-save-filter-query` | `Module/Chart` | Save WordPress post-type filter config |
| `visualizer-fetch-permissions-data` | `Module/Chart` | Fetch users/roles for permissions UI |

---

## Key Filters & Actions

These are the primary hooks the plugin exposes. Pro and third-party code hooks into these.

### Data Filters
```
visualizer-get-chart-series      ($series, $chart_id)   — Modify series before render
visualizer-get-chart-data        ($data, $chart_id)     — Modify data before render
visualizer-get-chart-settings    ($settings, $chart_id) — Modify settings before render
visualizer_format_data           ($data)                — Format data for display
visualizer_date_formats          ($formats)             — Date format definitions
visualizer_db_tables             ($tables)              — Available DB tables
visualizer_db_table_columns      ($cols, $table)        — Table column definitions
visualizer_db_tables_column_mapping ($map)              — Full column map (used by pro)
visualizer_db_prefix             ($prefix)              — DB table prefix override
```

### Feature/Capability Flags (used by pro)
```
visualizer_is_pro                ($bool)   — Whether pro plugin is active
visualizer_is_new_personal       ($bool)   — Whether on personal plan
visualizer_is_business           ($bool)   — Whether on business/developer plan
visualizer_pro_show_chart        ($bool, $chart_id)  — Whether current user can view chart
```

### UI Filters
```
visualizer_pro_chart_type_sidebar  ($html, $type, $library) — Inject pro sidebar panels
visualizer_action_buttons          ($buttons, $chart_id)    — Add/remove action buttons
visualizer_action_attributes       ($attrs, $type)          — Custom action button attrs
visualizer_container_attributes    ($attrs, $chart_id)      — Chart container HTML attrs
visualizer_inline_css              ($css, $chart_id)        — Inline chart container CSS
visualizer_lazy_by_default         ($bool)                  — Default lazy-load setting
visualizer_lazy_load_chart         ($bool, $chart_id)       — Per-chart lazy load
visualizer_chart_schedules         ($schedules)             — Add schedule options to UI
```

### Actions
```
visualizer_pro_frontend_load_resources  — Load pro JS/CSS on frontend pages with charts
visualizer_schedule_refresh_chart       — WP-Cron action: refresh a chart's data
visualizer_schedule_refresh_db          — WP-Cron action: refresh DB-query charts
```

---

## Scheduling (WP-Cron)

`Module/Setup` registers custom cron schedules (10min, hourly, 12hr, daily, 3-day). When a chart has a schedule set (via `CF_CHART_SCHEDULE`, `CF_JSON_SCHEDULE`, or `CF_DB_SCHEDULE`), a cron event fires the appropriate refresh action. The pro plugin implements the actual refresh handlers that re-fetch data from external sources and update post meta.

---

## Key Configuration Files
| File | Purpose |
|------|---------|
| `index.php` | Plugin entry point, autoloader, bootstrap |
| `classes/Visualizer/Plugin.php` | All constants (post type name, meta key names, version) |
| `phpcs.xml` | PHPCS rules (WordPress-Core + WordPress-Docs) |
| `phpstan.neon` | PHPStan level 6; baseline: `phpstan-baseline.neon` |
| `tests/e2e/playwright.config.js` | Playwright E2E config |
| `.releaserc.yml` | Semantic release config |
| `classes/Visualizer/Gutenberg/webpack.config.js` | Webpack config for block build |

---

## Coding Standards
- WordPress Coding Standards enforced by PHPCS
- PHP 7.4 minimum; no PHP 8-only syntax
- Text domain: `visualizer`
- Escape all output: `esc_html()`, `esc_attr()`, `wp_kses_post()`
- Sanitize all input: `sanitize_text_field()`, `absint()`, `FILTER_VALIDATE_INT`
- Prepare all DB queries: `$wpdb->prepare()`
- WordPress nonces required for all form submissions and AJAX calls
- Capability checks (`current_user_can()`) before any privileged operation

---

## File Map (Abridged)
```
visualizer/
├── index.php                          # Entry point, autoloader, bootstrap
├── uninstall.php                      # Cleanup on uninstall
├── classes/Visualizer/
│   ├── Plugin.php                     # Constants, singleton, version
│   ├── Module.php                     # Base module class (hook helpers, export utils)
│   ├── Render.php                     # Base render class
│   ├── Module/
│   │   ├── Admin.php                  # Admin UI, chart library
│   │   ├── Frontend.php               # Shortcode, REST endpoint, asset enqueueing
│   │   ├── Chart.php                  # Chart CRUD AJAX endpoints
│   │   ├── Sources.php                # Data source filter interception
│   │   ├── Setup.php                  # CPT registration, cron, activation hooks
│   │   ├── Utility.php                # Color utilities
│   │   ├── AMP.php                    # AMP rendering
│   │   └── Wizard.php                 # Setup wizard
│   ├── Source/
│   │   ├── Source.php                 # Abstract base source
│   │   ├── Csv.php                    # Local CSV
│   │   ├── Csv/Remote.php             # Remote CSV URL
│   │   ├── Json.php                   # JSON endpoint
│   │   ├── Query.php                  # SQL query
│   │   └── Query/Params.php           # Dynamic query builder params
│   ├── Render/
│   │   ├── Library.php                # Chart library admin page
│   │   ├── Page/Types.php             # Chart type selection step
│   │   ├── Page/Data.php              # Data import step
│   │   ├── Page/Settings.php          # Settings step
│   │   ├── Page/Update.php            # Save step
│   │   ├── Sidebar/Type/GoogleCharts/ # Google Charts settings forms
│   │   ├── Sidebar/Type/ChartJS/      # ChartJS settings forms
│   │   └── Sidebar/Type/DataTable/    # DataTable settings forms
│   └── Gutenberg/
│       ├── Block.php                  # Block registration + REST endpoints
│       ├── build/block.js             # Compiled React block (do not edit directly)
│       ├── src/index.js               # Block entry point
│       ├── src/Editor.js              # Main block edit component
│       ├── src/Components/            # React UI components
│       └── webpack.config.js          # Build config
├── js/
│   ├── frame.js                       # Classic editor initialization
│   ├── library.js                     # Chart library + media frame UI
│   ├── render-facade.js               # Frontend: action buttons + renderer dispatch
│   ├── render-google.js               # Google Charts renderer
│   ├── render-chartjs.js              # Chart.js renderer
│   ├── render-datatables.js           # DataTables.net renderer
│   └── customization.js              # User-defined chart customizations hook
├── samples/                           # Sample chart data files
├── tests/                             # PHPUnit tests
└── vendor/                            # Composer packages (OpenSpout for XLSX)
```

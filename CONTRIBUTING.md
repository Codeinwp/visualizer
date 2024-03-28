## Releasing

This repository uses conventional [changelog commit](https://github.com/Codeinwp/conventional-changelog-simple-preset) messages to trigger release

How to release a new version:

- Clone the master branch
- Do your changes
- Send a PR to master and merge it using the following subject message
  - `release: <release short description>` - for patch release
  - `release(minor): <release short description>` - for minor release
  - `release(major): <release short description>` - for major release
    The release notes will inherit the body of the commit message which triggered the release. For more details check the [simple-preset](https://github.com/Codeinwp/conventional-changelog-simple-preset) that we use.

## Architecture

### Introduction

The two main features are:

- data importing
- chart rendering

Those two options are present in the plugin dashboard and as Gutenberg blocks.

All the charts are saved as custom post type (`post_type=visualizer`), and the data is saved as post meta (with the prefix `visualizer-`). (Check plugin settings in `classes/Visualizer/Plugin.php`)

The charts can be embedded in page via shortcode or Gutenberg blocks.

### Data Importing

For data importing, the most basic usage is using a CSV or JSON file. The source can be an uploaded file or a URL. The data is then parsed and stored in a format that can be used by the chart rendering feature.

For parsing the CSV and JSON files, we use built-in PHP functions.

> [!NOTE]
> The `samples` folder contains some sample data files that can be used for testing.

Advanced data importing features include data from:

- a database using a query;
- post meta values withing a desired `post_type`;
- WooCommerce Report API;
- from other charts;
- manually inputted data with the build-in editor.

Related code is in `classes/Visualizer/Source`. Pro Features like database import are in `visualizer-pro` plugin on `inc/addon.php`.
The rendering for Import dashboard is done in `classes/Visualizer/Render/Layout.php`; for Gutenberg Blocks check `classes/Visualizer/Gutenberg/src/Components/Import`.

### Chart Rendering

The chart rendering is done with the help of the [Chart.js](https://www.chartjs.org/) library. The plugin uses the `chartjs` library to render the charts.

The workflow for rendering a chart is:

- We add markup for the chart in the page; ( `[visualizer id="5295" lazy="no" class=""]` can be rendered via shortcode or Gutenberg block with function `gutenberg_block_callback` in `Block.php`);
- We load the data into the global object `window.visualizer`. (Beside the object, and endpoint is also created for fetching the data, available with `rest_url` property);
- Using js (with `render-facade.js`), we search the markup in the page, and using the stored ID, we pull the data from the global object and initiate the chart rendering.

Check the class `Visualizer_Module_Frontend` to see all the hooks and filters that are used to render the charts.

### Data handling

While the data importing and rendering are the main selling points, the most of work is done in the data handling which includes interface rendering, storing, and fetching the data.

The Charts menu settings are available in `Chart Library` page in the admin dashboard, but also in the block `Inspector` as part of the Gutenberg block. The first is done via PHP and jQuery, and the second is done via React. Any design changes should be done in both places.

Same thing for chart library visualization, it can be either from admin dashboard or from Gutenberg block.

> [!NOTE]
> The plugin tries to offer seamless experience for both classic and Gutenberg editor. But the bugs resulted might be different, especially in the Gutenberg editor where changes are happening frequently.

> [!NOTE]
> Unlike the jQeury, the Gutenberg block require a build step, you will need to install all the NPM dependencies and run `npm run dev` or `npm run build` to see the changes.

### Setup Wizard

The `Setup Wizard` is an onboarding process that helps the user to set up its first chart. When the plugin is installed for the first time, the user is redirected to the wizard.

The steps includes:

1. Choosing the chart type;
2. Importing the sample data;
3. Asking the user if he wants a draft page to show the chart;
4. Promotion (optional);
5. Email subscription;
6. Redirect to draft page if the user marked the option at step 3. Otherwise, redirect to the plugin dashboard.

The page rendering is done using the `setup-wizard.php` file, and the server logic is in the `Visualizer_Module_Wizard` class. A jQuery script (`setup-wizard`) is used to handle the interactions.

> [!NOTE]
> The usual process include showing the promotion and email collection form. Those are hidden when the `preview` query parameter is present in the URL. The Preview is used as demo presentation for the `Live Preview` feature of the WordPress store.

### Quirks

#### Number Formatting

The workflow for adding a chart with number formatting is:

1. Select the chart;
2. Add the data without any other specific sign (`$`, `%`, `.`, etc.) -- `2.5` will be added as `25`;
3. Generate the chart;
4. Add number formatting (`#.#` for `2.5`).

For displaying data mentioning a percentage (%), amount, or any other specific signs ($, €, etc.); you need to specify the correct number formatting.

Percentage:

1. When manually inputting the data, if you want to display 80%, you need to add it as `0.8`.
2. Then you go trough the number formatting box and input `#%`.

Amounts:

1. For inputting amounts like `2,345`, you need to input it as `2345`.
2. Then you go to number formatting and input `#,###`.
3. Same thing for `2.5`, you need add the format as `#.#`.

For specific signs:

1. For displaying a sign like `$` (like `$2.345`), you need to follow the same steps as for the amount.
2. Except the number formatting will be `$#,###`.

# CONTRIBUTING GUIDELINES

- [Setup Guide](#setup-guide)
- [Development Guide](#development-guide)
- [Testing Guide](#testing-guide)

# Setup Guide

This document describes how to set up your development environment, so that it is ready to run, develop and test this WordPress Plugin or Theme.

Suggestions are provided for the LAMP/LEMP stack and Git client are for those who prefer the UI over a command line and/or are less familiar with
WordPress, PHP, MySQL and Git - but you're free to use your preferred software.

## Setup

### LAMP/LEMP stack

Any Apache/nginx, PHP 7.x+ and MySQL 5.8+ stack running WordPress. For example, but not limited to:

- Valet (recommended)
- Local by Flywheel
- Docker
- MAMP
- WAMP

### Composer

If [Composer](https://getcomposer.org) is not installed on your local environment, enter the following commands at the command line to install it:

```bash
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (hash_file('sha384', 'composer-setup.php') === '906a84df04cea2aa72f40b5f787e49f22d4c2f19492ac310e8cba5b96ac8b64115ac402c8cd292b8a03482574915d1a8') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
php composer-setup.php
php -r "unlink('composer-setup.php');"
sudo mv composer.phar /usr/local/bin/composer
```

Confirm that installation was successful by entering the `composer --version` command at the command line

### Clone Repository

Using your preferred Git client or command line, clone this repository into the `wp-content/plugins/` folder of your local WordPress installation.

If you prefer to clone the repository elsewhere, and them symlink it to your local WordPress installation, that will work as well.

If you're new to this, use [GitHub Desktop](https://desktop.github.com/) or [Tower](https://www.git-tower.com/mac)

For Plugins the cloned folder should be under `wp-content/plugins/` and for Themes under `wp-content/themes/`.

### Install Dependencies for PHP and JS

In the cloned repository's directory, at the command line, run `composer install`.
This will install the development dependencies. If you want to install just the production dependencies, run `composer install --no-dev`.

The development dependencies include:

- PHPStan
- PHPUnit
- PHP_CodeSniffer
- WordPress Coding Standards
- WordPress PHPUnit Polyfills

For the JS dependencies, run `npm install`.
To watch for changes in the JS files, run `npm run dev` if present or `npm run dist` to build a new version.

### PHP_CodeSniffer

To run PHP_CodeSniffer, run `composer lint`. This will run the WordPress Coding Standards checks.
To fix automatically fixable issues, run `composer format`.

### PHPUnit

To run PHPUnit, run `phpunit` or `./vendor/bin/phpunit` if it is not configured globally.

### E2E Tests

If the folder `e2e-tests` is present, you can run the E2E tests by following the instructions in the [E2E testing](./e2e-tests/README.md).

### Next Steps

With your development environment setup, you'll probably want to start development, which is covered bellow in the **Development Guide**.

# Development Guide

This document describes the high level workflow used when working on a WordPress Plugin or Theme.

You're free to use your preferred IDE and Git client. We recommend PHPStorm or Visual Studio Code, and GitHub CLI.

## Prerequisites

If you haven't yet set up your local development environment with a WordPress Plugin repository installed, refer to the [Setup Guide](#setup-guide).

his is for a new feature that does not have a GitHub Issue number, enter a short descriptive name for the branch, relative to what you're working on

- If this is for a feature/bug that has a GitHub Issue number, enter feat/issue_name or fix/issue_name, where issue_name is a descriptive name for the issue

Once done, make sure you've switched to your new branch, and begin making the necessary code additions/changes/deletions.

## Coding Standards

Code must follow [WordPress Coding standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/), which is checked
when running tests (more on this below).

## Security and Sanitization

When [outputting data](https://developer.wordpress.org/plugins/security/securing-output/), escape it using WordPress' escaping functions such as `esc_html()`, `esc_attr__()`, `wp_kses()`, `wp_kses_post()`.

When reading [user input](https://developer.wordpress.org/plugins/security/securing-input/), sanitize it using WordPress' sanitization functions such as `sanitize_text_field()`, `sanitize_textarea_field()`.

When writing to the database, prepare database queries using `$wpdb->prepare()`

Never trust user input. Sanitize it.

Make use of [WordPress nonces](https://codex.wordpress.org/WordPress_Nonces) for saving form submitted data.

Coding standards will catch any sanitization, escaping or database queries that aren't prepared.

## Composer Packages

We use Composer for package management. A package can be added to one of two sections of the `composer.json` file: `require` or `require-dev`.

### "require"

Packages listed in the "require" directive are packages that the Plugin needs in order to function for end users.

These packages are included when the Plugin is deployed to WordPress.org

Typically, packages listed in this section would be libraries that the Plugin uses.

### "require-dev"

Packages listed in the "require-dev" directive are packages that the Plugin **does not** need in order to function for end users.

These packages are **not** included when the Plugin is deployed to wordpress.org

Typically, packages listed in this section would be internal development tools for testing, such as:

- Coding Standards
- PHPStan
- PHPUnit

## Committing Work

Remember to commit your changes to your branch relatively frequently, with a meaningful, short summary that explains what the change(s) do.
This helps anyone looking at the commit history in the future to find what they might be looking for.

If it's a particularly large commit, be sure to include more information in the commit description.

## Next Steps

Once you've finished your feature or issue, you must write/amend tests for it. Refer to the [Testing Guide](#testing-guide) for a detailed walkthrough
on how to write a test.

# Testing Guide

This document describes how to:

- create and run tests for your development work,
- ensure code meets PHP and WordPress Coding Standards, for best practices and security,
- ensure code passes static analysis, to catch potential errors that tests might miss

If you're new to creating and running tests, this guide will walk you through how to do this.

For those more experienced with creating and running tests, our tests are written in TS for [Playwright](https://playwright.dev/) used for End-to-End testing,
and in PHP for [PHPUnit](https://phpunit.de/).

A PHPUnit guide for WordPress can be found [here](https://make.wordpress.org/core/handbook/testing/automated-testing/phpunit/).

## Prerequisites

If you haven't yet set up your local development environment with this Plugin repository installed, refer to the [Setup Guide](#setup-guide).

If you haven't yet created a branch and made any code changes to the Plugin or Theme, refer to the [Development Guide](#development-guide)

## Write (or modify) a test

If your work creates new functionality, write a test.

If your work fixes existing functionality, check if a test exists. Either update that test, or create a new test if one doesn't exist.

Tests are written in TS using [Playwright](https://playwright.dev/) and PHP using [PHPUnit](https://phpunit.de/).

## Types of Test

There are different types of tests that can be written:

- Acceptance Tests: Test as a non-technical user in the web browser.
- Functional Tests: Test the framework (WordPress).
- Integration Tests: Test code modules in the context of a WordPress website.
- Unit Tests: Test single PHP classes or functions in isolation.
- WordPress Unit Tests: Test single PHP classes or functions in isolation, with WordPress functions and classes loaded.

There is no definitive / hard guide, as a test can typically overlap into different types (such as Acceptance and Functional).

The most important thing is that you have a test for _something_. If in doubt, an Acceptance Test will suffice.

### Writing an Acceptance Test

An acceptance test is a test that simulates a user interacting with the Plugin or Theme in a web browser.
Refer to Writing an End-to-End Test below.

### Writing an End-to-End Test

To write an End-to-End test, create a new file under `e2e-tests/specs` with the name of the spec or functionality you are testing, and add `.spec.test` to the file name.

E.g. for `e2e-tests/specs/checkout.spec.test.js`, the test file should be `checkout.spec.test.js`.

For more information on writing End-to-End tests, refer to the [Playwright documentation](https://playwright.dev/docs/test-intro).

You can check End-to-End [README](./e2e-tests/README.md) for more details.

## Writing a WordPress Unit Test

WordPress Unit tests provide testing of Plugin/Theme specific functions and/or classes, typically to assert that they perform as expected
by a developer. This is primarily useful for testing our API class, and confirming that any Plugin registered filters return
the correct data.

To create a new WordPress Unit Test, create a new file under `tests/php/unit` with the name of the class you are testing, and the suffix `Test`.
The filename should be in `lower-case-with-dash`, and the class name should be in `CamelCase`.

E.g. for `tests/php/unit/class-api-test.php`, the test class should be `class APITest extends \PHPUnit\Framework\TestCase`.

```php
<?php
class APITest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \WpunitTester
     */
    protected $tester;

    public function setUp(): void
    {
        // Before...
        parent::setUp();
        // Your set up methods here.
    }
    public function tearDown(): void
    {
        // Your tear down methods here.
        // Then...
        parent::tearDown();
    }
    // Tests
    public function test_it_works()
    {
        $post = static::factory()->post->create_and_get();

        $this->assertInstanceOf(\WP_Post::class, $post);
    }
}
```

## Run PHPUnit Tests

Once you have written your code and test(s), run the tests to make sure there are no errors.

```bash
./vendor/bin/phpunit tests/php/unit/class-api-test.php
```

Any errors should be corrected by making applicable code or test changes.

## Run PHP CodeSniffer

In the Plugin's or Theme's directory, run the following command to run PHP_CodeSniffer, which will check the code meets Coding Standards
as defined in the `phpcs.tests.xml` configuration:

```bash
composer run lint
```

`--standard=phpcs.tests.xml` tells PHP CodeSniffer to use the Coding Standards rules / configuration defined in `phpcs.tests.xml`.
These differ slightly from WordPress' Coding Standards, to ensure that writing tests isn't a laborious task, whilst maintain consistency
in test coding style.
`-v` produces verbose output
`-s` specifies the precise rule that failed

Any errors should be corrected by either:

- making applicable code changes
- running `composer run format` to automatically fix coding standards

Need to change the PHP or WordPress coding standard rules applied? Either:

- ignore a rule in the affected code, by adding `phpcs:ignore {rule}`, where {rule} is the given rule that failed in the above output.
- edit the [phpcs.tests.xml](phpcs.tests.xml) file.

## Next Steps

Once your test(s) are written and successfully run locally, submit your branch via a new **Pull Request**.

It's best to create a Pull Request in draft mode, as this will trigger all tests to run as a GitHub Action, allowing you to
double-check all tests pass.

If the PR tests fail, you can make code changes as necessary, pushing to the same branch. This will trigger the tests to run again.

If the PR tests pass, you can publish the PR, assigning some reviewers.

## Miscellaneous

### WordPress Store Plugin Preview

The plugin preview for the WordPress store is available via the `.wordpress-org/blueprints/blueprint.json` file. Read more about this feature in the [announcement](https://make.wordpress.org/meta/2023/12/08/plugin-previews-are-now-available-for-opt-in/).

Examples:

- https://github.com/dss-web/jobbnorge-block/blob/main/.wordpress-org/blueprints/blueprint.json
- https://github.com/WordPress/wordpress-playground/blob/c6da5d622e7e49bd151bab7b72ac17501a21fea5/packages/docs/site/docs/03-build-an-app/01-index.md
- https://github.com/TablePress/TablePress/blob/62aab50e7a9c486caaeff26dff4dc01e059ecb91/.wordpress-org/blueprints/blueprint.json
- https://github.com/johnbillion/user-switching/blob/d26e982fc1389fff24e6d6572e238066e2b056d2/.wordpress-org/blueprints/blueprint.json
- https://github.com/10up/ads-txt/blob/ef95e10f5a9973aaf4cad773e8e78aafd03af059/.wordpress-org/blueprints/blueprint.json
- https://github.com/janw-me/default-featured-image/blob/de4d226216105f3d8c573dfbf28bbba89330286e/.wordpress-org/blueprints/blueprint.json

> [!NOTE]
> Make sure to respect the [schema](https://github.com/WordPress/wordpress-playground/blob/trunk/packages/playground/blueprints/public/blueprint-schema.json). You can validate your blueprint using a schema validator: [online](https://www.jsonschemavalidator.net/), [CLI](https://ajv.js.org/packages/ajv-cli.html)

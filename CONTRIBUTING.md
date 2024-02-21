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

> ![NOTE]
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

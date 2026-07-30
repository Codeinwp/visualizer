<?php
/**
 * E2E test helper (loaded as an mu-plugin via .wp-env.json).
 *
 * Charts created through the quick wizard have no `lazy_load_chart` setting,
 * so force lazy rendering on the frontend to exercise the delayed script
 * loader in lazy-render.spec.js. Elementor previews are unaffected: the
 * widget force-disables lazy rendering with the same filter during render.
 */
add_filter( 'visualizer_lazy_load_chart', '__return_true' );

<?php
/**
 * Enqueue scripts and styles for the front end.
 *
 * Handles conditional loading of external libraries (Flatpickr), main CSS,
 * and the core JavaScript logic — only on pages that have a configured
 * Landeseiten form.
 *
 * @package LandeseitenForm
 * @since   1.0.0
 * @version 2.0.2
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Register (but don't enqueue) frontend assets.
 *
 * Assets are only enqueued later by lf_apply_form_settings() via
 * lf_enqueue_registered_assets() when a configured form is found on the page.
 *
 * @since 2.0.2
 */
function lf_register_assets() {
    // 1. Library: Flatpickr (bundled locally for reliability)
    wp_register_style( 'flatpickr-css', LF_PLUGIN_URL . 'vendor/flatpickr/css/flatpickr.min.css', [], '4.6.13' );
    wp_register_style( 'flatpickr-airbnb', LF_PLUGIN_URL . 'vendor/flatpickr/css/airbnb.css', [ 'flatpickr-css' ], '4.6.13' );
    wp_register_script( 'flatpickr-js', LF_PLUGIN_URL . 'vendor/flatpickr/js/flatpickr.min.js', [], '4.6.13', true );

    // Locale files (loaded conditionally by gravity-forms-hooks.php)
    wp_register_script( 'flatpickr-l10n-de', LF_PLUGIN_URL . 'vendor/flatpickr/js/l10n-de.js', [ 'flatpickr-js' ], '4.6.13', true );

    // 2. Main Stylesheet
    wp_register_style(
        'landeseiten-form-styles',
        LF_PLUGIN_URL . 'assets/css/main.css',
        [],
        LF_VERSION
    );

    // 3. Main JavaScript Logic
    wp_register_script(
        'landeseiten-form-main-script',
        LF_PLUGIN_URL . 'assets/js/main.js',
        [ 'flatpickr-js' ],
        LF_VERSION,
        true
    );

    // 4. Initializer Script
    wp_register_script(
        'landeseiten-form-init-script',
        LF_PLUGIN_URL . 'assets/js/init.js',
        [ 'landeseiten-form-main-script' ],
        LF_VERSION,
        true
    );
}
add_action( 'wp_enqueue_scripts', 'lf_register_assets', 5 );

/**
 * Enqueue the previously registered assets.
 *
 * Called from gravity-forms-hooks.php when a configured form is detected
 * on the current page. This ensures assets only load where needed.
 *
 * @since 2.0.2
 */
function lf_enqueue_registered_assets() {
    static $enqueued = false;

    if ( $enqueued ) {
        return;
    }

    wp_enqueue_style( 'flatpickr-css' );
    wp_enqueue_style( 'flatpickr-airbnb' );
    wp_enqueue_script( 'flatpickr-js' );
    wp_enqueue_style( 'landeseiten-form-styles' );
    wp_enqueue_script( 'landeseiten-form-main-script' );
    wp_enqueue_script( 'landeseiten-form-init-script' );

    $enqueued = true;
}
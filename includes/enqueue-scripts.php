<?php
/**
 * Enqueue scripts and styles for the front end.
 *
 * Handles loading of external libraries (Flatpickr), main CSS, and the core
 * JavaScript logic required for the multi-step form functionality.
 *
 * @package LandeseitenForm
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Enqueue frontend assets.
 *
 * @since 1.0.0
 */
function lf_enqueue_assets() {

    // 1. External Library: Flatpickr (Required for Date Range Picker)
    // We use the Airbnb theme for a cleaner, modern look out-of-the-box.
    wp_enqueue_style( 'flatpickr-css', 'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css', [], '4.6.13' );
    wp_enqueue_style( 'flatpickr-airbnb', 'https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/airbnb.css', ['flatpickr-css'], '4.6.13' );
    wp_enqueue_script( 'flatpickr-js', 'https://cdn.jsdelivr.net/npm/flatpickr', [], '4.6.13', true );

    // 2. Main Stylesheet
    // Contains all core layout, animations, and variable-based theming.
    wp_enqueue_style(
        'landeseiten-form-styles',
        LF_PLUGIN_URL . 'assets/css/main.css',
        [],
        LF_VERSION // Uses the constant defined in the main plugin file
    );

    // 3. Main JavaScript Logic
    // Contains the core classes (Field, Validator, LandeseitenForm) and progress bar logic.
    // 'flatpickr-js' is listed as a dependency to ensure the library loads first.
    wp_enqueue_script(
        'landeseiten-form-main-script',
        LF_PLUGIN_URL . 'assets/js/main.js',
        ['flatpickr-js'], 
        LF_VERSION,
        true
    );

    // 4. Initializer Script
    // Bootstraps the form instance when the DOM is ready.
    wp_enqueue_script(
        'landeseiten-form-init-script',
        LF_PLUGIN_URL . 'assets/js/init.js',
        ['landeseiten-form-main-script'],
        LF_VERSION,
        true
    );
}
add_action( 'wp_enqueue_scripts', 'lf_enqueue_assets' );
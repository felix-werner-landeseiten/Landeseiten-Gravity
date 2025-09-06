<?php
/**
 * Enqueue scripts and styles for the front end.
 *
 * @package LandeseitenForm
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Enqueues scripts and styles for the front end.
 *
 * This function is hooked into 'wp_enqueue_scripts'.
 */
function lf_enqueue_assets() {

    // 1. Enqueue the main stylesheet
    wp_enqueue_style(
        'landeseiten-form-styles',                      // A unique handle for our stylesheet
        LF_PLUGIN_URL . 'assets/css/main.css',          // Full URL to the file
        [],                                             // No dependencies
        '1.0.0'                                         // Version number
    );

    // 2. Enqueue the main JavaScript class file
    wp_enqueue_script(
        'landeseiten-form-main-script',                 // A unique handle for our main script
        LF_PLUGIN_URL . 'assets/js/main.js',            // Full URL to the file
        [],                                             // No dependencies
        '1.0.0',                                        // Version number
        true                                            // Load in the footer
    );

    // 3. Enqueue the initializer script, and make it dependent on the main script
    wp_enqueue_script(
        'landeseiten-form-init-script',                 // A unique handle for our init script
        LF_PLUGIN_URL . 'assets/js/init.js',            // Full URL to the file
        ['landeseiten-form-main-script'],               // Important: This script depends on the main one!
        '1.0.0',                                        // Version number
        true                                            // Load in the footer
    );
}
add_action( 'wp_enqueue_scripts', 'lf_enqueue_assets' );
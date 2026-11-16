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
 * Turns the saved calendar language into a Flatpickr locale code.
 *
 * "auto" uses the page locale, which WPML / Polylang switch per language
 * (de_DE → "de", en_US → "en").
 *
 * @since 2.5.0
 * @param string $saved Saved setting: "auto", "en", "de" or '' (legacy: English).
 * @return string Two-letter code, e.g. "de".
 */
function lf_resolve_calendar_lang( $saved ) {
    if ( 'auto' !== $saved ) {
        return (string) $saved;
    }
    return strtolower( substr( (string) get_locale(), 0, 2 ) );
}

/**
 * Enqueues the bundled Flatpickr locale for $lang.
 *
 * @since 2.5.0
 * @param string $lang Two-letter code.
 * @return bool False when no locale file is bundled (Flatpickr stays English).
 */
function lf_enqueue_calendar_locale( $lang ) {
    $lang   = sanitize_key( $lang );
    $handle = 'flatpickr-l10n-' . $lang;

    if ( ! wp_script_is( $handle, 'registered' ) ) {
        $file = 'vendor/flatpickr/js/l10n-' . $lang . '.js';
        if ( '' === $lang || ! file_exists( LF_PLUGIN_DIR . $file ) ) {
            return false;
        }
        wp_register_script( $handle, LF_PLUGIN_URL . $file, [ 'flatpickr-js' ], '4.6.13', true );
    }

    wp_enqueue_script( $handle );
    return true;
}

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

    // Block themes render the content (and so gform_pre_render) before
    // wp_enqueue_scripts. Register now, or wp_localize_script() drops the
    // per-form settings because the handle does not exist yet.
    if ( ! wp_script_is( 'landeseiten-form-init-script', 'registered' ) ) {
        lf_register_assets();
    }

    wp_enqueue_style( 'flatpickr-css' );
    wp_enqueue_style( 'flatpickr-airbnb' );
    wp_enqueue_script( 'flatpickr-js' );
    wp_enqueue_style( 'landeseiten-form-styles' );
    wp_enqueue_script( 'landeseiten-form-main-script' );
    wp_enqueue_script( 'landeseiten-form-init-script' );

    $enqueued = true;
}
<?php
/**
 * Plugin Name:       Landeseiten Form for Gravity Forms
 * Description:       A premium wrapper for Gravity Forms to create multi-step, animated user experiences with modern styling.
 * Version:           2.3.5
 * Author:            Landeseiten.de
 * Author URI:        https://landeseiten.de
 * License:           GPLv2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       landeseiten-form
 * Requires at least: 5.8
 * Requires PHP:      7.4
 *
 * @package           LandeseitenForm
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Define plugin constants.
 *
 * @since 1.0.0
 */
define( 'LF_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'LF_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'LF_VERSION', '2.3.5' );

/**
 * Load core plugin files.
 *
 * The plugin is split into modules for better organization:
 * - enqueue-scripts.php: Handles CSS/JS assets.
 * - post-type.php: Registers the custom post type for configurations.
 * - meta-boxes.php: Handles the backend UI for settings.
 * - gravity-forms-hooks.php: Connects settings to Gravity Forms output.
 */
/**
 * Default frontend validation messages.
 *
 * Kept in one place so the admin UI, the frontend config and the JS
 * defaults (assets/js/init.js) stay in sync.
 *
 * @since 2.3.4
 * @return array<string,string> meta_key => default message
 */
function lf_error_message_defaults() {
	return [
		'_lf_error_required' => 'Dieses Feld ist ein Pflichtfeld.',
		'_lf_error_email'    => 'Die E-Mail-Adresse ist ungültig.',
		'_lf_error_phone'    => 'Die Telefonnummer ist ungültig.',
		'_lf_error_url'      => 'Bitte geben Sie eine gültige Web-Adresse ein.',
		'_lf_error_consent'  => 'Bitte stimmen Sie den Bedingungen zu.',
	];
}

/**
 * Returns the saved error message for a config post, or '' when the site
 * still uses a default.
 *
 * The admin form pre-fills each message with the current default and saves it
 * verbatim, so existing configs carry the old wording as if it were custom.
 * Values that match any previous default are treated as "not customised" so a
 * change of defaults reaches existing forms without a migration.
 *
 * @since 2.3.4
 */
function lf_get_custom_error_message( $post_id, $meta_key ) {
	$value = (string) get_post_meta( $post_id, $meta_key, true );
	if ( $value === '' ) {
		return '';
	}

	$legacy = [
		'_lf_error_required' => [ 'Dieses Feld ist erforderlich.' ],
		'_lf_error_email'    => [ 'Bitte geben Sie eine gültige E-Mail-Adresse ein.' ],
		'_lf_error_phone'    => [ 'Bitte geben Sie eine gültige Telefonnummer (nur Ziffern) ein.' ],
	];
	$defaults = lf_error_message_defaults();
	$known    = isset( $legacy[ $meta_key ] ) ? $legacy[ $meta_key ] : [];
	if ( isset( $defaults[ $meta_key ] ) ) {
		$known[] = $defaults[ $meta_key ];
	}

	return in_array( $value, $known, true ) ? '' : $value;
}

require_once LF_PLUGIN_DIR . 'includes/enqueue-scripts.php';
require_once LF_PLUGIN_DIR . 'includes/post-type.php';
require_once LF_PLUGIN_DIR . 'includes/meta-boxes.php';
require_once LF_PLUGIN_DIR . 'includes/gravity-forms-hooks.php';

/**
 * Initialize the GitHub Updater.
 *
 * Uses the Plugin Update Checker library to handle automatic updates
 * directly from the GitHub repository.
 *
 * @since 1.2.0
 */
if ( file_exists( LF_PLUGIN_DIR . 'vendor/plugin-update-checker/plugin-update-checker.php' ) ) {
    require_once LF_PLUGIN_DIR . 'vendor/plugin-update-checker/plugin-update-checker.php';

    // Verify the class exists to prevent fatal errors.
    if ( class_exists( 'YahnisElsts\PluginUpdateChecker\v5\PucFactory' ) ) {
        $myUpdateChecker = YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
            'https://github.com/felix-werner-landeseiten/Landeseiten-Gravity',
            __FILE__,
            'landeseiten-form'
        );

        // Use GitHub Releases (tagged versions) instead of branch tracking.
        // Only proper releases (not pre-releases) trigger auto-updates.
        $myUpdateChecker->getVcsApi()->enableReleaseAssets();

        // Optional: define LF_GITHUB_TOKEN in wp-config.php to avoid GitHub
        // API rate limit (60 req/hour unauthenticated vs 5000 with a token).
        if ( defined( 'LF_GITHUB_TOKEN' ) && LF_GITHUB_TOKEN ) {
            $myUpdateChecker->setAuthentication( LF_GITHUB_TOKEN );
        }
    }
}
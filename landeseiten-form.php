<?php
/**
 * Plugin Name:       Landeseiten Form for Gravity Forms
 * Description:       A premium wrapper for Gravity Forms to create multi-step, animated user experiences with modern styling.
 * Version:           2.0.0
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
define( 'LF_VERSION', '2.0.0' );

/**
 * Load core plugin files.
 *
 * The plugin is split into modules for better organization:
 * - enqueue-scripts.php: Handles CSS/JS assets.
 * - post-type.php: Registers the custom post type for configurations.
 * - meta-boxes.php: Handles the backend UI for settings.
 * - gravity-forms-hooks.php: Connects settings to Gravity Forms output.
 */
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

        // Set the branch that contains the stable release.
        $myUpdateChecker->setBranch( 'main' );
    }
}
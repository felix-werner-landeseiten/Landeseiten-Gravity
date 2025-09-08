<?php
/**
 * Plugin Name:       Landeseiten Form for Gravity Forms
 * Description:       A wrapper for Gravity Forms to create multi step animated user experience.
 * Version:           1.2.0
 * Author:            Landeseiten.de
 * Author URI:        https://landeseiten.de
 * License:           GPLv2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       landeseiten-form
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

// Define plugin constants for easy access.
define( 'LF_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'LF_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Include the necessary files.
require_once LF_PLUGIN_DIR . 'includes/enqueue-scripts.php';
require_once LF_PLUGIN_DIR . 'includes/post-type.php';
require_once LF_PLUGIN_DIR . 'includes/meta-boxes.php';
require_once LF_PLUGIN_DIR . 'includes/gravity-forms-hooks.php';

// --- Initialize the GitHub Updater ---
require_once LF_PLUGIN_DIR . 'vendor/plugin-update-checker/plugin-update-checker.php';

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$myUpdateChecker = PucFactory::buildUpdateChecker(
    'https://github.com/felix-werner-landeseiten/Landeseiten-Gravity', 
    __FILE__, 
    'landeseiten-form' 
);

// Optional: Set the branch that contains the stable release.
$myUpdateChecker->setBranch('main');
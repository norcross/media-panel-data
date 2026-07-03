<?php
/**
 * Plugin Name: Media Panel Data
 * Plugin URI:  https://github.com/norcross/media-panel-data
 * Description: Surfacing additional information about media items in the library.
 * Version:     0.0.1-dev
 * Author:      Andrew Norcross
 * Author URI:  https://github.com/norcross/
 * Text Domain: media-panel-data
 * Domain Path: /languages
 * License:     MIT
 * License URI: https://opensource.org/licenses/MIT
 *
 * @package MediaPanelData
 */

// Declare our namespace.
namespace Norcross\MediaPanelData;

// Call our CLI namespace.
use WP_CLI;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define our version.
define( __NAMESPACE__ . '\VERS', '0.0.1' );

// Plugin root file.
define( __NAMESPACE__ . '\FILE', __FILE__ );

// Define our file base.
define( __NAMESPACE__ . '\BASE', plugin_basename( __FILE__ ) );

// Plugin Folder URL and directory.
define( __NAMESPACE__ . '\URL', plugin_dir_url( __FILE__ ) );
define( __NAMESPACE__ . '\DIR', plugin_dir_path( __FILE__ ) );

// Set our assets URL constant.
define( __NAMESPACE__ . '\ASSETS_URL', URL . 'assets' );
define( __NAMESPACE__ . '\ASSETS_PATH', __DIR__ . '/assets' );

// Set the various prefixes for our actions and filters.
define( __NAMESPACE__ . '\HOOK_PREFIX', 'media_panel_data_' );

// Go and load our files.
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/display.php';

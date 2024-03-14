<?php
/**
 * Plugin Name: Roxane
 * Plugin URI: https://blog.charliemerland.me/
 * Description: CharlieMerland.me main plugin.
 * Version: 1.2.0
 * Author: Charlie Merland
 * Author URI: https://charliemerland.me/
 * License: GPLv2
 *
 * @since 1.0
 * @package Roxane
 */

// Make sure we don't expose any info if called directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ROXANE_VERSION', '1.2.0' );
define( 'ROXANE_BUILD',   false );
define( 'ROXANE_PATH',    trailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'ROXANE_URL',     trailingslashit( plugin_dir_url( __FILE__ ) ) );

define( 'ROXANE_ADMIN_SCRIPTS_URL',  trailingslashit( ROXANE_URL . 'admin/js' ) );
define( 'ROXANE_ADMIN_STYLES_URL',   trailingslashit( ROXANE_URL . 'admin/css' ) );
define( 'ROXANE_PUBLIC_SCRIPTS_URL', trailingslashit( ROXANE_URL . 'public/js' ) );
define( 'ROXANE_PUBLIC_STYLES_URL',  trailingslashit( ROXANE_URL . 'public/css' ) );

require_once ROXANE_PATH . 'app/traits/class-singleton.php';
require_once ROXANE_PATH . 'app/class-roxane.php';

/**
 * The main function responsible for returning the one true plugin Instance
 * to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $roxane = Roxane(); ?>
 *
 * @since 1.0
 *
 * @return Roxane\Roxane The one true Roxane Instance
 */
function roxane() {
  return Roxane\Roxane::instance();
}

roxane();

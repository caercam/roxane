<?php
/**
 * The file that defines the plugin dashboard class.
 *
 * @link https://charliemerland.me/
 * @package Roxane
 */

namespace roxane;

use roxane\support\facades\L10n;
use roxane\traits\Singleton;
use roxane\support\facades\Settings;

use function Roxane\support\helpers\settings;

/**
 * Define the dashboard class.
 *
 * @since 1.0
 * @author Charlie Merland <charlie@caercam.org>
 */
class Dashboard {

	use Singleton;

	/**
	 * @var string
	 */
	private $version;

	/**
	 * @var array
	 */
	private $archive_pages;

	/**
	 * Class constructor.
	 *
	 * @since 1.0
	 * @access public
	 */
	public function __construct() {

		$this->version = time();
	}

	/**
	 * Register frontend stuff.
	 * 
	 * @since 1.0
	 * @access public
	 */
	public function register() {

		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_styles' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

		add_action( 'admin_menu', [ $this, 'register_admin_pages' ] );
	}

	/**
	 * Enqueue admin-side styles.
	 *
	 * @since 1.0
	 * @access public
	 *
	 * @param string $hook_suffix The current admin page.
	 */
	public function enqueue_styles( $hook_suffix ) {

    	$this->register_styles();

		wp_enqueue_style( 'roxane-common' );
	}

	/**
	 * Enqueue admin-side styles.
	 *
	 * @since 1.0
	 * @access public
	 *
	 * @param string $hook_suffix The current admin page.
	 */
	public function enqueue_scripts( $hook_suffix ) {

    	$this->register_scripts();

		wp_enqueue_script( 'roxane-common' );

		$options = get_option( 'roxane_options', [] );
        wp_add_inline_script(
            'roxane-common',
            'window.roxaneOptions = ' . json_encode( [
				'tmdb_api_key' => get_option( 'tmdb_api_key' ),
                'locale' => get_bloginfo( 'language' )
            ] )
        );

        wp_set_script_translations( 'roxane-common', 'roxane', ROXANE_PATH . '/languages' );
	}

	/**
	 * Register admin-side scripts.
	 *
	 * @since 1.0
	 * @access private
	 */
	private function register_styles() {

		wp_register_style( 'roxane-common', ROXANE_URL . 'admin/css/common.css', [], $this->version, 'all' );
	}

	/**
	 * Register admin-side styles.
	 *
	 * @since 1.0
	 * @access private
	 */
	private function register_scripts() {

		$assets = require ROXANE_PATH . 'build/quick-create.asset.php';
		wp_register_script( 'roxane-quick-create', ROXANE_URL . 'build/quick-create.js', $assets['dependencies'], $this->version, 'all' );
		wp_register_script( 'roxane-common', ROXANE_URL . 'admin/js/common.js', [ 'jquery', /*'wp-element', 'wp-components',*/ 'roxane-quick-create' ], $this->version, 'all' );
	}

	/**
	 * Register admin-side styles.
	 *
	 * @since 1.0
	 * @access public
	 */
	public function register_admin_pages() {

		add_submenu_page( 'tools.php', 'TMDb', 'TMDb', 'manage_options', 'tmdb', [ $this, 'tmdb' ] );
	}

	/**
	 * TMDb page callback.
	 * 
	 * @since 1.0.0
	 * @access public
	 */
	public function tmdb() {

		$updated = false;
		if ( isset( $_POST['tmdb_api_key'] ) ) {
			check_admin_referer( 'update-tmdb-api-key' );
			update_option( 'tmdb_api_key', $_POST['tmdb_api_key'] );
			$updated = true;
		}

		require_once ROXANE_PATH . 'admin/templates/tmdb.php';
	}
}

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

		add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_block_editor_assets' ] );

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

		wp_register_script( 'roxane-common', ROXANE_URL . 'admin/js/common.js', [ 'jquery' ], $this->version, 'all' );
	}

	public function enqueue_block_editor_assets() {

		$this->register_block_editor_sidebar_plugin();

		wp_enqueue_script( 'series-episode-backdrop-plugin' );
	}

	public function register_block_editor_sidebar_plugin() {

		wp_register_script( 'series-episode-backdrop-plugin', ROXANE_URL . 'admin/js/plugins.min.js', [ 'wp-plugins', 'wp-edit-post', 'wp-element' ] );
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

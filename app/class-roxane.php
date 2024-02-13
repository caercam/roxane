<?php
/**
 * The file that defines the core plugin class.
 *
 * @link https://charliemerland.me/
 * @package Roxane
 */

namespace roxane;

use roxane\registrars\Permalinks;
use roxane\registrars\Taxonomies;
use roxane\registrars\Term_Meta;
use roxane\editors\Category_Editor;
use roxane\editors\Post_Editor;
use roxane\editors\Series_Editor;
use roxane\traits\Singleton;

/**
 * Define the main plugin class.
 *
 * @since 1.0
 * @author Charlie Merland <charlie@caercam.org>
 */
class Roxane {

	use Singleton;

	/**
	 * @var string
	 */
	private $version;

	/**
	 * @var string
	 */
	private $plugin_dir;

	/**
	 * @var string
	 */
	private $plugin_url;

	/**
	 * @var string
	 */
	private $include_dir;

	/**
	 * @var string
	 */
	private $include_url;

	/**
	 * Constructor.
	 *
	 * @since 1.0
	 * @access private
	 */
	private function __construct() {

		$this->version = ROXANE_VERSION;

		$this->plugin_dir = plugin_dir_path( __FILE__ );
		$this->plugin_url = plugin_dir_url( __FILE__ );

		$this->include_dir = trailingslashit( $this->plugin_dir . 'includes' );
		$this->include_url = trailingslashit( $this->plugin_url . 'includes' );

		$this->setup();
	}

	/**
	 * Initialize.
	 *
	 * @since 1.0
	 * @access private
	 */
	private function setup() {

		// Let's get this party started.
		add_action( 'plugins_loaded', [ $this, 'run' ] );

		// i18n
		add_action( 'roxane/run', [ &$this, 'translate' ] );

		// Let's get to work.
		add_action( 'roxane/run', [ $this, 'storyboard' ] );
		add_action( 'roxane/run', [ $this, 'rehearsal' ] );
		add_action( 'roxane/run', [ $this, 'background' ] );
		add_action( 'roxane/run', [ $this, 'foreground' ] );

		// Plugin activation hook.
		register_activation_hook( ROXANE_PATH, [ $this, 'activate' ] );
	}

	/**
	 * Load plugin translations.
	 *
	 * @since 1.0
	 * @access public
	 */
	public function translate() {

		$plugin_path = dirname( plugin_basename( __FILE__ ) ) . '/languages/';

		// Load main translations.
		load_plugin_textdomain( 'roxane', false, $plugin_path );
	}

	/**
	 * Prepare plugin.
	 *
	 * @since 1.0
	 * @access public
	 */
	public function storyboard() {

		// Load traits.
		require_once ROXANE_PATH . 'app/traits/class-singleton.php';

		// Load registrars.
		require_once ROXANE_PATH . 'app/registrars/class-taxonomies.php';
		require_once ROXANE_PATH . 'app/registrars/class-term-meta.php';
		require_once ROXANE_PATH . 'app/registrars/class-permalinks.php';

		// Load frontend.
		require_once ROXANE_PATH . 'app/class-frontend.php';

		// Load dashboard.
		if ( is_admin() ) {
			require_once ROXANE_PATH . 'admin/editors/class-category-editor.php';
			require_once ROXANE_PATH . 'admin/editors/class-post-editor.php';
			require_once ROXANE_PATH . 'admin/editors/class-series-editor.php';
			require_once ROXANE_PATH . 'app/class-dashboard.php';
		}
	}

	/**
	 * Load internal features.
	 *
	 * @since 1.0
	 * @access public
	 */
	public function rehearsal() {

		$taxonomies = Taxonomies::instance();
		add_action( 'init', [ $taxonomies, 'register' ] );
		add_filter( 'term_link', [ $taxonomies, 'term_link' ], 1, 2 );

		$term_meta = Term_Meta::instance();
		add_action( 'saved_term', [ $term_meta, 'save_meta_input' ], 10, 3 );

		$permalinks = Permalinks::instance();
		add_filter( 'series_rewrite_rules', [ $permalinks, 'series_rewrite_rules' ], 1, 1 );
	}

	/**
	 * Load admin features.
	 *
	 * @since 1.0
	 * @access public
	 */
	public function background() {

		if ( ! is_admin() ) {
			return false;
		}

		$dashboard = Dashboard::instance();
		add_action( 'init', [ $dashboard, 'register' ] );

		$category_editor = new Category_Editor;
		add_action( 'category_edit_form_fields', [ $category_editor, 'edit_category_image' ] );
		add_action( 'category_add_form_fields',  [ $category_editor, 'add_category_image' ] );
		add_action( 'edit_category',             [ $category_editor, 'save_category_image' ] );
		add_action( 'create_category',           [ $category_editor, 'save_category_image' ] );

		add_filter( 'manage_edit-category_columns',  [ $category_editor, 'category_columns' ] );
		add_filter( 'manage_category_custom_column', [ $category_editor, 'category_column' ], 10, 3 );

		$series_editor = new Series_Editor;
		add_action( 'series_edit_form_fields', [ $series_editor, 'edit_series_image' ] );
		add_action( 'series_add_form_fields',  [ $series_editor, 'add_series_image' ] );
		add_action( 'edit_series',             [ $series_editor, 'save_series_image' ] );
		add_action( 'create_series',           [ $series_editor, 'save_series_image' ] );
		add_action( 'series_edit_form_fields', [ $series_editor, 'edit_series_fields' ] );
		add_action( 'series_add_form_fields',  [ $series_editor, 'edit_series_fields' ] );

		add_filter( 'manage_edit-series_columns',  [ $series_editor, 'series_columns' ] );
		add_filter( 'manage_series_custom_column', [ $series_editor, 'series_column' ], 10, 3 );

		$post_editor = new Post_Editor;
		add_filter( 'manage_posts_columns',       [ $post_editor, 'post_columns' ] );
		add_filter( 'manage_posts_custom_column', [ $post_editor, 'post_column' ], 10, 3 );
	}

	/**
	 * Load public features.
	 *
	 * @since 1.0
	 * @access public
	 */
	public function foreground() {

		$frontend = Frontend::instance();
		add_action( 'init', [ $frontend, 'register' ] );
	}

	/**
	 * Handle inital installation and upgrading of the plugin.
	 *
	 * @since 1.0
	 * @access public
	 */
	public function activate() {

		$db_version = get_option( 'roxane_version' );
		if ( version_compare( $db_version, $this->version ) ) {
			update_option( 'roxane_version', $this->version );
		}
	}

	/**
	 * Now you wouldn't believe me if I told you, but I could run
	 * like the wind blows.
	 *
	 * @since 1.0
	 * @access public
	 */
	public function run() {

		/**
		 * Let's get this party started.
		 *
		 * @since 1.0
		 *
		 * @param Roxane\Library $this Plugin class instance, passed by reference.
		 */
		do_action_ref_array( 'roxane/run', [ $this ] );
	}
}

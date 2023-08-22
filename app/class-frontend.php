<?php
/**
 * The file that defines the plugin frontend class.
 *
 * @link https://charliemerland.me/
 * @package Roxane
 */

namespace roxane;

use roxane\traits\Singleton;

/**
 * Define the frontend class.
 *
 * @since 1.0
 * @author Charlie Merland <charlie@caercam.org>
 */
class Frontend {

	use Singleton;

	/**
	 * @var string
	 */
	private $version;

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

		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_styles' ] );
	}

  /**
	 * Enqueue public-side styles.
	 *
	 * @since 1.0
	 * @access public
	 */
	public function enqueue_styles() {

    $this->register_styles();

		wp_enqueue_style( 'roxane' );
	}

	/**
	 * Enqueue public-side scripts.
	 *
	 * @since 1.0
	 * @access public
	 */
	public function enqueue_scripts() {

    $this->register_scripts();

		wp_enqueue_script( 'roxane' );
	}

	/**
	 * Register public-side scripts.
	 *
	 * @since 1.0
	 * @access private
	 */
	private function register_styles() {

		wp_register_style( 'roxane', ROXANE_PUBLIC_STYLES_URL . 'app.min.css', [], $this->version, 'all' );
	}

	/**
	 * Register public-side styles.
	 *
	 * @since 1.0
	 * @access private
	 */
	private function register_scripts() {

		wp_register_script( 'roxane', ROXANE_PUBLIC_SCRIPTS_URL . 'app.min.js', [], $this->version, true );
	}
}

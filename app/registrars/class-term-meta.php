<?php
/**
 * The file that defines the plugin term meta class.
 *
 * @link https://charliemerland.me/
 * @package Roxane
 */

namespace roxane\registrars;

use roxane\traits\Singleton;

/**
 * Define the term meta class.
 *
 * @since 1.0.0
 * @author Charlie Merland <charlie@caercam.org>
 */
class Term_Meta {

	use Singleton;

  /**
	 * Static instance.
	 *
	 * @since 1.0.0
	 * @static
	 * @access private
	 *
	 * @var Term_Meta
	 */
	private static $_instance = null;

	/**
	 * Term Meta parameters.
	 *
	 * @since 1.0.0
	 * @static
	 * @access private
	 *
	 * @var array
	 */
	private $term_meta = [];

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {

		$this->term_meta = [
			'series_tmdb_id' => [
				'taxonomy' => 'series',
				'type' => 'string',
				'single' => true,
			],
		];
	}

	/**
	 * Register custom Term Meta.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function register() {

		foreach ( $this->term_meta as $slug => $args ) {
			$taxonomies = (array) $args['taxonomy'];
			foreach ( $taxonomies as $taxonomy ) {
				register_term_meta( $taxonomy, $slug, $args );
			}
		}
	}

	/**
	 * Save registered metadata.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param  WP_Term
	 * @return void
	 */
	public function save_meta_input( $term_id, $tt_id, $taxonomy ) {

		if ( ! isset( $_POST['meta_input'] ) ) {
			return false;
		}

    	foreach ( $_POST['meta_input'] as $key => $value ) {
			if ( isset( $this->term_meta[ $key ] ) ) {
				$args = $this->term_meta[ $key ];
				if ( isset( $args['taxonomy'] ) && ! in_array( $taxonomy, (array) $args['taxonomy'], true ) ) {
					continue;
				}
				if ( ! empty( $args['prepare_callback'] ) && is_callable( $args['prepare_callback'] ) ) {
					$value = call_user_func_array( $args['prepare_callback'], array( $term_id, $key, $value ) );
				}
				update_term_meta( $term_id, $key, $value );
			}
		}
	}
}
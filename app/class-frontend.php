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

		add_filter( 'the_content', [ $this, 'add_series_links' ] );

		add_action( 'wp_after_insert_post', [ $this, 'set_series_episodes_backdrops' ], 1, 3 );
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

		wp_register_script( 'roxane', ROXANE_ADMIN_SCRIPTS_URL . 'app.min.js', [], $this->version, true );
	}

	/**
	 * Replace series status posts title and season with links
	 * to the corresponding terms, if any.
	 * 
	 * @since 1.1
	 * @access public
	 * 
	 * @param  string $content
	 * @return string
	 */
	public function add_series_links( $content ) {

		$preg = preg_match_all( '/A vu <em>(.*?)<\/em>, (saison [0-9]+), (épisode [0-9]+)/m', $content, $matches );
		if ( ! $preg ) {
			return $content;
		}

		$matches = array_merge( ...array_values( $matches ) );
		if ( empty( $matches ) || 4 !== count( $matches ) ) {
			return $content;
		}

		$series = get_terms( [
			'name' => $matches[1],
			'taxonomy' => 'series',
		] );

		if ( ! is_array( $series ) ) {
			return $content;
		}

		$series = array_shift( $series );

		$season = get_terms( [
			'name' => "$matches[1] − $matches[2]",
			'parent' => $series->term_id,
			'taxonomy' => 'series',
		] );

		if ( ! is_array( $season ) ) {
			return $content;
		}

		$season = array_shift( $season );

		$content = str_replace(
			[
				$matches[1],
				$matches[2],
			],
			[
				sprintf(
					'<a href="%s">%s</a>',
					get_term_link( $series, 'series' ),
					$matches[1]
				),
				sprintf(
					'<a href="%s">%s</a>',
					get_term_link( $season, 'series' ),
					$matches[2]
				),
			],
			$content
		);

		return $content;
	}

	/**
	 * Set series episodes featured image using TMDb.
	 * 
	 * @since 1.1
	 * @access public
	 * 
	 * @param int $post_id
	 * @param WP_Post $post
	 * @param bool $update
	 */
	public function set_series_episodes_backdrops( $post_id, $post, $update ) {

		if ( $update ) {
			return false;
		}

		if ( 'revision' === $post->post_type ) {
			$post_id = $post->post_parent;
		}

		if ( 'status' !== get_post_format( $post ) ) {
			set_post_format( $post_id, 'status' );
		}

		if ( ! has_term( 'Séries', 'category', $post ) ) {
			$term = get_term_by( 'name', 'Séries', 'category' );
			if ( $term instanceof \WP_Term ) {
				wp_set_post_terms( $post_id, $term->term_id, 'category' );
			}
		}

		$preg = preg_match_all( '/(.*?) ([0-9]+)(×|x)([0-9]+)/m', $post->post_title, $matches );
    if ( ! $preg ) {
			return false;
    }

		$matches = array_merge( ...array_values( $matches ) );
    if ( empty( $matches ) ) {
			return false;
    }

    $series = get_term_by( 'name', $matches[1], 'series' );
    if ( ! $series ) {
			return false;
    }

    $seasons = get_terms( [
			'name' => "{$matches[1]} − Saison {$matches[2]}",
			'parent' => $series->term_id,
			'taxonomy' => 'series',
    ] );

    if ( ! is_array( $seasons ) ) {
			return false;
    }

    $season = array_shift( $seasons );
    if ( ! $season ) {
			return false;
		}

		if ( ! has_term( $season->term_id, 'series', $post ) ) {
			wp_set_post_terms( $post_id, $season->term_id, 'series' );
		}

		$tmdb_id = get_term_meta( $series->term_id, 'series_tmdb_id', true );
		if ( empty( $tmdb_id ) ) {
			return false;
		}

		$api_key = get_option( 'tmdb_api_key', '' );
		if ( empty( $api_key ) ) {
			return false;
		}

		$response = wp_remote_get( "https://api.themoviedb.org/3/tv/{$tmdb_id}/season/{$matches[2]}/episode/{$matches[4]}?api_key={$api_key}" );
    if ( 200 !== $response['response']['code'] ?? false ) {
      return false;
    }

    $data = json_decode( $response['body'] );
		if ( empty( $data->still_path ) ) {
			return false;
		}

		$url = "https://image.tmdb.org/t/p/original{$data->still_path}";

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$tmp = download_url( $url );
		if ( is_wp_error( $tmp ) ) {
			return false;
		}

		$image = imagecreatefromjpeg( $tmp );

		$scaled = imagescale( $image, 960, 540 );
		if ( false !== $scaled ) {
			imagejpeg( $scaled, $tmp );

			$cropped = imagecrop( $scaled, [
				'x' => 0,
				'y' => 90,
				'width' => 960,
				'height' => 360,
			] );

			if ( false !== $cropped ) {
					imagejpeg( $cropped, $tmp );
					imagedestroy( $cropped );
			}

			imagedestroy( $scaled );
		}

		imagedestroy( $image );

		$args = array(
			'name' => basename( $url ),
			'tmp_name' => $tmp,
		);

		$description = "Aperçu de {$series->name}, saison {$matches[2]}, épisode {$matches[4]}";

		$post_data = [
			'post_date' => $post->post_date,
			'post_title' => $description,
			'post_content' => $description,
			'post_excerpt' => $description,
		];

		$attachment_id = media_handle_sideload( $args, $post_id, $description, $post_data );

		wp_delete_file( $tmp );

		if ( is_wp_error( $attachment_id ) ) {
			return false;
		}

		update_post_meta( $attachment_id, '_wp_attachment_image_alt', $description );

		$src = wp_get_original_image_path( $attachment_id );
		if ( ! $src ) {
			return false;
		}

		set_post_thumbnail( $post_id, $attachment_id );
	}
}

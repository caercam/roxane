<?php
/**
 * REST API endpoints for the quick-create feature.
 *
 * @link https://charliemerland.me/
 * @package Roxane
 */

namespace roxane;

use roxane\traits\Singleton;

/**
 * Registers custom REST API routes for quick-create.
 *
 * @since 1.4.0
 * @author Charlie Merland <charlie@caercam.org>
 */
class REST_API {

	use Singleton;

	/**
	 * Register REST routes.
	 *
	 * @since 1.4.0
	 * @access public
	 */
	public function register() {

		add_action( 'rest_api_init', [ $this, 'register_routes' ] );
	}

	/**
	 * Register custom REST routes.
	 *
	 * @since 1.4.0
	 * @access public
	 */
	public function register_routes() {

		register_rest_route( 'roxane/v1', '/upload-movie-backdrop', [
			'methods'             => 'POST',
			'callback'            => [ $this, 'upload_movie_backdrop' ],
			'permission_callback' => function () {
				return current_user_can( 'upload_files' );
			},
			'args' => [
				'post_id' => [
					'required' => true,
					'type'     => 'integer',
				],
			],
		] );
	}

	/**
	 * Upload a cropped movie backdrop and set it as featured image.
	 *
	 * Expects a multipart form with:
	 * - post_id: the post to attach the image to
	 * - image:   the JPEG file (cropped backdrop)
	 * - title:   movie title for image description
	 *
	 * @since 1.4.0
	 * @access public
	 *
	 * @param  \WP_REST_Request $request
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function upload_movie_backdrop( $request ) {

		$post_id = absint( $request->get_param( 'post_id' ) );
		$title   = sanitize_text_field( $request->get_param( 'title' ) ?? '' );
		$files   = $request->get_file_params();

		if ( empty( $files['image'] ) ) {
			return new \WP_Error( 'no_image', 'No image file provided.', [ 'status' => 400 ] );
		}

		$post = get_post( $post_id );
		if ( ! $post ) {
			return new \WP_Error( 'invalid_post', 'Post not found.', [ 'status' => 404 ] );
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$description = ! empty( $title ) ? $title : $post->post_title;

		$post_data = [
			'post_date'    => $post->post_date,
			'post_title'   => $description,
			'post_content' => $description,
			'post_excerpt' => $description,
		];

		// Use WordPress' built-in media upload handler.
		$_FILES['image'] = $files['image'];
		$attachment_id = media_handle_upload( 'image', $post_id, $post_data );

		if ( is_wp_error( $attachment_id ) ) {
			return $attachment_id;
		}

		update_post_meta( $attachment_id, '_wp_attachment_image_alt', $description );
		set_post_thumbnail( $post_id, $attachment_id );

		return new \WP_REST_Response( [
			'success'       => true,
			'attachment_id' => $attachment_id,
		], 200 );
	}
}

<?php
/**
 * The file that defines the plugin post editor class.
 *
 * @link https://charliemerland.me/
 * @package Roxane
 */

namespace roxane\editors;

use roxane\traits\Singleton;

/**
 * Define the post editor class.
 *
 * @since 1.0.0
 * @author Charlie Merland <charlie@caercam.org>
 */
class Post_Editor {

	use Singleton;

  /**
	 * Thumbnail column added to post admin.
	 * 
	 * @since 1.0 
	 * 
	 * @param  mixed $columns
	 * @return void
	 */
	public function post_columns( $columns ) {

		$columns['thumbnail'] = '<span class="dashicons dashicons-format-image"></span>';
	
		return $columns;
	}
	
	/**
	 * Thumbnail column value added to post admin.
	 * 
	 * @since 1.0
	 * 
	 * @param  mixed $columns
	 * @param  mixed $column
	 * @param  mixed $id
	 * @return void
	 */
	public function post_column( $column_name, $post_id ) {

		if ( 'thumbnail' !== $column_name ) {
			return false;
		}

		echo get_the_post_thumbnail( $post_id, 'thumb' );
	}

}
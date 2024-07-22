<?php
/**
 * The file that defines the plugin series editor class.
 *
 * @link https://charliemerland.me/
 * @package Roxane
 */

namespace roxane\editors;

use roxane\traits\Singleton;
use WP_Term;

/**
 * Define the series editor class.
 *
 * @since 1.0.0
 * @author Charlie Merland <charlie@caercam.org>
 */
class Series_Editor {

	use Singleton;

	/**
	 * Add image field in series add form.
	 * 
	 * @since 1.0
   * @access public
	 * 
	 * @return void
	 */
	public function add_series_image() {

		wp_enqueue_media();

		$image_url = ROXANE_URL . 'public/images/default.jpg';
?>
		<script>cmme = { default_image: '<?php echo ROXANE_URL . 'public/images/default.jpg' ?>' };</script>
		<div class="form-field term-image-wrap">
			<label for="term_image"><?php _e( 'Image' ) ?></label>
			<img class="term-image" src="<?php echo esc_url( $image_url ) ?>"/>
			<input type="hidden" name="series_image" id="term_image" value="" />
			<button type="button" class="upload-term-image-button button">Sélectionner une image</button>
		</div>
<?php
	}

	/**
	 * Add image field in series edit form.
	 * 
	 * @since 1.0
	 * 
	 * @param  object $series Term object
	 * @return void
	 */
	public function edit_series_image( $series ) {

		wp_enqueue_media();

		$attachment_id  = get_term_meta( $series->term_id, 'series_image', true );
		$attachment_url = $this->get_image( $series->term_id, 'large' );

		$tmdb_id = get_term_meta( $series->term_id, 'series_tmdb_id', $single = true );
?>
		<script>cmme = { default_image: '<?php echo ROXANE_URL . 'public/images/default.jpg' ?>' };</script>
		<tr class="form-field form-required term-image-wrap">
			<th scope="row"><label for="term_image"><?php _e( 'Image' ) ?></label></th>
			<td>
				<img class="term-image" src="<?php echo esc_url( $attachment_url ) ?>"/>
				<input type="hidden" name="series_image" id="term_image" value="<?php echo esc_attr( $attachment_id ) ?>" />
				<button type="button" class="upload-term-image-button button">Sélectionner une image</button>
				<button type="button" class="remove-term-image-button button">Supprimer l’image</button>
				<p class="description"></p>
			</td>
		</tr>
<?php
	}

  /**
	 * Thumbnail column added to series admin.
	 * 
	 * @since 1.0 
   * @access public
	 * 
	 * @param  mixed $columns
	 * @return void
	 */
	public function series_columns( $columns ) {

		$columns['tmdb_id'] = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" height="20" width="20"><!--!Font Awesome Free 6.6.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M185 23l-17-17L134.1 40l17 17 39 39L0 96 0 512l512 0 0-416L321.9 96l39-39 17-17L344 6.1 327 23l-71 71L185 23zM424 232a24 24 0 1 1 48 0 24 24 0 1 1 -48 0zm24 56a24 24 0 1 1 0 48 24 24 0 1 1 0-48zM64 160l320 0 0 288L64 448l0-288z" fill="#2c3338"/></svg>';
		$columns['thumbnail'] = '<span class="dashicons dashicons-format-image"></span>';
	
		return $columns;
	}
	
	/**
	 * Thumbnail column value added to series admin.
	 * 
	 * @since 1.0
   * @access public
	 * 
	 * @param  mixed $columns
	 * @param  mixed $column
	 * @param  mixed $id
	 * 
	 * @return void
	 */
	public function series_column( $columns, $column, $id ) {

		if ( $column == 'thumbnail' ) {
			$columns = '<span><img src="' . esc_url( $this->get_image( $id, 'medium' ) ) . '" alt="' . __( 'Thumbnail' ) . '" class="wp-post-image" /></span>';
		} elseif ( $column == 'tmdb_id' ) {
			$tmdb_id = get_term_meta( $id, 'series_tmdb_id', $single = true );
			$columns = '<a href="' . esc_url( "https://www.themoviedb.org/tv/{$tmdb_id}" ) . '" target="_blank">' . esc_html( $tmdb_id ) . '</a>';
		}

		return $columns;
	}

	/**
	 * Retrieve series image URL.
	 * 
	 * @since 1.0
   * @access private
	 * 
	 * @param  int    $term_id Term ID
	 * @param  string $size Image size
	 * 
	 * @return string
	 */
	private function get_image( $term_id, $size = 'medium' ) {

		$attachment_id  = get_term_meta( $term_id, 'series_image', $single = true );
		$attachment_url = wp_get_attachment_image_url( $attachment_id, $size );
		if ( ! $attachment_url ) {
			$attachment_url = ROXANE_URL . 'public/images/default.jpg';
		}

		return $attachment_url;
	}

	/**
	 * Display additional fields for the series terms.
	 * 
	 * @since 1.1
	 * @access public
	 * 
	 * @param WP_Term $series
	 */
	public function edit_series_fields( $series ) {

		if ( $series instanceof WP_Term ) {
	    $tmdb_id = get_term_meta( $series->term_id, 'series_tmdb_id', $single = true );
		}

    require ROXANE_PATH . 'admin/templates/series-editor.php';
  }

	/**
	 * Save the series image's attachment ID.
	 * 
	 * @since 1.1
	 * @access public
	 * 
	 * @param int $term_id Term ID
	 */
	public function save_series_image( $term_id ) {

		if ( isset( $_POST['series_image'] ) ) {
			if ( false !== strpos( $_POST['series_image'], '.jpg' ) ) {

				$series = get_term( $term_id, 'series' );

				require_once ABSPATH . 'wp-admin/includes/file.php';
				require_once ABSPATH . 'wp-admin/includes/media.php';
				require_once ABSPATH . 'wp-admin/includes/image.php';
		
				$url = "https://image.tmdb.org/t/p/original{$_POST['series_image']}";

				$tmp = download_url( $url );
				if ( is_wp_error( $tmp ) ) {
					return false;
				}

				$args = array(
					'name' => basename( $url ),
					'tmp_name' => $tmp,
				);

				$description = "Aperçu de {$series->name}";

				$post_data = [
					'post_title' => $description,
					'post_content' => $description,
					'post_excerpt' => $description,
				];

				$attachment_id = media_handle_sideload( $args, $post_id, $description, $post_data );
				if ( is_wp_error( $attachment_id ) ) {
					return false;
				}

				update_term_meta( $term_id, 'series_image', $attachment_id );
			} else {
				update_term_meta( $term_id, 'series_image', intval( $_POST['series_image'] ) );
			}
		}
	}
}
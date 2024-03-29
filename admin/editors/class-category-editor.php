<?php
/**
 * The file that defines the plugin category editor class.
 *
 * @link https://charliemerland.me/
 * @package Roxane
 */

namespace roxane\editors;

use roxane\traits\Singleton;

/**
 * Define the category editor class.
 *
 * @since 1.0.0
 * @author Charlie Merland <charlie@caercam.org>
 */
class Category_Editor {

	use Singleton;

	/**
	 * Add image field in category add form.
	 * 
	 * @since 1.0
   * @access public
	 * 
	 * @return void
	 */
	public function add_category_image() {

		wp_enqueue_media();

		$image_url = ROXANE_URL . 'public/images/default.jpg';
?>
		<script>cmme = { default_image: '<?php echo ROXANE_URL . 'public/images/default.jpg' ?>' };</script>
		<div class="form-field term-image-wrap">
			<label for="term_image"><?php _e( 'Image' ) ?></label>
			<img class="term-image" src="<?php echo esc_url( $image_url ) ?>"/>
			<input type="hidden" name="category_image" id="term_image" value="" />
			<button class="upload-term-image-button button">Sélectionner une image</button>
		</div>
<?php
	}

	/**
	 * Add image field in category edit form.
	 * 
	 * @since 1.0
	 * 
	 * @param  object $category Term object
	 * @return void
	 */
	public function edit_category_image( $category ) {

		wp_enqueue_media();

		$attachment_id  = get_term_meta( $category->term_id, 'category_image', $single = true );
		$attachment_url = self::get_image( $category->term_id, 'large' );
?>
		<script>cmme = { default_image: '<?php echo ROXANE_URL . 'public/images/default.jpg' ?>' };</script>
		<tr class="form-field form-required term-image-wrap">
			<th scope="row"><label for="term_image"><?php _e( 'Image' ) ?></label></th>
			<td>
				<img class="term-image" src="<?php echo esc_url( $attachment_url ) ?>"/>
				<input type="hidden" name="category_image" id="term_image" value="<?php echo esc_attr( $attachment_id ) ?>" />
				<button class="upload-term-image-button button">Sélectionner une image</button>
				<button class="remove-term-image-button button">Supprimer l’image</button>
				<p class="description"></p>
			</td>
		</tr>
<?php
	}

  /**
	 * Thumbnail column added to category admin.
	 * 
	 * @since 1.0 
   * @access public
	 * 
	 * @param  mixed $columns
	 * @return void
	 */
	public function category_columns( $columns ) {

		$columns['thumbnail'] = '<span class="dashicons dashicons-format-image"></span>';
	
		return $columns;
	}
	
	/**
	 * Thumbnail column value added to category admin.
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
	public function category_column( $columns, $column, $id ) {

		if ( $column == 'thumbnail' ) {
			$columns = '<span><img src="' . esc_url( $this->get_image( $id, 'medium' ) ) . '" alt="' . __( 'Thumbnail' ) . '" class="wp-post-image" /></span>';
		}

		return $columns;
	}

	/**
	 * Retrieve category image URL.
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

		$attachment_id  = get_term_meta( $term_id, 'category_image', true );
		$attachment_url = wp_get_attachment_image_url( $attachment_id, $size );
		if ( ! $attachment_url ) {
			$attachment_url = ROXANE_URL . 'public/images/default.jpg';
		}

		return $attachment_url;
	}

	/**
	 * Display additional fields for the category terms.
	 * 
	 * @since 1.1
	 * @access public
	 * 
	 * @param WP_Term $category
	 */
	public function edit_category_fields( $category = null ) {

		require ROXANE_PATH . 'admin/templates/category-editor.php';
  }

	/**
	 * Save the category image's attachment ID.
	 * 
	 * @since 1.1
	 * @access public
	 * 
	 * @param int $term_id Term ID
	 */
	public function save_category_image( $term_id ) {

		if ( isset( $_POST['category_image'] ) ) {
			update_term_meta( $term_id, 'category_image', intval( $_POST['category_image'] ) );
		}
	}
}
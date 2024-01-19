<?php
/**
 * The file that defines the plugin taxonomies class.
 *
 * @link https://charliemerland.me/
 * @package Roxane
 */

namespace roxane\registrars;

use roxane\traits\Singleton;

/**
 * Define the taxonomies class.
 *
 * @since 1.0.0
 * @author Charlie Merland <charlie@ekole.fr>
 */
class Taxonomies {

	use Singleton;

  /**
	 * Taxonomies parameters.
	 *
	 * @since 1.0.0
	 * @static
	 * @access private
	 *
	 * @var array
	 */
	private $taxonomies = [];

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {

    $this->taxonomies = [
      'rating' => [
				'label' => 'Notes',
				'labels' => [
					'name' => 'Notes',
					'singular_name' => 'Notes',
					'search_items' => 'Rechercher des notes',
					'popular_items' => NULL,
					'all_items' => 'Notes',
					'parent_item' => 'Note parente',
					'parent_item_colon' => 'Note parente :',
					'edit_item' => 'Modifier la note',
					'view_item' => 'Voir la note',
					'update_item' => 'Mettre à jour la note',
					'add_new_item' => 'Ajouter une nouvelle note',
					'new_item_name' => 'Nom de la nouvelle note',
					'separate_items_with_commas' => NULL,
					'add_or_remove_items' => NULL,
					'choose_from_most_used' => NULL,
					'not_found' => 'Aucune note trouvée.',
					'no_terms' => 'Aucune note',
					'filter_by_item' => 'Filtrer par note',
					'items_list_navigation' => 'Navigation de la liste des notes',
					'items_list' => 'Liste des notes',
					'most_used' => 'Plus utilisées',
					'back_to_items' => '&larr; Aller aux notes',
					'item_link' => 'Lien de la note',
					'item_link_description' => 'Un lien vers une note.',
					'menu_name' => 'Notes',
					'name_admin_bar' => 'Notes',
					'archives' => 'Notes',
				],
				'post_types' => [ 'post' ],
				'description' => '',
				'public' => true,
				'publicly_queryable' => true,
				'hierarchical' => true,
				'show_ui' => true,
				'show_in_menu' => true,
				'show_in_nav_menus' => true,
				'show_tagcloud' => true,
				'show_in_quick_edit' => true,
				'show_admin_column' => true,
				'rewrite' => 'note',
				'query_var' => false,
				'show_in_rest' => true,
				'capabilities' => [
					'manage_terms' => 'manage_options',
					'edit_terms' => 'manage_options',
				],
			],
    ];
  }

	/**
	 * Register custom taxonomies.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function register() {

		foreach ( $this->taxonomies as $slug => $args ) {
			register_taxonomy( $slug, $args['post_types'], $args );
		}
	}
}
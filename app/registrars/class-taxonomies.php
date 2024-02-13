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
 * @author Charlie Merland <charlie@caercam.org>
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
      'series' => [
				'label' => 'Séries',
				'labels' => [
					'name' => 'Séries',
					'singular_name' => 'Séries',
					'search_items' => 'Rechercher des séries',
					'popular_items' => NULL,
					'all_items' => 'Séries',
					'parent_item' => 'Série parente',
					'parent_item_colon' => 'Série parente :',
					'edit_item' => 'Modifier la série',
					'view_item' => 'Voir la série',
					'update_item' => 'Mettre à jour la série',
					'add_new_item' => 'Ajouter une nouvelle série',
					'new_item_name' => 'Nom de la nouvelle série',
					'separate_items_with_commas' => NULL,
					'add_or_remove_items' => NULL,
					'choose_from_most_used' => NULL,
					'not_found' => 'Aucune série trouvée.',
					'no_terms' => 'Aucune série',
					'filter_by_item' => 'Filtrer par série',
					'items_list_navigation' => 'Navigation de la liste des séries',
					'items_list' => 'Liste des séries',
					'most_used' => 'Plus utilisées',
					'back_to_items' => '&larr; Aller aux séries',
					'item_link' => 'Lien de la série',
					'item_link_description' => 'Un lien vers une série.',
					'menu_name' => 'Séries',
					'name_admin_bar' => 'Séries',
					'archives' => 'Séries',
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
				'rewrite' => [
					'slug' => 'series/%parent%',
				],
				'query_var' => true,
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

	/**
	 * Filter series term link.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $termlink
	 * @param WP_Term $term
	 * @return string
	 */
	public function term_link( $termlink, $term ) {

		if ( 'series' !== $term->taxonomy ) {
			return $termlink;
		}

		if ( 'series' !== $term->taxonomy || ! $term->parent ) {
			$termlink = str_replace( '%parent%/', '', $termlink );
			return $termlink;
		}

		$parent = get_term_by( 'term_id', $term->parent, 'series' );
		if ( ! $parent ) {
			$termlink = str_replace( '%parent%', '', $termlink );
			return $termlink;
		}

		$termlink = str_replace( '%parent%', $parent->slug, $termlink );
		$termlink = str_replace( "/{$parent->slug}-", '/', $termlink );

		return $termlink;
	}
}
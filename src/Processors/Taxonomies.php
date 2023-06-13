<?php
namespace MetaBox\ACF\Processors;
use WP_Query;

class Taxonomies extends Base {
	protected function get_items() {
		// Process all post types at once.
		if ( $_SESSION['processed'] ) {
			return [];
		}

		$query = new WP_Query( [
			'post_type'              => 'acf-taxonomy',
			'post_status'            => 'any',
			'posts_per_page'         => -1,
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		] );

		return $query->posts;
	}

	protected function migrate_item() {
		$this->migrate_taxonomies();

		$this->disable_post();
	}

	private function migrate_taxonomies() {
		$taxonomy = unserialize( $this->item->post_content );

		$args   = [
			'labels'             => $taxonomy['labels'],
			'description'        => $taxonomy['description'],
			'supports'           => $taxonomy['supports'],
			'public'             => $taxonomy['public'],
			'publicly_queryable' => $taxonomy['publicly_queryable'],
			'show_ui'            => $taxonomy['show_ui'],
			'show_in_menu'       => $taxonomy['show_in_menu'],
			'show_in_rest'       => $taxonomy['show_in_rest'],
			'menu_icon'          => $taxonomy['menu_icon'] ?? 'dashicons-editor-justify',
			'rewrite'            => $taxonomy['rewrite'],
			'query_var'          => $taxonomy['query_var'],
			'has_archive'        => $taxonomy['has_archive'],
			'hierarchical'       => $taxonomy['hierarchical'],
			'menu_position'      => $taxonomy['menu_position'] ?? 200,
			'map_meta_cap'       => true,
			'capabilities'       => [
				// Meta capabilities.
				'edit_post'              => 'edit_mb_taxonomy',
				'read_post'              => 'read_mb_taxonomy',
				'delete_post'            => 'delete_mb_taxonomy',

				// Primitive capabilities used outside of map_meta_cap():
				'edit_posts'             => 'manage_options',
				'edit_others_posts'      => 'manage_options',
				'publish_posts'          => 'manage_options',
				'read_private_posts'     => 'manage_options',

				// Primitive capabilities used within map_meta_cap():
				'read'                   => 'read',
				'delete_posts'           => 'manage_options',
				'delete_private_posts'   => 'manage_options',
				'delete_published_posts' => 'manage_options',
				'delete_others_posts'    => 'manage_options',
				'edit_private_posts'     => 'manage_options',
				'edit_published_posts'   => 'manage_options',
				'create_posts'           => 'manage_options',
			],
		];

		register_post_type( 'mb-taxonomy', $args );

		$types = empty( $taxonomy['object_type'] ) ? [] : $taxonomy['object_type'];
		register_taxonomy( $taxonomy['taxonomy'], $types, $args );
	}

	protected function disable_post() {
		$data = [
			'ID'          => $this->item->ID,
			'post_status' => 'acf-disabled',
		];

		wp_update_post( $data );
	}
}

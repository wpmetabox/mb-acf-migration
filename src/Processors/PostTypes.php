<?php
namespace MetaBox\ACF\Processors;
use WP_Query;

class PostTypes extends Base {
	protected function get_items() {
		// Process all post types at once.
		if ( $_SESSION['processed'] ) {
			return [];
		}

		$query = new WP_Query( [
			'post_type'              => 'acf-post-type',
			'post_status'            => 'any',
			'posts_per_page'         => -1,
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		] );

		return $query->posts;
	}

	protected function migrate_item() {
		$this->migrate_post_types();

		$this->disable_post();
	}

	private function migrate_post_types() {
		$post_type = unserialize( $this->item->post_content );

		$args   = [
			'labels'             => $post_type['labels'],
			'description'        => $post_type['description'],
			'supports'           => $post_type['supports'],
			'public'             => $post_type['public'],
			'publicly_queryable' => $post_type['publicly_queryable'],
			'show_ui'            => $post_type['show_ui'],
			'show_in_menu'       => $post_type['show_in_menu'],
			'show_in_rest'       => $post_type['show_in_rest'],
			'menu_icon'          => $post_type['menu_icon'] ?? 'dashicons-editor-justify',
			'can_export'         => $post_type['can_export'],
			'delete_with_user'   => $post_type['delete_with_user'],
			'rewrite'            => $post_type['rewrite'],
			'query_var'          => $post_type['query_var'],
			'has_archive'        => $post_type['has_archive'],
			'hierarchical'       => $post_type['hierarchical'],
			'taxonomies'         => $post_type['taxonomies'],
			'menu_position'      => $post_type['menu_position'] ?? 200,
			'map_meta_cap'       => true,
			'capabilities'       => [
				// Meta capabilities.
				'edit_post'              => 'edit_mb_post_type',
				'read_post'              => 'read_mb_post_type',
				'delete_post'            => 'delete_mb_post_type',

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

		register_post_type( 'mb-post-type', $args );
		register_post_type( $post_type['post-type'], $args );

	}

	protected function disable_post() {
		$data = [
			'ID'          => $this->item->ID,
			'post_status' => 'acf-disabled',
		];

		wp_update_post( $data );
	}
}

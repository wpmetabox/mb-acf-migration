<?php
namespace MetaBox\ACF\Processors\Data;

use WP_Query;

class Fields {
	private $parent;
	private $storage;
	private $field;

	public function __construct( $parent, $storage ) {
		$this->parent  = $parent;
		$this->storage = $storage;
	}

	public function migrate_fields() {
		$fields = $this->get_fields();
		foreach ( $fields as $field ) {
			$this->field = $field;
			$this->migrate_field();
		}
	}

	private function get_fields() {
		$query = new WP_Query( [
			'post_type'              => 'acf-field',
			'post_status'            => 'any',
			'posts_per_page'         => -1,
			'post_parent__in'        => (array) $this->parent,
			'order'                  => 'ASC',
			'orderby'                => 'menu_order',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		] );

		return $query->posts;
	}

	private function migrate_field() {
		$settings = unserialize( $this->field->post_content );

		$ignore_types = ['link', 'accordion', 'clone'];
		if ( in_array( $settings['type'], $ignore_types ) ) {
			return;
		}

		$settings['id']   = $this->field->post_excerpt;

		$args = [
			'settings' => $settings,
			'post_id'  => $this->field->ID,
			'storage'  => $this->storage,
		];
		$field_type = new FieldType( $args );
		$field_type->migrate();
	}
}

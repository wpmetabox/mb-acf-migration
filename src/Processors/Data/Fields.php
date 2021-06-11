<?php
namespace MetaBox\ACF\Processors\Data;

use WP_Query;

class Fields {
	private $id;
	private $storage;
	private $field;

	public function __construct( $id = 0, $storage ) {
		$this->id = $id;
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
			'post_type'      => 'acf-field',
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'no_found_rows'  => true,
			'post_parent'    => $this->id,
			'order'          => 'ASC',
			'orderby'        => 'menu_order',
		] );

		return $query->posts;
	}

	private function migrate_field() {
		$settings = unserialize( $this->field->post_content );

		$ignore_types = ['link', 'accordion', 'clone'];
		if ( in_array( $settings['type'], $ignore_types ) ) {
			return;
		}

		$settings['id'] = $this->field->post_excerpt;

		$field_type = new FieldType( $settings, $this->field, $this->storage );
		$field_type->migrate();
	}
}

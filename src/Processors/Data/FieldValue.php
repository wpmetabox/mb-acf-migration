<?php
namespace MetaBox\ACF\Processors\Data;

use WP_Query;

class FieldValue {
	private $key;
	private $storage;
	private $type;
	private $post_id;

	public function __construct( $args ) {
		$this->key        = $args['key'];
		$this->delete_key = $args['delete_key'] ?? "_{$args['key']}";
		$this->storage    = $args['storage'];

		// For group, repeater, flexible content.
		$this->type    = $args['type'] ?? null;
		$this->post_id = $args['post_id'] ?? null;
	}

	public function get_value() {
		$method = "get_value_{$this->type}";
		$method = method_exists( $this, $method ) ? $method : 'get_value_general';

		return $this->$method();
	}

	private function get_value_general() {
		// Get from backup key first.
		$backup_key = "_acf_bak_{$this->key}";
		$value = $this->storage->get( $backup_key );
		if ( '' !== $value ) {
			return $value;
		}

		// Backup the value.
		$value = $this->storage->get( $this->key );
		if ( '' !== $value ) {
			$this->storage->update( $backup_key, $value );
		}

		// Delete extra keys.
		$this->storage->delete( $this->delete_key );

		return $value;
	}

	private function get_value_group() {
		$values     = [];
		$sub_fields = $this->get_sub_fields();

		foreach ( $sub_fields as $sub_field ) {
			$settings = unserialize( $sub_field->post_content );
			$sub_key  = $sub_field->post_excerpt;
			$key      = $this->key . '_' . $sub_key;

			$field_value = new self( [
				'key'        => $key,
				'delete_key' => $key,
				'storage'    => $this->storage,
				'type'       => $settings['type'],
				'post_id'    => $sub_field->ID,
			] );

			$values[ $sub_key ] = $field_value->get_value();
		}

		return $values;
	}

	private function get_sub_fields() {
		$query_args = array_filter( [
			'post_type'      => 'acf-field',
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'no_found_rows'  => true,
			'order'          => 'ASC',
			'orderby'        => 'menu_order',
			'post_parent'    => $this->post_id,
		] );
		$query = new WP_Query( $query_args );

		return $query->posts;
	}
}
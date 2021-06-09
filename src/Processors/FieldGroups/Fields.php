<?php
namespace MetaBox\ACF\Processors\FieldGroups;

use WP_Query;

class Fields {
	private $id;
	private $fields = [];
	private $field;

	public function __construct( $id ) {
		$this->id = $id;
	}

	public function migrate_fields() {
		$fields = $this->get_fields();
		foreach ( $fields as $field ) {
			$this->field = $field;
			$this->migrate_field();
		}

		return $this->fields;
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

		$settings['name'] = $this->field->post_title;
		$settings['id']   = $this->field->post_excerpt;

		if ( $settings['type'] === 'google_map' ) {
			$id = 'text_' . uniqid();
			$address_field = [
				'id'     => $settings['id'] . '_address',
				'type'   => 'text',
				'name'   => $settings['name'] . ' ' . __( 'Address', 'mb-acf-migration' ),
				'_id'    => $id,
				'_state' => 'collapse',
			];
			$this->fields[ $id ] = $address_field;

			$settings['address_field'] = $address_field['id'];
		}

		$field_type = new FieldType( $settings, $this->field );
		$settings   = $field_type->migrate();

		$conditional_logic = new ConditionalLogic( $settings );
		$conditional_logic->migrate();

		$this->fields[ $settings['_id'] ] = $settings;
	}
}

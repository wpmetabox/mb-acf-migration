<?php
namespace MetaBox\ACF\Processors;

use WP_Query;
use MBBParser\Parsers\MetaBox;

class FieldGroups extends Base {
	private $post_id;
	private $settings = [];
	private $fields   = [];
	private $field;

	protected function get_items() {
		$query = new WP_Query( [
			'post_type'      => 'acf-field-group',
			'post_status'    => 'any',
			'posts_per_page' => $this->threshold,
			'no_found_rows'  => true,
			'offset'         => $_SESSION['processed'],
		] );

		return $query->posts;
	}

	protected function migrate_item() {
		$this->post_id  = null;
		$this->settings = [];
		$this->fields   = [];

		// $this->switch_post_type();

		$this->create_post();
		$this->migrate_settings();
		$this->migrate_fields();

		$data = [
			'post_name'  => $this->item->post_name,
			'post_title' => $this->item->post_title,
			'fields'     => $this->fields,
			'settings'   => $this->settings,
		];

		$parser = new MetaBox( $data );
		$parser->parse();
		update_post_meta( $this->post_id, 'meta_box', $parser->get_settings() );

		// $this->delete_post();
	}

	private function switch_post_type() {
		$data = [
			'ID'        => $this->item->ID,
			'post_type' => 'meta-box',
		];
		wp_update_post( $data );
		$this->post_id = $this->item->ID;
	}

	private function create_post() {
		$data = [
			'post_title'        => $this->item->post_title,
			'post_type'         => 'meta-box',
			'post_status'       => $this->item->post_status === 'acf-disabled' ? 'draft' : 'publish',
			'post_name'         => 'group_60bd91c5575f8',
			'post_content'      => $this->item->post_content,
			'post_date'         => $this->item->post_date,
			'post_date_gmt'     => $this->item->post_date_gmt,
			'post_modified'     => $this->item->post_modified,
			'post_modified_gmt' => $this->item->post_modified_gmt,
		];
		wp_update_post( $data );

		$post_id = get_post_meta( $this->item->ID, 'meta_box_id', true );
		if ( $post_id ) {
			$this->post_id = $data['ID'] = $post_id;
			wp_update_post( $data );
		} else {
			$this->post_id = wp_insert_post( $data );
		}

		update_post_meta( $this->item->ID, 'meta_box_id', $this->post_id );
	}

	private function delete_post() {
		wp_delete_post( $this->item->ID );
	}

	private function migrate_settings() {
		$this->settings = unserialize( $this->item->post_content );

		// Context.
		if ( !empty( $this->settings['position'] ) ) {
			$this->settings['context'] = $this->settings['position'] === 'acf_after_title' ? 'after_title' : $this->settings['position'];
			unset( $this->settings['position'] );
		}

		$this->migrate_location();

		update_post_meta( $this->post_id, 'settings', $this->settings );
	}

	private function migrate_location() {
		$location    = $this->settings['location'];
		$object_type = null;
		$post_types  = [];
		$taxonomies  = [];

		foreach ( $location as $group ) {
			foreach ( $group as $rule ) {
				if ( $rule['param'] === 'post_type' ) {
					$object_type = 'post';

					if ( $rule['operator'] === '==' ) {
						$post_types[] = $rule['value'];
					}
				}

				if ( $rule['param'] === 'attachment' ) {
					$object_type = 'post';
					$post_types[] = 'attachment';
				}

				if ( $rule['param'] === 'taxonomy' ) {
					$object_type = 'term';
					$taxonomies[] = $rule['value'];
				}

				if ( $rule['param'] === 'user_form' || $rule['param'] === 'user_role' ) {
					$object_type = 'user';
				}

				if ( $rule['param'] === 'comment' ) {
					$object_type = 'comment';
				}

				if ( $rule['param'] === 'block' ) {
					$object_type = 'block';
				}
			}
		}

		$post_types = array_unique( $post_types );
		$taxonomies = array_unique( $taxonomies );

		$this->settings['object_type'] = $object_type;

		if ( $object_type === 'post' ) {
			$this->settings['post_types'] = $post_types;
		} elseif ( $object_type === 'term' ) {
			$this->settings['taxonomies'] = $taxonomies;
		}

		$include_exclude = new FieldGroups\IncludeExclude( $location );
		$this->settings['include_exclude'] = $include_exclude->migrate();

		unset( $this->settings['location'] );
	}

	private function migrate_fields() {
		$fields = $this->get_fields();
		foreach ( $fields as $field ) {
			$this->field = $field;
			$this->migrate_field();
		}

		update_post_meta( $this->post_id, 'fields', $this->fields );
	}

	private function get_fields() {
		$query = new WP_Query( [
			'post_type'      => 'acf-field',
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'no_found_rows'  => true,
			'post_parent'    => $this->item->ID,
			'order'          => 'ASC',
		] );

		return $query->posts;
	}

	private function migrate_field() {
		$settings = unserialize( $this->field->post_content );

		$ignore_types = ['link'];
		if ( in_array( $settings['type'], $ignore_types ) ) {
			return;
		}

		$settings['name'] = $this->field->post_title;
		$settings['id']   = $this->field->post_excerpt;

		if ( $settings['type'] === 'google_map' ) {
			$address_field = [
				'id'     => $settings['id'] . '_address',
				'type'   => 'text',
				'name'   => $settings['name'] . ' ' . __( 'Address', 'mb-acf-migration' ),
				'_id'    => 'text_' . uniqid(),
				'_state' => 'collapse',
			];
			$this->fields[] = $address_field;

			$settings['address_field'] = $address_field['id'];
		}

		$field_type = new FieldGroups\FieldType( $settings );
		$settings   = $field_type->migrate();

		$conditional_logic = new FieldGroups\ConditionalLogic( $settings );
		$conditional_logic->migrate();

		$this->fields[] = $settings;
	}
}

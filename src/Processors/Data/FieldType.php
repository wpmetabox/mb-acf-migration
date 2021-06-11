<?php
namespace MetaBox\ACF\Processors\Data;

use MetaBox\Support\Arr;

class FieldType {
	private $settings;
	private $post;
	private $storage;

	public function __construct( $settings, $post, $storage ) {
		$this->settings = $settings;
		$this->post     = $post;
		$this->storage  = $storage;
	}

	public function __get( $name ) {
		return $this->settings[ $name ] ?? null;
	}

	public function migrate() {
		$this->storage->delete( "_{$this->id}" );

		$method = "migrate_{$this->type}";
		if ( method_exists( $this, $method ) ) {
			$this->$method();
		}

		return $this->settings;
	}

	private function get_data() {
		$backup_key = "_acf_bak_{$this->id}";
		$data = $this->storage->get( $backup_key );
		if ( '' !== $data ) {
			return $data;
		}

		// Back the value.
		$data = $this->storage->get( $this->id );
		if ( '' !== $data ) {
			$this->storage->update( $backup_key, $data );
		}

		return $data;
	}

	private function migrate_gallery() {
		$this->migrate_multiple( true );
	}

	private function migrate_select() {
		$this->migrate_multiple();
	}

	private function migrate_checkbox() {
		$this->migrate_multiple( true );
	}

	private function migrate_post_object() {
		$this->migrate_multiple();
	}

	private function migrate_page_link() {
		$this->migrate_multiple();
	}

	private function migrate_relationship() {
		$this->migrate_multiple( true );
	}

	private function migrate_user() {
		$this->migrate_multiple();
	}

	private function migrate_google_map() {
		$data = $this->get_data();
		if ( empty( $data ) || ! is_array( $data ) ) {
			return;
		}
		$value = "{$data['lat']},{$data['lng']},{$data['zoom']}";
		$this->storage->update( $this->id, $value );
		$this->storage->update( $this->id . '_address', $data['address'] );
	}

	private function migrate_group() {
	}

	private function migrate_repeater() {
	}

	private function migrate_flexible_content() {
	}

	private function migrate_multiple( $force_multiple = false ) {
		if ( ! $force_multiple && ! $this->multiple ) {
			return;
		}
		$data = $this->get_data();
		if ( empty( $data ) || ! is_array( $data ) ) {
			return;
		}
		$this->storage->delete( $this->id );
		foreach ( $data as $value ) {
			$this->storage->add( $this->id, $value );
		}
	}
}
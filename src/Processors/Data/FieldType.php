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
		$method = "migrate_{$this->type}";
		if ( method_exists( $this, $method ) ) {
			$this->$method();
		}

		return $this->settings;
	}

	private function get_data() {
		$backup_key = "_acf_bak_{$this->id}";
		$data = $this->storage->get( $backup_key );
		if ( empty( $data ) ) {
			$data = $this->storage->get( $this->id );
		}
		return $data;
	}

	private function migrate_gallery() {
	}

	private function migrate_select() {
	}

	private function migrate_checkbox() {
	}

	private function migrate_post_object() {
	}

	private function migrate_page_link() {
	}

	private function migrate_relationship() {
	}

	private function migrate_taxonomy() {
	}

	private function migrate_user() {
	}

	private function migrate_google_map() {
	}

	private function migrate_group() {
	}

	private function migrate_repeater() {
	}

	private function migrate_flexible_content() {
	}
}
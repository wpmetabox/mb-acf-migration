<?php
namespace MetaBox\ACF\Modules;

use MetaBox\Support\Arr;

class FieldType {
	private $settings;

	public function __construct( $settings ) {
		$this->settings = $settings;
	}

	public function migrate() {
		$this->migrate_general_settings();

		$type   = $this->settings['type'];
		$method = "migrate_$type";
		if ( method_exists( $this, $method ) ) {
			$this->$method();
		}

		return $this->settings;
	}

	private function migrate_general_settings() {
		Arr::change_key( $this->settings, 'instructions', 'label_description' );
		Arr::change_key( $this->settings, 'default_value', 'std' );
		Arr::change_key( $this->settings, 'maxlength', 'limit' );

		$this->settings['_id']    = $this->settings['type'] . '_' . uniqid();
		$this->settings['_state'] = 'collapse';
	}

	private function migrate_image() {
		$this->settings['type'] = 'single_image';
	}
}
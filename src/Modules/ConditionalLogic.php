<?php
namespace MetaBox\ACF\Modules;

class ConditionalLogic {
	private $settings;

	public function __construct( &$settings ) {
		$this->settings = &$settings;
	}

	public function migrate() {
		unset( $this->settings['conditional_logic'] );
	}
}
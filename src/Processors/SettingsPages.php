<?php
namespace MetaBox\ACF\Processors;

class SettingsPages extends Base {
	protected $object_type = 'setting';

	protected function get_items() {
		// Process all settings pages at once.
		if ( $_SESSION['processed'] ) {
			return [];
		}
		$settings_pages = acf_options_page()->get_pages();

		$settings_pages = array_values( wp_list_pluck( $settings_pages, 'post_id' ) );

		return $settings_pages;
	}

	protected function migrate_item() {
		$this->migrate_fields();
	}

	private function migrate_fields() {
		$fields = new Data\Fields( $this->get_field_group_ids(), $this );
		$fields->migrate_fields();
	}

	public function get( $key ) {
		$option_name = "{$this->item}_{$key}";
		return get_option( $option_name, '' );
	}

	public function add( $key, $value ) {
		$option = (array) get_option( $this->item, [] );
		if ( ! isset( $option[ $key ] ) ) {
			$option[ $key ] = [];
		}
		$option[ $key ][] = $value;
		update_option( $this->item, $option );
	}

	public function update( $key, $value ) {
		// For backup value.
		if ( strpos( $key, '_acf_bak' ) === 0 ) {
			update_option( $key, $value );
			return;
		}

		// For normal option value.
		$option = (array) get_option( $this->item, [] );
		$option[ $key ] = $value;
		update_option( $this->item, $option );
	}

	public function delete( $key ) {
		// Delete option first.
		$option_name = "{$this->item}_{$key}";
		delete_option( $option_name );

		$option_name = "_{$this->item}{$key}";
		delete_option( $option_name );

		// Delete value in the option.
		$option = (array) get_option( $this->item, [] );
		unset( $option[ $key ] );
		update_option( $this->item, $option );
	}
}

<?php
namespace MetaBox\ACF\Processors\FieldGroups;

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

		unset( $this->settings['wrapper'] );
		unset( $this->settings['return_format'] );
	}

	private function migrate_image() {
		$this->settings['type'] = 'single_image';
		Arr::change_key( $this->settings, 'preview_size', 'image_size' );

		unset( $this->settings['library'] );
		unset( $this->settings['min_width'] );
		unset( $this->settings['min_height'] );
		unset( $this->settings['min_size'] );
		unset( $this->settings['max_width'] );
		unset( $this->settings['max_height'] );
		unset( $this->settings['max_size'] );
		unset( $this->settings['mime_types'] );
	}

	private function migrate_file() {
		$this->settings['type'] = 'file_advanced';
		$this->settings['max_file_uploads'] = 1;

		unset( $this->settings['library'] );
		unset( $this->settings['min_size'] );
		unset( $this->settings['max_size'] );
		unset( $this->settings['mime_types'] );
	}

	private function migrate_wysiwyg() {
		$options = [];
		if ( $this->settings['toolbar'] === 'basic' ) {
			$id = uniqid();
			$options[ $id ] = [
				'id'    => $id,
				'key'   => 'teeny',
				'value' => true,
			];
		}
		if ( $this->settings['tabs'] === 'visual' ) {
			$id = uniqid();
			$options[ $id ] = [
				'id'    => $id,
				'key'   => 'quicktags',
				'value' => false,
			];
		}
		if ( $this->settings['tabs'] === 'text' ) {
			$id = uniqid();
			$options[ $id ] = [
				'id'    => $id,
				'key'   => 'tinymce',
				'value' => false,
			];
		}

		$id = uniqid();
		$options[ $id ] = [
			'id'    => $id,
			'key'   => 'media_buttons',
			'value' => (bool) $this->settings['media_upload'],
		];

		$this->settings['options'] = $options;

		unset( $this->settings['toolbar'] );
		unset( $this->settings['media_upload'] );
		unset( $this->settings['delay'] );
	}

	private function migrate_gallery() {
		$this->settings['type'] = 'image_advanced';
		Arr::change_key( $this->settings, 'preview_size', 'image_size' );
		$this->settings['add_to'] = $this->settings['insert'] === 'append' ? 'end' : 'beginning';

		unset( $this->settings['insert'] );
		unset( $this->settings['library'] );
		unset( $this->settings['min'] );
		unset( $this->settings['max'] );
		unset( $this->settings['min_width'] );
		unset( $this->settings['min_height'] );
		unset( $this->settings['min_size'] );
		unset( $this->settings['max_width'] );
		unset( $this->settings['max_height'] );
		unset( $this->settings['max_size'] );
		unset( $this->settings['mime_types'] );
	}

	private function migrate_select() {
		$this->migrate_choices();

		if ( $this->settings['allow_null'] ) {
			$this->settings['placeholder'] = __( '- Select -', 'mb-acf-migration' );
		}

		$this->settings['multiple'] = (bool) $this->settings['multiple'];
		if ( $this->settings['multiple'] ) {
			$this->settings['std'] = (array) $this->settings['std'];
			$this->settings['std'] = reset( $this->settings['std'] );
		} else {
			$this->settings['std'] = (string) $this->settings['std'];
		}

		if ( $this->settings['ui'] ) {
			$this->settings['type'] = 'select_advanced';
		}

		unset( $this->settings['allow_null'] );
		unset( $this->settings['ui'] );
		unset( $this->settings['ajax'] );
	}

	private function migrate_checkbox() {
		$this->settings['type'] = 'checkbox_list';

		$this->migrate_choices();

		$this->settings['std'] = implode( "\n", (array) $this->settings['std'] );

		Arr::change_key( $this->settings, 'toggle', 'select_all_none' );
		if ( $this->settings['layout'] === 'horizontal' ) {
			$this->settings['inline'] = true;
		}

		unset( $this->settings['layout'] );
		unset( $this->settings['allow_custom'] );
		unset( $this->settings['save_custom'] );
	}

	private function migrate_radio() {
		$this->migrate_choices();

		if ( $this->settings['layout'] === 'horizontal' ) {
			$this->settings['inline'] = true;
		}

		unset( $this->settings['allow_null'] );
		unset( $this->settings['other_choice'] );
		unset( $this->settings['layout'] );
		unset( $this->settings['save_other_choice'] );
	}

	private function migrate_button_group() {
		$this->migrate_choices();

		if ( $this->settings['layout'] === 'horizontal' ) {
			$this->settings['inline'] = true;
		}

		unset( $this->settings['allow_null'] );
		unset( $this->settings['layout'] );
	}

	private function migrate_choices() {
		$values = [];
		foreach ( $this->settings['choices'] as $key => $value ) {
			$values[] = "$key: $value";
		}
		$this->settings['options'] = implode( "\n", $values );

		unset( $this->settings['choices'] );
	}

	private function migrate_true_false() {
		$this->settings['type'] = $this->settings['ui'] ? 'switch' : 'checkbox';

		Arr::change_key( $this->settings, 'ui_on_text', 'on_label' );
		Arr::change_key( $this->settings, 'ui_off_text', 'off_label' );
		unset( $this->message );
	}

	private function migrate_post_object() {
		$this->settings['type'] = 'post';

		if ( isset( $this->settings['taxonomy'] ) && is_array( $this->settings['taxonomy'] ) ) {
			$query_args = [];
			foreach ( $this->settings['taxonomy'] as $k => $item ) {
				list( $taxonomy, $slug ) = explode( ':', $item );

				$id = uniqid();
				$query_args[ $id ] = [
					'id'    => $id,
					'key'   => "tax_query.$k.taxonomy",
					'value' => $taxonomy,
				];

				$id = uniqid();
				$query_args[ $id ] = [
					'id'    => $id,
					'key'   => "tax_query.$k.field",
					'value' => 'slug',
				];

				$id = uniqid();
				$query_args[ $id ] = [
					'id'    => $id,
					'key'   => "tax_query.$k.terms",
					'value' => $slug,
				];
			}

			$this->settings['query_args'] = $query_args;
		}

		$this->settings['multiple'] = (bool) $this->settings['multiple'];

		unset( $this->settings['allow_null'] );
		unset( $this->settings['ui'] );
	}

	private function migrate_page_link() {
		$this->migrate_post_object();

		unset( $this->settings['allow_archives'] );
	}

	private function migrate_relationship() {
		$this->migrate_post_object();

		unset( $this->settings['elements'] );
		unset( $this->settings['min'] );
		unset( $this->settings['max'] );
	}

	private function migrate_taxonomy() {
		$types = [
			'checkbox'     => 'checkbox_list',
			'multi_select' => 'select_advanced',
		];
		if ( isset( $types[ $this->settings['field_type'] ] ) ) {
			$this->settings['field_type'] = $types[ $this->settings['field_type'] ];
		}
		Arr::change_key( $this->settings, 'add_term', 'add_new' );

		$this->settings['multiple'] = (bool) $this->settings['multiple'];

		unset( $this->settings['save_terms'] );
		unset( $this->settings['load_terms'] );
		unset( $this->settings['allow_null'] );
	}
}
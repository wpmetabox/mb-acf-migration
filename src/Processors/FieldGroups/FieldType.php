<?php
namespace MetaBox\ACF\Processors\FieldGroups;

use MetaBox\Support\Arr;

class FieldType {
	private $settings;

	public function __construct( $settings ) {
		$this->settings = $settings;
	}

	public function __get( $name ) {
		return $this->settings[ $name ] ?? null;
	}

	public function __set( $name, $value ) {
		return $this->settings[ $name ] = $value;
	}

	public function __isset( $name ) {
		return isset( $this->settings[ $name ] );
	}

	public function __unset( $name ) {
		unset( $this->settings[ $name ] );
	}

	public function migrate() {
		$this->migrate_general_settings();

		$method = "migrate_{$this->type}";
		if ( method_exists( $this, $method ) ) {
			$this->$method();
		}

		return $this->settings;
	}

	private function migrate_general_settings() {
		Arr::change_key( $this->settings, 'instructions', 'label_description' );
		Arr::change_key( $this->settings, 'default_value', 'std' );
		Arr::change_key( $this->settings, 'maxlength', 'limit' );

		$this->_id    = $this->type . '_' . uniqid();
		$this->_state = 'collapse';

		unset( $this->wrapper );
		unset( $this->return_format );
	}

	private function migrate_image() {
		$this->type = 'single_image';
		Arr::change_key( $this->settings, 'preview_size', 'image_size' );

		unset( $this->library );
		unset( $this->min_width );
		unset( $this->min_height );
		unset( $this->min_size );
		unset( $this->max_width );
		unset( $this->max_height );
		unset( $this->max_size );
		unset( $this->mime_types );
	}

	private function migrate_file() {
		$this->type = 'file_advanced';
		$this->max_file_uploads = 1;

		unset( $this->library );
		unset( $this->min_size );
		unset( $this->max_size );
		unset( $this->mime_types );
	}

	private function migrate_wysiwyg() {
		$options = [];
		if ( $this->toolbar === 'basic' ) {
			$id = uniqid();
			$options[ $id ] = [
				'id'    => $id,
				'key'   => 'teeny',
				'value' => true,
			];
		}
		if ( $this->tabs === 'visual' ) {
			$id = uniqid();
			$options[ $id ] = [
				'id'    => $id,
				'key'   => 'quicktags',
				'value' => false,
			];
		}
		if ( $this->tabs === 'text' ) {
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
			'value' => (bool) $this->media_upload,
		];

		$this->options = $options;

		unset( $this->toolbar );
		unset( $this->media_upload );
		unset( $this->delay );
	}

	private function migrate_gallery() {
		$this->type = 'image_advanced';
		Arr::change_key( $this->settings, 'preview_size', 'image_size' );
		$this->add_to = $this->insert === 'append' ? 'end' : 'beginning';

		unset( $this->insert );
		unset( $this->library );
		unset( $this->min );
		unset( $this->max );
		unset( $this->min_width );
		unset( $this->min_height );
		unset( $this->min_size );
		unset( $this->max_width );
		unset( $this->max_height );
		unset( $this->max_size );
		unset( $this->mime_types );
	}

	private function migrate_select() {
		$this->migrate_choices();

		if ( $this->allow_null ) {
			$this->placeholder = __( '- Select -', 'mb-acf-migration' );
		}

		$this->multiple = (bool) $this->multiple;
		if ( $this->multiple ) {
			$this->std = (array) $this->std;
			$this->std = reset( $this->settings['std'] );
		} else {
			$this->std = (string) $this->std;
		}

		if ( $this->ui ) {
			$this->type = 'select_advanced';
		}

		unset( $this->allow_null );
		unset( $this->ui );
		unset( $this->ajax );
	}

	private function migrate_checkbox() {
		$this->type = 'checkbox_list';

		$this->migrate_choices();

		$this->std = implode( "\n", (array) $this->std );

		Arr::change_key( $this->settings, 'toggle', 'select_all_none' );
		if ( $this->layout === 'horizontal' ) {
			$this->inline = true;
		}

		unset( $this->layout );
		unset( $this->allow_custom );
		unset( $this->save_custom );
	}

	private function migrate_radio() {
		$this->migrate_choices();

		if ( $this->layout === 'horizontal' ) {
			$this->inline = true;
		}

		unset( $this->allow_null );
		unset( $this->other_choice );
		unset( $this->layout );
		unset( $this->save_other_choice );
	}

	private function migrate_button_group() {
		$this->migrate_choices();

		if ( $this->layout === 'horizontal' ) {
			$this->inline = true;
		}

		unset( $this->allow_null );
		unset( $this->layout );
	}

	private function migrate_choices() {
		$values = [];
		foreach ( $this->choices as $key => $value ) {
			$values[] = "$key: $value";
		}
		$this->options = implode( "\n", $values );

		unset( $this->choices );
	}

	private function migrate_true_false() {
		$this->type = $this->ui ? 'switch' : 'checkbox';

		Arr::change_key( $this->settings, 'ui_on_text', 'on_label' );
		Arr::change_key( $this->settings, 'ui_off_text', 'off_label' );
		unset( $this->message );
	}

	private function migrate_post_object() {
		$this->type = 'post';

		if ( isset( $this->taxonomy ) && is_array( $this->taxonomy ) ) {
			$query_args = [];
			foreach ( $this->taxonomy as $k => $item ) {
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

			$this->query_args = $query_args;
		}

		$this->multiple = (bool) $this->multiple;

		unset( $this->allow_null );
		unset( $this->ui );
	}

	private function migrate_page_link() {
		$this->migrate_post_object();

		unset( $this->allow_archives );
	}

	private function migrate_relationship() {
		$this->migrate_post_object();

		unset( $this->elements );
		unset( $this->min );
		unset( $this->max );
	}

	private function migrate_taxonomy() {
		$types = [
			'checkbox'     => 'checkbox_list',
			'multi_select' => 'select_advanced',
		];
		if ( isset( $types[ $this->field_type ] ) ) {
			$this->field_type = $types[ $this->field_type ];
		}
		Arr::change_key( $this->settings, 'add_term', 'add_new' );

		$this->multiple = (bool) $this->multiple;

		unset( $this->save_terms );
		unset( $this->load_terms );
		unset( $this->allow_null );
	}

	private function migrate_user() {
		$this->multiple = (bool) $this->multiple;

		unset( $this->role );
		unset( $this->allow_null );
	}
}
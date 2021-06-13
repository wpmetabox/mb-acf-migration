<?php
namespace MetaBox\ACF\Processors;

use MetaBox\Support\Data as Helper;

class Terms extends Base {
	protected function get_items() {
		$terms = get_terms( [
			'taxonomy'   => array_keys( Helper::get_taxonomies() ),
			'hide_empty' => false,
			'number'     => $this->threshold,
			'offset'     => $_SESSION['processed'],
			'fields'     => 'ids',
		] );

		return $terms;
	}

	protected function migrate_item() {
		$this->migrate_fields();
	}

	private function migrate_fields() {
		$fields = new Data\Fields( 0, $this );
		$fields->migrate_fields();
	}

	public function get( $key ) {
		return get_term_meta( $this->item, $key, true );
	}

	public function add( $key, $value ) {
		add_term_meta( $this->item, $key, $value, false );
	}

	public function update( $key, $value ) {
		update_term_meta( $this->item, $key, $value );
	}

	public function delete( $key ) {
		delete_term_meta( $this->item, $key );
	}
}

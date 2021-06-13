<?php
namespace MetaBox\ACF\Processors;

use MetaBox\Support\Data as Helper;
use WP_Query;

class Posts extends Base {
	protected function get_items() {
		$query = new WP_Query( [
			'post_type'      => array_keys( Helper::get_post_types() ),
			'post_status'    => 'any',
			'posts_per_page' => $this->threshold,
			'no_found_rows'  => true,
			'offset'         => $_SESSION['processed'],
			'fields'         => 'ids',
		] );

		return $query->posts;
	}

	protected function migrate_item() {
		$this->migrate_fields();
	}

	private function migrate_fields() {
		$fields = new Data\Fields( 0, $this );
		$fields->migrate_fields();
	}

	public function get( $key ) {
		return get_post_meta( $this->item, $key, true );
	}

	public function add( $key, $value ) {
		add_post_meta( $this->item, $key, $value, false );
	}

	public function update( $key, $value ) {
		update_post_meta( $this->item, $key, $value );
	}

	public function delete( $key ) {
		delete_post_meta( $this->item, $key );
	}
}

<?php
namespace MetaBox\ACF\Processors;

use MetaBox\Support\Data as Helper;
use WP_Query;

class Posts extends Base {
	protected $object_type = 'post';

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
}

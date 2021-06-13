<?php
namespace MetaBox\ACF\Processors;

use WP_Query;

class Comments extends Base {
	protected $object_type     = 'comment';
	private   $field_group_ids = null;

	protected function get_items() {
		$comments = get_comments( [
			'number'        => $this->threshold,
			'offset'        => $_SESSION['processed'],
			'no_found_rows' => true,
			'fields'        => 'ids',
		] );

		return $comments;
	}

	protected function migrate_item() {
		$this->migrate_fields();
	}

	private function migrate_fields() {
		$fields = new Data\Fields( $this->get_field_group_ids(), $this );
		$fields->migrate_fields();
	}

	private function get_field_group_ids() {
		if ( null !== $this->field_group_ids ) {
			return $this->field_group_ids;
		}

		$query = new WP_Query( [
			'post_type'      => 'meta-box',
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'no_found_rows'  => true,
			'fields'         => 'ids',
		] );

		$ids = array_filter( $query->posts, function( $id ) {
			$settings = get_post_meta( $id, 'settings', true );
			return $settings['object_type'] === 'comment';
		} );

		$this->field_group_ids = $ids;

		return $ids;
	}
}

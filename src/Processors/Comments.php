<?php
namespace MetaBox\ACF\Processors;

class Comments extends Base {
	protected $object_type = 'comment';

	protected function get_items() {
		$field_group_ids = $this->get_field_group_ids();
		if ( empty( $field_group_ids ) ) {
			return [];
		}

		$comments = get_comments( [
			'number'        => $this->threshold,
			'offset'        => (int) $_SESSION['processed'], // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
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
}

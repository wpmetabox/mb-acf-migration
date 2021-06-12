<?php
namespace MetaBox\ACF\Processors;

class Users extends Base {
	protected function get_items() {
		$users = get_users( [
			'number'      => $this->threshold,
			'offset'      => $_SESSION['processed'],
			'count_total' => false,
		] );

		return $users;
	}

	protected function migrate_item() {
		$this->migrate_fields();
	}

	private function migrate_fields() {
		$fields = new Data\Fields( 0, $this );
		$fields->migrate_fields();
	}

	public function get( $key ) {
		return get_user_meta( $this->item->ID, $key, true );
	}

	public function add( $key, $value ) {
		add_user_meta( $this->item->ID, $key, $value, false );
	}

	public function update( $key, $value ) {
		update_user_meta( $this->item->ID, $key, $value );
	}

	public function delete( $key ) {
		delete_user_meta( $this->item->ID, $key );
	}
}

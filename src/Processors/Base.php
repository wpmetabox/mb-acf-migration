<?php
namespace MetaBox\ACF\Processors;

abstract class Base {
	protected $threshold = 10;
	protected $item;
	protected $object_type;

	public function migrate() {
		$items = $this->get_items();
		if ( empty( $items ) ) {
			wp_send_json_success( [
				'message' => __( 'Done', 'mb-acf-migration' ),
				'type'    => 'done',
			] );
		}

		$output = [];
		foreach( $items as $item ) {
			$this->item = $item;
			$output[] = $this->migrate_item();
		}
		$output = array_filter( $output );

		$_SESSION['processed'] += count( $items );
		wp_send_json_success( [
			'message' => sprintf( __( 'Processed %d items...', 'mb-acf-migration' ), $_SESSION['processed'] ) . '<br>' . implode( '<br>', $output ),
			'type'    => 'continue',
		] );
	}

	abstract protected function get_items();
	abstract protected function migrate_item();

	public function get( $key ) {
		return get_metadata( $this->object_type, $this->item, $key, true );
	}

	public function add( $key, $value ) {
		add_metadata( $this->object_type, $this->item, $key, $value, false );
	}

	public function update( $key, $value ) {
		update_metadata( $this->object_type, $this->item, $key, $value );
	}

	public function delete( $key ) {
		delete_metadata( $this->object_type, $this->item, $key );
	}
}

<?php
namespace MetaBox\ACF\Processors;

abstract class Base {
	protected $threshold = 10;
	protected $item;

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
}

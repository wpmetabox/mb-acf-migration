<?php
namespace MetaBox\ACF;

class AdminPage {
	public function __construct() {
		add_filter( 'rwmb_admin_menu', '__return_true' );
		add_action( 'admin_menu', [ $this, 'add_menu' ] );
	}

	public function add_menu() {
		$page_hook = add_submenu_page(
			'meta-box',
			esc_html__( 'ACF Migration', 'mb-acf-migration' ),
			esc_html__( 'ACF Migration', 'mb-acf-migration' ),
			'manage_options',
			'mb-acf-migration',
			[ $this, 'render' ]
		);
		add_action( "admin_print_styles-$page_hook", [ $this, 'enqueue' ] );
	}

	public function enqueue() {
		wp_enqueue_script( 'mb-acf', plugins_url( 'js/migrate.js', __DIR__ ), [], '', true );
		wp_localize_script( 'mb-acf', 'MbAcf', [
			'start' => __( 'Start', 'mb-acf-migration' ),
			'done'  => __( 'Done', 'mb-acf-migration' ),
		] );
	}

	public function render() {
		?>
		<div class="wrap">
			<h1><?= get_admin_page_title() ?></h1>
			<button class="button button-primary" id="process"><?php esc_html_e( 'Migrate', 'mb-acf-migration' ) ?></button>
			<div id="status"></div>
		</div>
		<?php
	}
}

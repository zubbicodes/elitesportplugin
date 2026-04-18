<?php
/**
 * ESC_Elementor — Registers all Elementor widgets for the plugin.
 *
 * Widgets:
 *   - ESC_Widget_Coach_Form     → Coach registration form
 *   - ESC_Widget_Student_Form   → Find-a-coach / student lead form
 *   - ESC_Widget_Directory      → Coach directory grid
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ESC_Elementor {

	public function __construct() {
		add_action( 'elementor/widgets/register', [ $this, 'register_widgets' ] );
		add_action( 'elementor/elements/categories_registered', [ $this, 'register_category' ] );
	}

	/**
	 * Register a custom Elementor widget category.
	 */
	public function register_category( $elements_manager ): void {
		$elements_manager->add_category(
			'elite-sports-connect',
			[
				'title' => __( 'Elite Sports Connect', 'elite-sports-connect' ),
				'icon'  => 'fa fa-trophy',
			]
		);
	}

	/**
	 * Load widget files and register with Elementor.
	 */
	public function register_widgets( $widgets_manager ): void {
		require_once ESC_PLUGIN_DIR . 'elementor-widgets/widget-coach-form.php';
		require_once ESC_PLUGIN_DIR . 'elementor-widgets/widget-student-form.php';
		require_once ESC_PLUGIN_DIR . 'elementor-widgets/widget-directory.php';

		$widgets_manager->register( new ESC_Widget_Coach_Form() );
		$widgets_manager->register( new ESC_Widget_Student_Form() );
		$widgets_manager->register( new ESC_Widget_Directory() );
	}
}

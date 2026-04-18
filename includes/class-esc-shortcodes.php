<?php
/**
 * ESC_Shortcodes — Registers all plugin shortcodes and loads templates.
 *
 * Available shortcodes:
 *   [register_coach]
 *   [find_a_coach]
 *   [directory_coaches]
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ESC_Shortcodes {

	public function __construct() {
		add_shortcode( 'register_coach',    [ $this, 'render_coach_form' ] );
		add_shortcode( 'find_a_coach',      [ $this, 'render_student_form' ] );
		add_shortcode( 'directory_coaches', [ $this, 'render_directory' ] );
	}

	// ─── [register_coach] ────────────────────────────────────────────────────

	public function render_coach_form( array $atts = [] ): string {
		$atts = shortcode_atts( [
			'title'          => __( 'Join Our Coaching Platform', 'elite-sports-connect' ),
			'subtitle'       => __( 'Connect with athletes looking for expert guidance.', 'elite-sports-connect' ),
			'button_text'    => __( 'Submit Application', 'elite-sports-connect' ),
			'success_msg'    => __( 'Thank you! Your application is under review. We will be in touch shortly.', 'elite-sports-connect' ),
		], $atts, 'register_coach' );

		ob_start();
		require ESC_PLUGIN_DIR . 'templates/form-coach.php';
		return ob_get_clean();
	}

	// ─── [find_a_coach] ──────────────────────────────────────────────────────

	public function render_student_form( array $atts = [] ): string {
		$atts = shortcode_atts( [
			'title'       => __( 'Find Your Perfect Coach', 'elite-sports-connect' ),
			'subtitle'    => __( 'Tell us what you\'re looking for and we\'ll connect you with the best match.', 'elite-sports-connect' ),
			'button_text' => __( 'Send My Request', 'elite-sports-connect' ),
			'success_msg' => __( 'We have received your request! Our team will reach out with coach recommendations.', 'elite-sports-connect' ),
		], $atts, 'find_a_coach' );

		ob_start();
		require ESC_PLUGIN_DIR . 'templates/form-student.php';
		return ob_get_clean();
	}

	// ─── [directory_coaches] ─────────────────────────────────────────────────

	public function render_directory( array $atts = [] ): string {
		$atts = shortcode_atts( [
			'posts_per_page' => 12,
			'sport_filter'   => 'yes',
			'columns'        => 3,
			'title'          => __( 'Our Coaching Team', 'elite-sports-connect' ),
			'layout'         => 'feature',
			'show_contact'   => 'yes',
		], $atts, 'directory_coaches' );

		$atts['posts_per_page'] = absint( $atts['posts_per_page'] );
		$atts['columns']        = min( 4, max( 1, absint( $atts['columns'] ) ) );
		$atts['layout']         = in_array( $atts['layout'], [ 'grid', 'feature', 'split', 'minimal' ], true ) ? $atts['layout'] : 'feature';
		$atts['show_contact']   = ( 'no' === $atts['show_contact'] ) ? 'no' : 'yes';

		// Active sport filter from query param.
		$active_sport = isset( $_GET['esc_sport'] )
			? sanitize_text_field( wp_unslash( $_GET['esc_sport'] ) )
			: '';

		$query_args = [
			'post_type'      => 'coach',
			'post_status'    => 'publish',
			'posts_per_page' => $atts['posts_per_page'],
			'paged'          => max( 1, get_query_var( 'paged' ) ),
			'orderby'        => 'title',
			'order'          => 'ASC',
		];

		if ( $active_sport && in_array( $active_sport, ESC_Forms::get_sports_list(), true ) ) {
			$query_args['meta_query'] = [
				[
					'key'   => '_esc_sport',
					'value' => $active_sport,
				],
			];
		}

		$coaches = new WP_Query( $query_args );

		ob_start();
		require ESC_PLUGIN_DIR . 'templates/directory-coaches.php';
		wp_reset_postdata();
		return ob_get_clean();
	}

	// ─── Status Notice Helper ─────────────────────────────────────────────────

	private function render_status_notice( string $form_type ): void {
		if ( ! isset( $_GET['esc_status'] ) ) {
			return;
		}
		$status = sanitize_text_field( wp_unslash( $_GET['esc_status'] ) );
		$form   = isset( $_GET['esc_form'] ) ? sanitize_text_field( wp_unslash( $_GET['esc_form'] ) ) : '';

		if ( 'error' === $status ) {
			$message = isset( $_GET['esc_message'] )
				? esc_html( rawurldecode( sanitize_text_field( wp_unslash( $_GET['esc_message'] ) ) ) )
				: __( 'An error occurred. Please try again.', 'elite-sports-connect' );
			echo '<div class="esc-notice esc-notice--error" role="alert"><span class="esc-notice__icon">&#9888;</span>' . $message . '</div>';
		} elseif ( 'success' === $status && $form === $form_type ) {
			echo '<div class="esc-notice esc-notice--success" role="alert"><span class="esc-notice__icon">&#10003;</span>' .
				esc_html__( 'Your submission was received successfully!', 'elite-sports-connect' ) .
				'</div>';
		}
	}
}

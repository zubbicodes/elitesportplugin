<?php
/**
 * Elementor Widget: Find a Coach (Student Lead Form)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ESC_Widget_Student_Form extends \Elementor\Widget_Base {

	public function get_name(): string {
		return 'esc_student_form';
	}

	public function get_title(): string {
		return __( 'Find a Coach Form', 'elite-sports-connect' );
	}

	public function get_icon(): string {
		return 'eicon-form-vertical';
	}

	public function get_categories(): array {
		return [ 'elite-sports-connect' ];
	}

	public function get_keywords(): array {
		return [ 'student', 'athlete', 'find', 'coach', 'form', 'sports', 'lead' ];
	}

	protected function register_controls(): void {

		// ── Content Tab ──────────────────────────────────────────────────────
		$this->start_controls_section( 'section_content', [
			'label' => __( 'Form Content', 'elite-sports-connect' ),
			'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
		] );

		$this->add_control( 'form_title', [
			'label'   => __( 'Title', 'elite-sports-connect' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => __( 'Find Your Perfect Coach', 'elite-sports-connect' ),
		] );

		$this->add_control( 'form_subtitle', [
			'label'   => __( 'Subtitle', 'elite-sports-connect' ),
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => __( 'Tell us what you\'re looking for and we\'ll connect you with the best match.', 'elite-sports-connect' ),
			'rows'    => 2,
		] );

		$this->add_control( 'button_text', [
			'label'   => __( 'Submit Button Text', 'elite-sports-connect' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => __( 'Send My Request', 'elite-sports-connect' ),
		] );

		$this->add_control( 'show_location_field', [
			'label'        => __( 'Show Location Field', 'elite-sports-connect' ),
			'type'         => \Elementor\Controls_Manager::SWITCHER,
			'label_on'     => __( 'Yes', 'elite-sports-connect' ),
			'label_off'    => __( 'No', 'elite-sports-connect' ),
			'return_value' => 'yes',
			'default'      => 'yes',
		] );

		$this->end_controls_section();

		// ── Style Tab: Card ───────────────────────────────────────────────────
		$this->start_controls_section( 'section_style_card', [
			'label' => __( 'Form Card', 'elite-sports-connect' ),
			'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
		] );

		$this->add_control( 'card_background', [
			'label'     => __( 'Card Background', 'elite-sports-connect' ),
			'type'      => \Elementor\Controls_Manager::COLOR,
			'default'   => '#ffffff',
			'selectors' => [ '{{WRAPPER}} .esc-form-card' => 'background-color: {{VALUE}};' ],
		] );

		$this->add_responsive_control( 'card_padding', [
			'label'      => __( 'Card Padding', 'elite-sports-connect' ),
			'type'       => \Elementor\Controls_Manager::DIMENSIONS,
			'size_units' => [ 'px', 'em', '%' ],
			'default'    => [ 'top' => '48', 'right' => '48', 'bottom' => '48', 'left' => '48', 'unit' => 'px', 'isLinked' => true ],
			'selectors'  => [ '{{WRAPPER}} .esc-form-card' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
		] );

		$this->add_control( 'card_border_radius', [
			'label'      => __( 'Border Radius', 'elite-sports-connect' ),
			'type'       => \Elementor\Controls_Manager::SLIDER,
			'size_units' => [ 'px' ],
			'range'      => [ 'px' => [ 'min' => 0, 'max' => 40 ] ],
			'default'    => [ 'unit' => 'px', 'size' => 20 ],
			'selectors'  => [ '{{WRAPPER}} .esc-form-card' => 'border-radius: {{SIZE}}{{UNIT}};' ],
		] );

		$this->end_controls_section();

		// ── Style Tab: Heading ────────────────────────────────────────────────
		$this->start_controls_section( 'section_style_heading', [
			'label' => __( 'Heading', 'elite-sports-connect' ),
			'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
		] );

		$this->add_control( 'title_color', [
			'label'     => __( 'Title Color', 'elite-sports-connect' ),
			'type'      => \Elementor\Controls_Manager::COLOR,
			'default'   => '#0A192F',
			'selectors' => [ '{{WRAPPER}} .esc-form__title' => 'color: {{VALUE}};' ],
		] );

		$this->add_group_control( \Elementor\Group_Control_Typography::get_type(), [
			'name'     => 'title_typography',
			'selector' => '{{WRAPPER}} .esc-form__title',
		] );

		$this->add_control( 'accent_color', [
			'label'     => __( 'Accent / Button Color', 'elite-sports-connect' ),
			'type'      => \Elementor\Controls_Manager::COLOR,
			'default'   => '#F4A100',
			'selectors' => [
				'{{WRAPPER}} .esc-btn--primary'    => 'background-color: {{VALUE}};',
				'{{WRAPPER}} .esc-form__header-bar' => 'background-color: {{VALUE}};',
			],
		] );

		$this->end_controls_section();
	}

	protected function render(): void {
		$settings = $this->get_settings_for_display();
		echo do_shortcode(
			'[find_a_coach title="' . esc_attr( $settings['form_title'] ) . '" subtitle="' . esc_attr( $settings['form_subtitle'] ) . '" button_text="' . esc_attr( $settings['button_text'] ) . '"]'
		);
	}
}

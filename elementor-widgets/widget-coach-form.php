<?php
/**
 * Elementor Widget: Coach Registration Form
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ESC_Widget_Coach_Form extends \Elementor\Widget_Base {

	public function get_name(): string {
		return 'esc_coach_form';
	}

	public function get_title(): string {
		return __( 'Coach Registration Form', 'elite-sports-connect' );
	}

	public function get_icon(): string {
		return 'eicon-form-horizontal';
	}

	public function get_categories(): array {
		return [ 'elite-sports-connect' ];
	}

	public function get_keywords(): array {
		return [ 'coach', 'register', 'form', 'sports', 'elite' ];
	}

	protected function register_controls(): void {

		// ── Content Tab ──────────────────────────────────────────────────────
		$this->start_controls_section( 'section_content', [
			'label' => __( 'Form Content', 'elite-sports-connect' ),
			'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
		] );

		$this->add_control( 'form_title', [
			'label'       => __( 'Title', 'elite-sports-connect' ),
			'type'        => \Elementor\Controls_Manager::TEXT,
			'default'     => __( 'Join Our Coaching Platform', 'elite-sports-connect' ),
			'placeholder' => __( 'Form Title', 'elite-sports-connect' ),
		] );

		$this->add_control( 'form_subtitle', [
			'label'       => __( 'Subtitle', 'elite-sports-connect' ),
			'type'        => \Elementor\Controls_Manager::TEXTAREA,
			'default'     => __( 'Connect with athletes looking for expert guidance.', 'elite-sports-connect' ),
			'rows'        => 2,
		] );

		$this->add_control( 'button_text', [
			'label'   => __( 'Submit Button Text', 'elite-sports-connect' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => __( 'Submit Application', 'elite-sports-connect' ),
		] );

		$this->add_control( 'show_website_field', [
			'label'        => __( 'Show Website Field', 'elite-sports-connect' ),
			'type'         => \Elementor\Controls_Manager::SWITCHER,
			'label_on'     => __( 'Yes', 'elite-sports-connect' ),
			'label_off'    => __( 'No', 'elite-sports-connect' ),
			'return_value' => 'yes',
			'default'      => 'no',
		] );

		$this->end_controls_section();

		// ── Style Tab: Form Card ─────────────────────────────────────────────
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

		// ── Style Tab: Heading ───────────────────────────────────────────────
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
			'label'    => __( 'Title Typography', 'elite-sports-connect' ),
			'selector' => '{{WRAPPER}} .esc-form__title',
		] );

		$this->add_control( 'subtitle_color', [
			'label'     => __( 'Subtitle Color', 'elite-sports-connect' ),
			'type'      => \Elementor\Controls_Manager::COLOR,
			'default'   => '#718096',
			'selectors' => [ '{{WRAPPER}} .esc-form__subtitle' => 'color: {{VALUE}};' ],
		] );

		$this->end_controls_section();

		// ── Style Tab: Button ────────────────────────────────────────────────
		$this->start_controls_section( 'section_style_button', [
			'label' => __( 'Submit Button', 'elite-sports-connect' ),
			'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
		] );

		$this->add_control( 'btn_background', [
			'label'     => __( 'Background Color', 'elite-sports-connect' ),
			'type'      => \Elementor\Controls_Manager::COLOR,
			'default'   => '#F4A100',
			'selectors' => [ '{{WRAPPER}} .esc-btn--primary' => 'background-color: {{VALUE}};' ],
		] );

		$this->add_control( 'btn_text_color', [
			'label'     => __( 'Text Color', 'elite-sports-connect' ),
			'type'      => \Elementor\Controls_Manager::COLOR,
			'default'   => '#0A192F',
			'selectors' => [ '{{WRAPPER}} .esc-btn--primary' => 'color: {{VALUE}};' ],
		] );

		$this->end_controls_section();
	}

	protected function render(): void {
		$settings = $this->get_settings_for_display();

		$atts = [
			'title'           => $settings['form_title'],
			'subtitle'        => $settings['form_subtitle'],
			'button_text'     => $settings['button_text'],
			'show_website'    => $settings['show_website_field'],
		];

		echo do_shortcode( '[register_coach title="' . esc_attr( $atts['title'] ) . '" subtitle="' . esc_attr( $atts['subtitle'] ) . '" button_text="' . esc_attr( $atts['button_text'] ) . '"]' );
	}
}

<?php
/**
 * Elementor Widget: Coach Directory
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ESC_Widget_Directory extends \Elementor\Widget_Base {

	public function get_name(): string {
		return 'esc_directory';
	}

	public function get_title(): string {
		return __( 'Coach Directory', 'elite-sports-connect' );
	}

	public function get_icon(): string {
		return 'eicon-gallery-grid';
	}

	public function get_categories(): array {
		return [ 'elite-sports-connect' ];
	}

	public function get_keywords(): array {
		return [ 'coach', 'directory', 'grid', 'list', 'sports', 'elite' ];
	}

	protected function register_controls(): void {

		// ── Content Tab: Query ────────────────────────────────────────────────
		$this->start_controls_section( 'section_query', [
			'label' => __( 'Query & Layout', 'elite-sports-connect' ),
			'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
		] );

		$this->add_control( 'section_title', [
			'label'   => __( 'Section Title', 'elite-sports-connect' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => __( 'Our Coaching Team', 'elite-sports-connect' ),
		] );

		$this->add_control( 'posts_per_page', [
			'label'   => __( 'Coaches Per Page', 'elite-sports-connect' ),
			'type'    => \Elementor\Controls_Manager::NUMBER,
			'min'     => 1,
			'max'     => 50,
			'default' => 12,
		] );

		$this->add_control( 'columns', [
			'label'   => __( 'Grid Columns', 'elite-sports-connect' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => '3',
			'options' => [
				'1' => '1',
				'2' => '2',
				'3' => '3',
				'4' => '4',
			],
		] );

		$this->add_control( 'layout', [
			'label'   => __( 'Display Layout', 'elite-sports-connect' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => 'feature',
			'options' => [
				'grid'    => __( 'Grid Cards', 'elite-sports-connect' ),
				'feature' => __( 'Feature Rows', 'elite-sports-connect' ),
				'split'   => __( 'Split Cards', 'elite-sports-connect' ),
				'minimal' => __( 'Minimal List', 'elite-sports-connect' ),
			],
		] );

		$this->add_control( 'sport_filter', [
			'label'        => __( 'Show Sport Filter Bar', 'elite-sports-connect' ),
			'type'         => \Elementor\Controls_Manager::SWITCHER,
			'label_on'     => __( 'Yes', 'elite-sports-connect' ),
			'label_off'    => __( 'No', 'elite-sports-connect' ),
			'return_value' => 'yes',
			'default'      => 'yes',
		] );

		$this->add_control( 'show_contact_button', [
			'label'        => __( 'Show Contact Button', 'elite-sports-connect' ),
			'type'         => \Elementor\Controls_Manager::SWITCHER,
			'label_on'     => __( 'Yes', 'elite-sports-connect' ),
			'label_off'    => __( 'No', 'elite-sports-connect' ),
			'return_value' => 'yes',
			'default'      => 'yes',
		] );

		$this->end_controls_section();

		// ── Style Tab: Cards ──────────────────────────────────────────────────
		$this->start_controls_section( 'section_style_cards', [
			'label' => __( 'Coach Cards', 'elite-sports-connect' ),
			'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
		] );

		$this->add_control( 'card_bg', [
			'label'     => __( 'Card Background', 'elite-sports-connect' ),
			'type'      => \Elementor\Controls_Manager::COLOR,
			'default'   => '#ffffff',
			'selectors' => [ '{{WRAPPER}} .esc-coach-card' => 'background-color: {{VALUE}};' ],
		] );

		$this->add_control( 'card_radius', [
			'label'      => __( 'Card Border Radius', 'elite-sports-connect' ),
			'type'       => \Elementor\Controls_Manager::SLIDER,
			'size_units' => [ 'px' ],
			'range'      => [ 'px' => [ 'min' => 0, 'max' => 30 ] ],
			'default'    => [ 'unit' => 'px', 'size' => 16 ],
			'selectors'  => [ '{{WRAPPER}} .esc-coach-card' => 'border-radius: {{SIZE}}{{UNIT}};' ],
		] );

		$this->add_control( 'name_color', [
			'label'     => __( 'Coach Name Color', 'elite-sports-connect' ),
			'type'      => \Elementor\Controls_Manager::COLOR,
			'default'   => '#0A192F',
			'selectors' => [ '{{WRAPPER}} .esc-coach-card__name' => 'color: {{VALUE}};' ],
		] );

		$this->add_control( 'sport_tag_color', [
			'label'     => __( 'Sport Tag Background', 'elite-sports-connect' ),
			'type'      => \Elementor\Controls_Manager::COLOR,
			'default'   => '#F4A100',
			'selectors' => [ '{{WRAPPER}} .esc-tag' => 'background-color: {{VALUE}};' ],
		] );

		$this->add_control( 'sport_tag_text_color', [
			'label'     => __( 'Sport Tag Text Color', 'elite-sports-connect' ),
			'type'      => \Elementor\Controls_Manager::COLOR,
			'default'   => '#0A192F',
			'selectors' => [ '{{WRAPPER}} .esc-tag' => 'color: {{VALUE}};' ],
		] );

		$this->end_controls_section();

		// ── Style Tab: Section Heading ────────────────────────────────────────
		$this->start_controls_section( 'section_style_heading', [
			'label' => __( 'Section Heading', 'elite-sports-connect' ),
			'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
		] );

		$this->add_control( 'heading_color', [
			'label'     => __( 'Heading Color', 'elite-sports-connect' ),
			'type'      => \Elementor\Controls_Manager::COLOR,
			'default'   => '#0A192F',
			'selectors' => [ '{{WRAPPER}} .esc-directory__title' => 'color: {{VALUE}};' ],
		] );

		$this->add_group_control( \Elementor\Group_Control_Typography::get_type(), [
			'name'     => 'heading_typography',
			'selector' => '{{WRAPPER}} .esc-directory__title',
		] );

		$this->end_controls_section();
	}

	protected function render(): void {
		$settings = $this->get_settings_for_display();
		echo do_shortcode( sprintf(
			'[directory_coaches title="%s" posts_per_page="%d" columns="%s" sport_filter="%s" layout="%s" show_contact="%s"]',
			esc_attr( $settings['section_title'] ),
			absint( $settings['posts_per_page'] ),
			esc_attr( $settings['columns'] ),
			esc_attr( $settings['sport_filter'] ),
			esc_attr( $settings['layout'] ),
			esc_attr( $settings['show_contact_button'] )
		) );
	}
}

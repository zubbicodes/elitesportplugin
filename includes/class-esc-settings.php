<?php
/**
 * ESC_Settings — Registers the Plugin Menu and Settings Page
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ESC_Settings {

	public function __construct() {
		add_action( 'admin_menu', [ $this, 'register_menu' ] );
		add_action( 'admin_init', [ $this, 'register_settings' ] );
	}

	public function register_menu(): void {
		// Main Menu Page
		add_menu_page(
			__( 'Elite Sports Connect', 'elite-sports-connect' ),
			__( 'ES Connect', 'elite-sports-connect' ),
			'manage_options',
			'esc-settings',
			[ $this, 'render_settings_page' ],
			'dashicons-awards',
			5
		);

		// Rename main submenu
		add_submenu_page(
			'esc-settings',
			__( 'Settings', 'elite-sports-connect' ),
			__( 'Settings', 'elite-sports-connect' ),
			'manage_options',
			'esc-settings',
			[ $this, 'render_settings_page' ]
		);
	}

	public function register_settings(): void {
		register_setting( 'esc_settings_group', 'esc_support_email', [ 'type' => 'string', 'sanitize_callback' => 'sanitize_email' ] );
		register_setting( 'esc_settings_group', 'esc_smtp_host', [ 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ] );
		register_setting( 'esc_settings_group', 'esc_smtp_port', [ 'type' => 'integer', 'sanitize_callback' => 'absint' ] );
		register_setting( 'esc_settings_group', 'esc_smtp_username', [ 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ] );
		register_setting( 'esc_settings_group', 'esc_smtp_password', [ 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ] );
		register_setting( 'esc_settings_group', 'esc_enable_coach_form', [ 'type' => 'string', 'sanitize_callback' => [ $this, 'sanitize_checkbox' ], 'default' => '1' ] );
		register_setting( 'esc_settings_group', 'esc_enable_student_form', [ 'type' => 'string', 'sanitize_callback' => [ $this, 'sanitize_checkbox' ], 'default' => '1' ] );
		register_setting( 'esc_settings_group', 'esc_sports_list', [ 'type' => 'array', 'sanitize_callback' => [ $this, 'sanitize_sports_list' ], 'default' => ESC_Forms::get_default_sports_list() ] );

		add_settings_section(
			'esc_form_settings',
			__( 'Form Settings', 'elite-sports-connect' ),
			[ $this, 'render_form_settings_intro' ],
			'esc-settings'
		);

		add_settings_field( 'esc_enable_coach_form', __( 'Coach Application Form', 'elite-sports-connect' ), [ $this, 'render_checkbox_field' ], 'esc-settings', 'esc_form_settings', [ 'id' => 'esc_enable_coach_form', 'label' => __( 'Allow coach applications from the frontend', 'elite-sports-connect' ) ] );
		add_settings_field( 'esc_enable_student_form', __( 'Student Request Form', 'elite-sports-connect' ), [ $this, 'render_checkbox_field' ], 'esc-settings', 'esc_form_settings', [ 'id' => 'esc_enable_student_form', 'label' => __( 'Allow student requests from the frontend', 'elite-sports-connect' ) ] );
		add_settings_field( 'esc_sports_list', __( 'Sports List', 'elite-sports-connect' ), [ $this, 'render_sports_field' ], 'esc-settings', 'esc_form_settings', [ 'id' => 'esc_sports_list' ] );

		add_settings_section(
			'esc_main_settings',
			__( 'Email & SMTP Settings', 'elite-sports-connect' ),
			null,
			'esc-settings'
		);

		add_settings_field( 'esc_support_email', __( 'Support Email', 'elite-sports-connect' ), [ $this, 'render_text_field' ], 'esc-settings', 'esc_main_settings', [ 'id' => 'esc_support_email' ] );
		add_settings_field( 'esc_smtp_host', __( 'SMTP Host', 'elite-sports-connect' ), [ $this, 'render_text_field' ], 'esc-settings', 'esc_main_settings', [ 'id' => 'esc_smtp_host' ] );
		add_settings_field( 'esc_smtp_port', __( 'SMTP Port', 'elite-sports-connect' ), [ $this, 'render_text_field' ], 'esc-settings', 'esc_main_settings', [ 'id' => 'esc_smtp_port', 'type' => 'number' ] );
		add_settings_field( 'esc_smtp_username', __( 'SMTP Username', 'elite-sports-connect' ), [ $this, 'render_text_field' ], 'esc-settings', 'esc_main_settings', [ 'id' => 'esc_smtp_username' ] );
		add_settings_field( 'esc_smtp_password', __( 'SMTP Password', 'elite-sports-connect' ), [ $this, 'render_text_field' ], 'esc-settings', 'esc_main_settings', [ 'id' => 'esc_smtp_password', 'type' => 'password' ] );
	}

	public function render_text_field( array $args ): void {
		$id    = $args['id'];
		$type  = $args['type'] ?? 'text';
		$value = get_option( $id );
		echo '<input type="' . esc_attr( $type ) . '" id="' . esc_attr( $id ) . '" name="' . esc_attr( $id ) . '" value="' . esc_attr( $value ) . '" class="regular-text">';
	}

	public function render_checkbox_field( array $args ): void {
		$id      = $args['id'];
		$label   = $args['label'] ?? '';
		$checked = '1' === (string) get_option( $id, '1' );

		echo '<label for="' . esc_attr( $id ) . '">';
		echo '<input type="hidden" name="' . esc_attr( $id ) . '" value="0">';
		echo '<input type="checkbox" id="' . esc_attr( $id ) . '" name="' . esc_attr( $id ) . '" value="1" ' . checked( $checked, true, false ) . '>';
		echo ' ' . esc_html( $label );
		echo '</label>';
	}

	public function render_sports_field( array $args = [] ): void {
		$sports = get_option( 'esc_sports_list', ESC_Forms::get_default_sports_list() );
		if ( ! is_array( $sports ) ) {
			$sports = ESC_Forms::get_default_sports_list();
		}

		echo '<textarea id="esc_sports_list" name="esc_sports_list" rows="12" class="large-text code">' . esc_textarea( implode( "\n", $sports ) ) . '</textarea>';
		echo '<p class="description">' . esc_html__( 'Enter one sport per line. These values are used in both frontend forms and the coach directory filter.', 'elite-sports-connect' ) . '</p>';
	}

	public function render_form_settings_intro(): void {
		echo '<p>' . esc_html__( 'Control which forms accept new entries and manage the sports available across the plugin.', 'elite-sports-connect' ) . '</p>';
	}

	public function sanitize_checkbox( $value ): string {
		return empty( $value ) ? '0' : '1';
	}

	public function sanitize_sports_list( $value ): array {
		$raw_lines = is_array( $value ) ? $value : preg_split( '/\r\n|\r|\n/', (string) $value );
		$sports    = [];

		foreach ( $raw_lines as $line ) {
			$sport = sanitize_text_field( $line );
			if ( '' !== $sport ) {
				$sports[] = $sport;
			}
		}

		$sports = array_values( array_unique( $sports ) );

		return empty( $sports ) ? ESC_Forms::get_default_sports_list() : $sports;
	}

	public function render_settings_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Elite Sports Connect Settings', 'elite-sports-connect' ); ?></h1>
			<form action="options.php" method="post">
				<?php
				settings_fields( 'esc_settings_group' );
				do_settings_sections( 'esc-settings' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}
}

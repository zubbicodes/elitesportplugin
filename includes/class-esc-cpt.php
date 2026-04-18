<?php
/**
 * ESC_CPT — Registers Custom Post Types, Taxonomies, and Meta Boxes.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ESC_CPT {

	public function __construct() {
		add_action( 'init', [ __CLASS__, 'register_post_types' ] );
		add_action( 'init', [ __CLASS__, 'register_post_statuses' ] );
		add_action( 'add_meta_boxes', [ $this, 'register_meta_boxes' ] );
		add_action( 'save_post_coach',        [ $this, 'save_coach_meta' ], 10, 2 );
		add_action( 'save_post_student_lead', [ $this, 'save_student_meta' ], 10, 2 );
		add_action( 'admin_init', [ $this, 'handle_csv_export' ] );
		add_action( 'restrict_manage_posts', [ $this, 'render_export_button' ] );
		add_action( 'admin_footer-post.php', [ $this, 'render_status_admin_script' ] );
		add_action( 'admin_footer-post-new.php', [ $this, 'render_status_admin_script' ] );

		add_filter( 'manage_coach_posts_columns',       [ $this, 'coach_admin_columns' ] );
		add_action( 'manage_coach_posts_custom_column', [ $this, 'coach_admin_column_data' ], 10, 2 );

		add_filter( 'manage_student_lead_posts_columns',       [ $this, 'student_admin_columns' ] );
		add_action( 'manage_student_lead_posts_custom_column', [ $this, 'student_admin_column_data' ], 10, 2 );
	}

	public static function register_post_statuses(): void {
		register_post_status( 'esc_rejected', [
			'label'                     => _x( 'Rejected', 'post status', 'elite-sports-connect' ),
			'public'                    => false,
			'internal'                  => false,
			'protected'                 => true,
			'private'                   => true,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			/* translators: %s: number of rejected items */
			'label_count'               => _n_noop( 'Rejected <span class="count">(%s)</span>', 'Rejected <span class="count">(%s)</span>', 'elite-sports-connect' ),
		] );
	}

	// ─── Post Type Registration ───────────────────────────────────────────────

	public static function register_post_types(): void {
		// Coach CPT
			register_post_type( 'coach', [
			'labels'              => [
				'name'               => __( 'Coaches',           'elite-sports-connect' ),
				'singular_name'      => __( 'Coach',             'elite-sports-connect' ),
				'add_new_item'       => __( 'Add New Coach',      'elite-sports-connect' ),
				'edit_item'          => __( 'Edit Coach',         'elite-sports-connect' ),
				'new_item'           => __( 'New Coach',          'elite-sports-connect' ),
				'view_item'          => __( 'View Coach',         'elite-sports-connect' ),
				'search_items'       => __( 'Search Coaches',     'elite-sports-connect' ),
				'not_found'          => __( 'No coaches found.',  'elite-sports-connect' ),
				'not_found_in_trash' => __( 'No coaches in trash.', 'elite-sports-connect' ),
			],
				'public'              => true,
				'publicly_queryable'  => false,
				'has_archive'         => false,
				'exclude_from_search' => true,
				'show_in_rest'        => true,
				'show_in_menu'        => 'esc-settings',
				'menu_icon'           => 'dashicons-awards',
				'menu_position'       => 50,
				'supports'            => [ 'title', 'editor', 'thumbnail', 'custom-fields' ],
				'rewrite'             => false,
			] );

		// Student Lead CPT (admin-only — NOT publicly queryable)
		register_post_type( 'student_lead', [
			'labels'              => [
				'name'               => __( 'Student Leads',           'elite-sports-connect' ),
				'singular_name'      => __( 'Student Lead',            'elite-sports-connect' ),
				'add_new_item'       => __( 'Add New Student Lead',     'elite-sports-connect' ),
				'edit_item'          => __( 'Edit Student Lead',        'elite-sports-connect' ),
				'new_item'           => __( 'New Student Lead',         'elite-sports-connect' ),
				'not_found'          => __( 'No leads found.',         'elite-sports-connect' ),
				'not_found_in_trash' => __( 'No leads in trash.',      'elite-sports-connect' ),
			],
			'public'              => false,
			'publicly_queryable'  => false,
			'show_ui'             => true,
			'show_in_menu'        => 'esc-settings',
			'show_in_rest'        => false,
			'menu_icon'           => 'dashicons-groups',
			'menu_position'       => 51,
			'supports'            => [ 'title' ],
			'capability_type'     => 'post',
		] );
	}

	// ─── Meta Boxes ──────────────────────────────────────────────────────────

	public function register_meta_boxes(): void {
		add_meta_box(
			'esc_coach_details',
			__( 'Coach Details', 'elite-sports-connect' ),
			[ $this, 'render_coach_meta_box' ],
			'coach',
			'normal',
			'high'
		);

		add_meta_box(
			'esc_student_details',
			__( 'Student Lead Details', 'elite-sports-connect' ),
			[ $this, 'render_student_meta_box' ],
			'student_lead',
			'normal',
			'high'
		);
	}

	public function render_coach_meta_box( WP_Post $post ): void {
		wp_nonce_field( 'esc_coach_meta_nonce', 'esc_coach_meta_nonce' );
		$fields = self::get_coach_meta( $post->ID );
		?>
		<table class="form-table esc-meta-table">
			<tr>
				<th><label for="esc_email"><?php esc_html_e( 'Email', 'elite-sports-connect' ); ?></label></th>
				<td><input type="email" id="esc_email" name="esc_email" value="<?php echo esc_attr( $fields['email'] ); ?>" class="regular-text"></td>
			</tr>
			<tr>
				<th><label for="esc_phone"><?php esc_html_e( 'Phone', 'elite-sports-connect' ); ?></label></th>
				<td><input type="text" id="esc_phone" name="esc_phone" value="<?php echo esc_attr( $fields['phone'] ); ?>" class="regular-text"></td>
			</tr>
			<tr>
				<th><label for="esc_location"><?php esc_html_e( 'Location / City', 'elite-sports-connect' ); ?></label></th>
				<td><input type="text" id="esc_location" name="esc_location" value="<?php echo esc_attr( $fields['location'] ); ?>" class="regular-text"></td>
			</tr>
			<tr>
				<th><label for="esc_postal_code"><?php esc_html_e( 'Postal Code', 'elite-sports-connect' ); ?></label></th>
				<td><input type="text" id="esc_postal_code" name="esc_postal_code" value="<?php echo esc_attr( $fields['postal_code'] ); ?>" class="regular-text"></td>
			</tr>
			<tr>
				<th><label for="esc_sport"><?php esc_html_e( 'Sport', 'elite-sports-connect' ); ?></label></th>
				<td>
					<select id="esc_sport" name="esc_sport">
						<option value=""><?php esc_html_e( '— Select Sport —', 'elite-sports-connect' ); ?></option>
						<?php foreach ( ESC_Forms::get_sports_list() as $sport ) : ?>
							<option value="<?php echo esc_attr( $sport ); ?>" <?php selected( $fields['sport'], $sport ); ?>><?php echo esc_html( $sport ); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th><label for="esc_experience"><?php esc_html_e( 'Experience Level', 'elite-sports-connect' ); ?></label></th>
				<td>
					<select id="esc_experience" name="esc_experience">
						<option value=""><?php esc_html_e( '— Select Level —', 'elite-sports-connect' ); ?></option>
						<?php foreach ( ESC_Forms::get_experience_levels() as $level ) : ?>
							<option value="<?php echo esc_attr( $level ); ?>" <?php selected( $fields['experience'], $level ); ?>><?php echo esc_html( $level ); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th><label for="esc_website"><?php esc_html_e( 'Website (optional)', 'elite-sports-connect' ); ?></label></th>
				<td><input type="url" id="esc_website" name="esc_website" value="<?php echo esc_attr( $fields['website'] ); ?>" class="regular-text"></td>
			</tr>
		</table>
		<?php
	}

	public function render_student_meta_box( WP_Post $post ): void {
		wp_nonce_field( 'esc_student_meta_nonce', 'esc_student_meta_nonce' );
		$fields = self::get_student_meta( $post->ID );
		?>
		<table class="form-table esc-meta-table">
			<tr>
				<th><label for="esc_s_email"><?php esc_html_e( 'Email', 'elite-sports-connect' ); ?></label></th>
				<td><input type="email" id="esc_s_email" name="esc_s_email" value="<?php echo esc_attr( $fields['email'] ); ?>" class="regular-text"></td>
			</tr>
			<tr>
				<th><label for="esc_s_phone"><?php esc_html_e( 'Phone', 'elite-sports-connect' ); ?></label></th>
				<td><input type="text" id="esc_s_phone" name="esc_s_phone" value="<?php echo esc_attr( $fields['phone'] ); ?>" class="regular-text"></td>
			</tr>
			<tr>
				<th><label for="esc_s_location"><?php esc_html_e( 'Location / City', 'elite-sports-connect' ); ?></label></th>
				<td><input type="text" id="esc_s_location" name="esc_s_location" value="<?php echo esc_attr( $fields['location'] ); ?>" class="regular-text"></td>
			</tr>
			<tr>
				<th><label for="esc_s_sport"><?php esc_html_e( 'Preferred Sport', 'elite-sports-connect' ); ?></label></th>
				<td>
					<select id="esc_s_sport" name="esc_s_sport">
						<option value=""><?php esc_html_e( '— Select Sport —', 'elite-sports-connect' ); ?></option>
						<?php foreach ( ESC_Forms::get_sports_list() as $sport ) : ?>
							<option value="<?php echo esc_attr( $sport ); ?>" <?php selected( $fields['preferred_sport'], $sport ); ?>><?php echo esc_html( $sport ); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th><label for="esc_s_looking_for"><?php esc_html_e( 'Looking For', 'elite-sports-connect' ); ?></label></th>
				<td><textarea id="esc_s_looking_for" name="esc_s_looking_for" rows="4" class="large-text"><?php echo esc_textarea( $fields['looking_for'] ); ?></textarea></td>
			</tr>
		</table>
		<?php
	}

	// ─── Save Meta ───────────────────────────────────────────────────────────

	public function save_coach_meta( int $post_id ): void {
		if ( ! isset( $_POST['esc_coach_meta_nonce'] ) ) {
			return;
		}
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['esc_coach_meta_nonce'] ) ), 'esc_coach_meta_nonce' ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$fields = [
			'_esc_email'       => 'sanitize_email',
			'_esc_phone'       => 'sanitize_text_field',
			'_esc_location'    => 'sanitize_text_field',
			'_esc_postal_code' => 'sanitize_text_field',
			'_esc_sport'       => 'sanitize_text_field',
			'_esc_experience'  => 'sanitize_text_field',
			'_esc_website'     => 'esc_url_raw',
		];

		$post_map = [
			'_esc_email'       => 'esc_email',
			'_esc_phone'       => 'esc_phone',
			'_esc_location'    => 'esc_location',
			'_esc_postal_code' => 'esc_postal_code',
			'_esc_sport'       => 'esc_sport',
			'_esc_experience'  => 'esc_experience',
			'_esc_website'     => 'esc_website',
		];

		foreach ( $fields as $meta_key => $sanitize_fn ) {
			$post_key = $post_map[ $meta_key ];
			if ( isset( $_POST[ $post_key ] ) ) {
				$value = $sanitize_fn( wp_unslash( $_POST[ $post_key ] ) );
				update_post_meta( $post_id, $meta_key, $value );
			}
		}
	}

	public function save_student_meta( int $post_id ): void {
		if ( ! isset( $_POST['esc_student_meta_nonce'] ) ) {
			return;
		}
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['esc_student_meta_nonce'] ) ), 'esc_student_meta_nonce' ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$text_fields = [ 'esc_s_email', 'esc_s_phone', 'esc_s_location', 'esc_s_sport', 'esc_s_looking_for' ];
		$meta_map = [
			'esc_s_email'       => '_esc_s_email',
			'esc_s_phone'       => '_esc_s_phone',
			'esc_s_location'    => '_esc_s_location',
			'esc_s_sport'       => '_esc_s_preferred_sport',
			'esc_s_looking_for' => '_esc_s_looking_for',
		];

		foreach ( $text_fields as $field ) {
			if ( isset( $_POST[ $field ] ) ) {
				$fn    = ( $field === 'esc_s_email' ) ? 'sanitize_email' : 'sanitize_textarea_field';
				$value = $fn( wp_unslash( $_POST[ $field ] ) );
				update_post_meta( $post_id, $meta_map[ $field ], $value );
			}
		}
	}

	// ─── Admin Columns ───────────────────────────────────────────────────────

	public function coach_admin_columns( array $columns ): array {
		return [
			'cb'         => $columns['cb'],
			'title'      => __( 'Coach Name',       'elite-sports-connect' ),
			'esc_sport'  => __( 'Sport',             'elite-sports-connect' ),
			'esc_exp'    => __( 'Experience',        'elite-sports-connect' ),
			'esc_loc'    => __( 'Location',          'elite-sports-connect' ),
			'esc_email'  => __( 'Email',             'elite-sports-connect' ),
			'date'       => __( 'Submitted',         'elite-sports-connect' ),
		];
	}

	public function coach_admin_column_data( string $column, int $post_id ): void {
		switch ( $column ) {
			case 'esc_sport':
				echo esc_html( get_post_meta( $post_id, '_esc_sport', true ) );
				break;
			case 'esc_exp':
				echo esc_html( get_post_meta( $post_id, '_esc_experience', true ) );
				break;
			case 'esc_loc':
				echo esc_html( get_post_meta( $post_id, '_esc_location', true ) );
				break;
			case 'esc_email':
				$email = get_post_meta( $post_id, '_esc_email', true );
				echo '<a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a>';
				break;
		}
	}

	public function student_admin_columns( array $columns ): array {
		return [
			'cb'              => $columns['cb'],
			'title'           => __( 'Student Name',     'elite-sports-connect' ),
			'esc_s_sport'     => __( 'Preferred Sport',  'elite-sports-connect' ),
			'esc_s_location'  => __( 'Location',         'elite-sports-connect' ),
			'esc_s_email'     => __( 'Email',            'elite-sports-connect' ),
			'esc_s_phone'     => __( 'Phone',            'elite-sports-connect' ),
			'date'            => __( 'Submitted',        'elite-sports-connect' ),
		];
	}

	public function student_admin_column_data( string $column, int $post_id ): void {
		switch ( $column ) {
			case 'esc_s_sport':
				echo esc_html( get_post_meta( $post_id, '_esc_s_preferred_sport', true ) );
				break;
			case 'esc_s_location':
				echo esc_html( get_post_meta( $post_id, '_esc_s_location', true ) );
				break;
			case 'esc_s_email':
				$email = get_post_meta( $post_id, '_esc_s_email', true );
				echo '<a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a>';
				break;
			case 'esc_s_phone':
				echo esc_html( get_post_meta( $post_id, '_esc_s_phone', true ) );
				break;
		}
	}

	public function render_export_button( string $post_type ): void {
		if ( ! in_array( $post_type, [ 'coach', 'student_lead' ], true ) ) {
			return;
		}

		$query_args = wp_unslash( $_GET );
		unset( $query_args['esc_export_csv'], $query_args['_wpnonce'] );

		$query_args['esc_export_csv'] = '1';
		$query_args['_wpnonce']       = wp_create_nonce( 'esc_export_' . $post_type );

		echo '<a href="' . esc_url( add_query_arg( $query_args, admin_url( 'edit.php' ) ) ) . '" class="button" style="margin-left:8px;">' . esc_html__( 'Export CSV', 'elite-sports-connect' ) . '</a>';
	}

	public function handle_csv_export(): void {
		if ( empty( $_GET['esc_export_csv'] ) || empty( $_GET['post_type'] ) ) {
			return;
		}

		$post_type = sanitize_key( wp_unslash( $_GET['post_type'] ) );
		if ( ! in_array( $post_type, [ 'coach', 'student_lead' ], true ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_others_posts' ) ) {
			wp_die( esc_html__( 'You are not allowed to export these entries.', 'elite-sports-connect' ) );
		}

		check_admin_referer( 'esc_export_' . $post_type );

		$query_args = [
			'post_type'      => $post_type,
			'post_status'    => isset( $_GET['post_status'] ) && '' !== $_GET['post_status']
				? sanitize_key( wp_unslash( $_GET['post_status'] ) )
				: [ 'pending', 'publish', 'draft', 'future', 'private', 'trash', 'esc_rejected' ],
			'posts_per_page' => -1,
			'orderby'        => 'date',
			'order'          => 'DESC',
		];

		if ( ! empty( $_GET['s'] ) ) {
			$query_args['s'] = sanitize_text_field( wp_unslash( $_GET['s'] ) );
		}

		$posts = get_posts( $query_args );

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=' . $post_type . '-export-' . gmdate( 'Y-m-d-H-i-s' ) . '.csv' );

		$output = fopen( 'php://output', 'w' );

		if ( 'coach' === $post_type ) {
			fputcsv( $output, [ 'Name', 'Email', 'Phone', 'Location', 'Postal Code', 'Sport', 'Experience', 'Website', 'Status', 'Submitted' ] );
			foreach ( $posts as $post ) {
				$meta = self::get_coach_meta( $post->ID );
				fputcsv( $output, [
					$post->post_title,
					$meta['email'],
					$meta['phone'],
					$meta['location'],
					$meta['postal_code'],
					$meta['sport'],
					$meta['experience'],
					$meta['website'],
					$this->get_status_label( $post->post_status, 'coach' ),
					get_the_date( 'Y-m-d H:i:s', $post ),
				] );
			}
		} else {
			fputcsv( $output, [ 'Name', 'Email', 'Phone', 'Location', 'Preferred Sport', 'Looking For', 'Status', 'Submitted' ] );
			foreach ( $posts as $post ) {
				$meta = self::get_student_meta( $post->ID );
				fputcsv( $output, [
					$post->post_title,
					$meta['email'],
					$meta['phone'],
					$meta['location'],
					$meta['preferred_sport'],
					$meta['looking_for'],
					$this->get_status_label( $post->post_status, 'student_lead' ),
					get_the_date( 'Y-m-d H:i:s', $post ),
				] );
			}
		}

		fclose( $output );
		exit;
	}

	public function render_status_admin_script(): void {
		$screen = get_current_screen();
		if ( ! $screen || ! in_array( $screen->post_type, [ 'coach', 'student_lead' ], true ) ) {
			return;
		}

		$post_type = $screen->post_type;
		?>
		<script>
		jQuery(function ($) {
			const postType = <?php echo wp_json_encode( $post_type ); ?>;
			const statusSelect = $('#post_status');
			const statusDisplay = $('#post-status-display');
			const publishButton = $('#publish');

			if (!statusSelect.length) {
				return;
			}

			function upsertOption(value, label) {
				const existing = statusSelect.find('option[value="' + value + '"]');
				if (existing.length) {
					existing.text(label);
				} else {
					statusSelect.append($('<option>', { value: value, text: label }));
				}
			}

			if (postType === 'student_lead') {
				upsertOption('pending', 'Under Review');
				upsertOption('publish', 'Approved');
				upsertOption('esc_rejected', 'Rejected');
			}

			if (postType === 'coach') {
				upsertOption('publish', 'Approved');
				upsertOption('esc_rejected', 'Rejected');
			}

			function syncUi() {
				const current = statusSelect.val();
				let display = '';
				let button = 'Save';

				if (postType === 'student_lead') {
					if (current === 'publish') {
						display = 'Approved';
						button = 'Approve';
					} else if (current === 'esc_rejected') {
						display = 'Rejected';
						button = 'Reject';
					} else {
						display = 'Under Review';
						button = 'Save';
					}
				} else {
					if (current === 'publish') {
						display = 'Approved';
						button = 'Approve';
					} else if (current === 'esc_rejected') {
						display = 'Rejected';
						button = 'Reject';
					} else if (current === 'pending') {
						display = 'Pending Review';
						button = 'Save';
					}
				}

				if (display && statusDisplay.length) {
					statusDisplay.text(display);
				}
				if (publishButton.length) {
					publishButton.val(button).text(button);
				}
			}

			statusSelect.on('change', syncUi);
			syncUi();
		});
		</script>
		<?php
	}

	private function get_status_label( string $status, string $post_type ): string {
		if ( 'student_lead' === $post_type ) {
			if ( 'pending' === $status ) {
				return __( 'Under Review', 'elite-sports-connect' );
			}
			if ( 'publish' === $status ) {
				return __( 'Approved', 'elite-sports-connect' );
			}
		}

		if ( 'coach' === $post_type && 'publish' === $status ) {
			return __( 'Approved', 'elite-sports-connect' );
		}

		if ( 'esc_rejected' === $status ) {
			return __( 'Rejected', 'elite-sports-connect' );
		}

		$object = get_post_status_object( $status );
		return $object && ! empty( $object->label ) ? $object->label : $status;
	}

	// ─── Helper: Get Coach Meta ───────────────────────────────────────────────

	public static function get_coach_meta( int $post_id ): array {
		return [
			'email'       => get_post_meta( $post_id, '_esc_email',       true ),
			'phone'       => get_post_meta( $post_id, '_esc_phone',       true ),
			'location'    => get_post_meta( $post_id, '_esc_location',    true ),
			'postal_code' => get_post_meta( $post_id, '_esc_postal_code', true ),
			'sport'       => get_post_meta( $post_id, '_esc_sport',       true ),
			'experience'  => get_post_meta( $post_id, '_esc_experience',  true ),
			'website'     => get_post_meta( $post_id, '_esc_website',     true ),
			'photo_id'    => get_post_meta( $post_id, '_esc_photo_id',    true ),
		];
	}

	public static function get_student_meta( int $post_id ): array {
		return [
			'email'          => get_post_meta( $post_id, '_esc_s_email',          true ),
			'phone'          => get_post_meta( $post_id, '_esc_s_phone',          true ),
			'location'       => get_post_meta( $post_id, '_esc_s_location',       true ),
			'preferred_sport'=> get_post_meta( $post_id, '_esc_s_preferred_sport',true ),
			'looking_for'    => get_post_meta( $post_id, '_esc_s_looking_for',    true ),
		];
	}
}

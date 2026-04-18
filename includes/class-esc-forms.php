<?php
/**
 * ESC_Forms — Handles all frontend form submissions securely.
 *
 * Security: Nonce verification → MIME/size checks → sanitization → insert as pending.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ESC_Forms {

	// Allowed MIME types for photo uploads.
	private const ALLOWED_MIME_TYPES = [ 'image/jpeg', 'image/png', 'image/webp' ];
	// Max upload size in bytes (5 MB).
	private const MAX_UPLOAD_SIZE = 5242880;

	public function __construct() {
		add_action( 'init', [ __CLASS__, 'register_defaults' ], 1 );

		// admin_post_* is WordPress's dedicated HTML-form handler endpoint
		// (wp-admin/admin-post.php). It is the most reliable mechanism for
		// processing frontend forms: no hook-within-hook timing issues, all WP
		// functions available, redirect works correctly.
		// 'nopriv' variant fires for non-logged-in (guest) visitors.
		add_action( 'admin_post_nopriv_esc_student_form', [ $this, 'handle_student_lead' ] );
		add_action( 'admin_post_esc_student_form',        [ $this, 'handle_student_lead' ] );
		add_action( 'admin_post_nopriv_esc_coach_form',   [ $this, 'handle_coach_registration' ] );
		add_action( 'admin_post_esc_coach_form',          [ $this, 'handle_coach_registration' ] );
	}

	// ─── Coach Registration ───────────────────────────────────────────────────

	public function handle_coach_registration(): void {
		if ( ! self::is_form_enabled( 'coach' ) ) {
			$this->redirect_with_error( self::get_disabled_form_message( 'coach' ) );
			return;
		}

		// 1. Nonce verification.
		if ( ! isset( $_POST['esc_coach_nonce'] ) ||
			! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['esc_coach_nonce'] ) ), 'esc_coach_form' ) ) {
			$this->redirect_with_error( 'Security check failed. Please try again.' );
			return;
		}

		// 2. Honeypot check.
		if ( ! empty( $_POST['esc_website_hp'] ) ) {
			$this->redirect_with_error( 'Submission rejected.' );
			return;
		}

		// 3. Sanitize fields.
		$name       = sanitize_text_field( wp_unslash( $_POST['esc_name']       ?? '' ) );
		$email      = sanitize_email( wp_unslash( $_POST['esc_email']           ?? '' ) );
		$phone      = sanitize_text_field( wp_unslash( $_POST['esc_phone']      ?? '' ) );
		$location   = sanitize_text_field( wp_unslash( $_POST['esc_location']   ?? '' ) );
		$postal     = sanitize_text_field( wp_unslash( $_POST['esc_postal_code']?? '' ) );
		$sport      = sanitize_text_field( wp_unslash( $_POST['esc_sport']      ?? '' ) );
		$experience = sanitize_text_field( wp_unslash( $_POST['esc_experience'] ?? '' ) );
		$bio        = sanitize_textarea_field( wp_unslash( $_POST['esc_bio']    ?? '' ) );
		$website    = esc_url_raw( wp_unslash( $_POST['esc_website']            ?? '' ) );

		// 4. Required field validation.
		$required = compact( 'name', 'email', 'sport', 'experience' );
		foreach ( $required as $field => $value ) {
			if ( empty( $value ) ) {
				$this->redirect_with_error( 'Please fill in all required fields.' );
				return;
			}
		}
		if ( ! is_email( $email ) ) {
			$this->redirect_with_error( 'Please enter a valid email address.' );
			return;
		}

		// Validate sport and experience against allowed lists.
		if ( ! in_array( $sport, self::get_sports_list(), true ) ) {
			$this->redirect_with_error( 'Invalid sport selection.' );
			return;
		}
		if ( ! in_array( $experience, self::get_experience_levels(), true ) ) {
			$this->redirect_with_error( 'Invalid experience level.' );
			return;
		}

		// 5. Insert post as 'pending'.
		$post_id = wp_insert_post( [
			'post_title'   => $name,
			'post_content' => $bio,
			'post_status'  => 'pending',
			'post_type'    => 'coach',
		], true );

		if ( is_wp_error( $post_id ) ) {
			$this->redirect_with_error( 'Failed to save your application. Please try again.' );
			return;
		}

		// 6. Save meta.
		update_post_meta( $post_id, '_esc_email',       $email );
		update_post_meta( $post_id, '_esc_phone',       $phone );
		update_post_meta( $post_id, '_esc_location',    $location );
		update_post_meta( $post_id, '_esc_postal_code', $postal );
		update_post_meta( $post_id, '_esc_sport',       $sport );
		update_post_meta( $post_id, '_esc_experience',  $experience );
		update_post_meta( $post_id, '_esc_website',     $website );

		// 7. Handle photo upload.
		if ( ! empty( $_FILES['esc_photo']['name'] ) ) {
			$photo_id = $this->handle_photo_upload( $post_id );
			if ( is_wp_error( $photo_id ) ) {
				// Non-fatal: log but proceed.
				error_log( '[ESC] Photo upload error for post ' . $post_id . ': ' . $photo_id->get_error_message() );
			} else {
				update_post_meta( $post_id, '_esc_photo_id', $photo_id );
				set_post_thumbnail( $post_id, $photo_id );
			}
		}

		// 8. Trigger confirmation emails.
		do_action( 'esc_coach_registered', $post_id, $email, $name );

		$this->redirect_with_success( 'coach' );
	}

	// ─── Student Lead ─────────────────────────────────────────────────────────

	public function handle_student_lead(): void {
		if ( ! self::is_form_enabled( 'student' ) ) {
			$this->redirect_with_error( self::get_disabled_form_message( 'student' ) );
			return;
		}

		// 1. Nonce verification.
		if ( ! isset( $_POST['esc_student_nonce'] ) ||
			! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['esc_student_nonce'] ) ), 'esc_student_form' ) ) {
			$this->redirect_with_error( 'Security check failed. Please try again.' );
			return;
		}

		// 2. Honeypot.
		if ( ! empty( $_POST['esc_website_hp'] ) ) {
			$this->redirect_with_error( 'Submission rejected.' );
			return;
		}

		// 3. Sanitize.
		$name          = sanitize_text_field( wp_unslash( $_POST['esc_name']           ?? '' ) );
		$email         = sanitize_email( wp_unslash( $_POST['esc_email']               ?? '' ) );
		$phone         = sanitize_text_field( wp_unslash( $_POST['esc_phone']          ?? '' ) );
		$location      = sanitize_text_field( wp_unslash( $_POST['esc_location']       ?? '' ) );
		$sport         = sanitize_text_field( wp_unslash( $_POST['esc_preferred_sport']?? '' ) );
		$looking_for   = sanitize_textarea_field( wp_unslash( $_POST['esc_looking_for']?? '' ) );

		// 4. Required field validation.
		foreach ( [ $name, $email, $sport ] as $val ) {
			if ( empty( $val ) ) {
				$this->redirect_with_error( 'Please fill in all required fields.' );
				return;
			}
		}
		if ( ! is_email( $email ) ) {
			$this->redirect_with_error( 'Please enter a valid email address.' );
			return;
		}
		if ( ! in_array( $sport, self::get_sports_list(), true ) ) {
			$this->redirect_with_error( 'Invalid sport selection.' );
			return;
		}

		// 5. Insert as 'pending'. WordPress requires publish_posts capability to
		//    use 'publish' status; guest users fail that check and the status is
		//    silently downgraded to 'draft'. 'pending' has no such capability
		//    guard so guest submissions are always created correctly, and entries
		//    appear in the admin under Pending / All views.
		$post_id = wp_insert_post( [
			'post_title'  => $name,
			'post_status' => 'pending',
			'post_type'   => 'student_lead',
		], true );

		if ( is_wp_error( $post_id ) ) {
			$this->redirect_with_error( 'Failed to save your request. Please try again.' );
			return;
		}

		// 6. Save meta.
		update_post_meta( $post_id, '_esc_s_email',           $email );
		update_post_meta( $post_id, '_esc_s_phone',           $phone );
		update_post_meta( $post_id, '_esc_s_location',        $location );
		update_post_meta( $post_id, '_esc_s_preferred_sport', $sport );
		update_post_meta( $post_id, '_esc_s_looking_for',     $looking_for );

		// 7. Trigger confirmation emails.
		do_action( 'esc_student_lead_submitted', $post_id, $email, $name );

		$this->redirect_with_success( 'student' );
	}

	// ─── Photo Upload ─────────────────────────────────────────────────────────

	/**
	 * Validates and uploads the coach photo.
	 *
	 * @param int $post_id Parent post ID.
	 * @return int|WP_Error Attachment ID on success, WP_Error on failure.
	 */
	private function handle_photo_upload( int $post_id ) {
		if ( ! function_exists( 'wp_handle_upload' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}
		if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
			require_once ABSPATH . 'wp-admin/includes/image.php';
		}
		if ( ! function_exists( 'media_handle_upload' ) ) {
			require_once ABSPATH . 'wp-admin/includes/media.php';
		}

		$file = $_FILES['esc_photo'];

		// Validate size.
		if ( $file['size'] > self::MAX_UPLOAD_SIZE ) {
			return new WP_Error( 'esc_upload_size', 'Photo must be under 5 MB.' );
		}

		// Validate MIME type using finfo (server-side, not just extension).
		$finfo = new finfo( FILEINFO_MIME_TYPE );
		$mime  = $finfo->file( $file['tmp_name'] );
		if ( ! in_array( $mime, self::ALLOWED_MIME_TYPES, true ) ) {
			return new WP_Error( 'esc_upload_mime', 'Only JPG, PNG, or WebP images are allowed.' );
		}

		$upload_overrides = [ 'test_form' => false ];
		$uploaded = wp_handle_upload( $file, $upload_overrides );

		if ( isset( $uploaded['error'] ) ) {
			return new WP_Error( 'esc_upload_error', $uploaded['error'] );
		}

		$attachment = [
			'guid'           => $uploaded['url'],
			'post_mime_type' => $uploaded['type'],
			'post_title'     => sanitize_file_name( $file['name'] ),
			'post_status'    => 'inherit',
		];

		$attach_id   = wp_insert_attachment( $attachment, $uploaded['file'], $post_id );
		$attach_data = wp_generate_attachment_metadata( $attach_id, $uploaded['file'] );
		wp_update_attachment_metadata( $attach_id, $attach_data );

		return $attach_id;
	}

	// ─── Redirect Helpers ─────────────────────────────────────────────────────

	/**
	 * Returns the absolute URL of the form page to redirect back to.
	 *
	 * The form POSTs to admin-post.php, so we cannot derive the originating
	 * page from the current request. Instead each form includes a hidden
	 * esc_return_url field populated with get_permalink() at render time.
	 * We validate it is on the same site before using it.
	 */
	private function get_return_url(): string {
		$candidates = [];

		if ( ! empty( $_POST['esc_return_url'] ) ) {
			$candidates[] = esc_url_raw( wp_unslash( $_POST['esc_return_url'] ) );
		}

		$referer = wp_get_referer();
		if ( $referer ) {
			$candidates[] = esc_url_raw( $referer );
		}

		$home_host = wp_parse_url( home_url(), PHP_URL_HOST );

		foreach ( $candidates as $candidate ) {
			if ( empty( $candidate ) ) {
				continue;
			}

			$candidate_host = wp_parse_url( $candidate, PHP_URL_HOST );
			if ( $candidate_host && $home_host && strtolower( $candidate_host ) !== strtolower( $home_host ) ) {
				continue;
			}

			return remove_query_arg( [ 'esc_status', 'esc_form', 'esc_message' ], $candidate );
		}

		return home_url( '/' );
	}

	private function redirect_with_error( string $message ): void {
		$url = add_query_arg( [
			'esc_status'  => 'error',
			'esc_message' => rawurlencode( $message ),
		], $this->get_return_url() );
		wp_safe_redirect( $url );
		exit;
	}

	private function redirect_with_success( string $form_type ): void {
		$url = add_query_arg( [
			'esc_status' => 'success',
			'esc_form'   => $form_type,
		], $this->get_return_url() );
		wp_safe_redirect( $url );
		exit;
	}

	// ─── Static Data Lists ────────────────────────────────────────────────────

	public static function get_sports_list(): array {
		$sports = get_option( 'esc_sports_list', [] );

		if ( ! is_array( $sports ) || empty( $sports ) ) {
			$sports = self::get_default_sports_list();
		}

		return array_values( array_unique( array_filter( array_map( 'strval', $sports ) ) ) );
	}

	public static function get_default_sports_list(): array {
		return [
			'American Football',
			'Athletics / Track & Field',
			'Baseball',
			'Basketball',
			'Boxing',
			'Cricket',
			'Cross Country',
			'Cycling',
			'Golf',
			'Gymnastics',
			'Hockey',
			'Lacrosse',
			'MMA / Martial Arts',
			'Rowing',
			'Rugby',
			'Soccer / Football',
			'Swimming',
			'Tennis',
			'Triathlon',
			'Volleyball',
			'Wrestling',
			'Other',
		];
	}

	public static function get_forms_settings(): array {
		return [
			'coach_enabled'   => '1' === (string) get_option( 'esc_enable_coach_form', '1' ),
			'student_enabled' => '1' === (string) get_option( 'esc_enable_student_form', '1' ),
		];
	}

	public static function is_form_enabled( string $form_type ): bool {
		$settings = self::get_forms_settings();

		if ( 'coach' === $form_type ) {
			return $settings['coach_enabled'];
		}

		if ( 'student' === $form_type ) {
			return $settings['student_enabled'];
		}

		return false;
	}

	public static function get_disabled_form_message( string $form_type ): string {
		if ( 'coach' === $form_type ) {
			return __( 'Coach applications are currently disabled. Please check back later.', 'elite-sports-connect' );
		}

		return __( 'Student requests are currently disabled. Please check back later.', 'elite-sports-connect' );
	}

	public static function register_defaults(): void {
		if ( false === get_option( 'esc_sports_list', false ) ) {
			add_option( 'esc_sports_list', self::get_default_sports_list() );
		}

		if ( false === get_option( 'esc_enable_coach_form', false ) ) {
			add_option( 'esc_enable_coach_form', '1' );
		}

		if ( false === get_option( 'esc_enable_student_form', false ) ) {
			add_option( 'esc_enable_student_form', '1' );
		}
	}

	public static function get_experience_levels(): array {
		return [
			'Beginner',
			'Intermediate',
			'Advanced',
			'Professional',
			'Elite',
		];
	}
}

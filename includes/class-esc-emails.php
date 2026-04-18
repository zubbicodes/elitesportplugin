<?php
/**
 * ESC_Emails — All plugin email notifications.
 *
 * Hooks:
 *   1. esc_coach_registered         → confirmation to coach + admin notification.
 *   2. esc_student_lead_submitted   → confirmation to student + admin notification.
 *   3. transition_post_status       → "Welcome to the Platform" when coach goes pending → publish.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ESC_Emails {

	public function __construct() {
		add_action( 'esc_coach_registered',       [ $this, 'on_coach_registered' ], 10, 3 );
		add_action( 'esc_student_lead_submitted',  [ $this, 'on_student_submitted' ], 10, 3 );
		add_action( 'transition_post_status',      [ $this, 'handle_status_transition' ], 10, 3 );
		add_action( 'phpmailer_init',              [ $this, 'configure_phpmailer' ] );
	}

	// ─── Coach Registered ────────────────────────────────────────────────────

	public function on_coach_registered( int $post_id, string $email, string $name ): void {
		$admin_email = $this->get_notification_email();
		$site_name   = get_bloginfo( 'name' );

		// ── Confirmation to coach ──
		$subject = sprintf( __( '[%s] Your coaching application was received', 'elite-sports-connect' ), $site_name );
		$body    = $this->wrap_template(
			__( 'Application Received', 'elite-sports-connect' ),
			sprintf(
				/* translators: 1: coach first name */
				__( 'Hi %1$s,', 'elite-sports-connect' ),
				esc_html( $name )
			),
			__( "Thank you for submitting your coaching application to <strong>{$site_name}</strong>. Our team will review your profile and notify you once it is approved and live on the platform.", 'elite-sports-connect' ),
			__( 'You will receive a separate email once your profile goes live.', 'elite-sports-connect' )
		);
		$this->send( $email, $subject, $body );

		// ── Admin notification ──
		$admin_subject = sprintf( __( '[%s] New Coach Application — %s', 'elite-sports-connect' ), $site_name, $name );
		$edit_url      = get_edit_post_link( $post_id, 'raw' );
		$admin_body    = $this->wrap_template(
			__( 'New Coach Application', 'elite-sports-connect' ),
			__( 'Hi Admin,', 'elite-sports-connect' ),
			sprintf(
				__( 'A new coach application has been submitted by <strong>%s</strong> (%s).', 'elite-sports-connect' ),
				esc_html( $name ),
				esc_html( $email )
			),
			'<a href="' . esc_url( $edit_url ) . '" style="display:inline-block;padding:12px 28px;background:#F4A100;color:#0A192F;font-weight:700;text-decoration:none;border-radius:6px;">' .
				__( 'Review Application', 'elite-sports-connect' ) . '</a>'
		);
		$this->send( $admin_email, $admin_subject, $admin_body );
	}

	// ─── Student Submitted ───────────────────────────────────────────────────

	public function on_student_submitted( int $post_id, string $email, string $name ): void {
		$admin_email = $this->get_notification_email();
		$site_name   = get_bloginfo( 'name' );

		// ── Confirmation to student ──
		$subject = sprintf( __( '[%s] We received your coach request', 'elite-sports-connect' ), $site_name );
		$body    = $this->wrap_template(
			__( 'Request Received', 'elite-sports-connect' ),
			sprintf( __( 'Hi %s,', 'elite-sports-connect' ), esc_html( $name ) ),
			__( "Thank you for reaching out via <strong>{$site_name}</strong>. Our team will review your request and match you with the best available coach for your sport and goals.", 'elite-sports-connect' ),
			__( 'We will be in touch shortly!', 'elite-sports-connect' )
		);
		$this->send( $email, $subject, $body );

		// ── Admin notification ──
		$admin_subject = sprintf( __( '[%s] New Student Lead — %s', 'elite-sports-connect' ), $site_name, $name );
		$edit_url      = get_edit_post_link( $post_id, 'raw' );
		$admin_body    = $this->wrap_template(
			__( 'New Student Lead', 'elite-sports-connect' ),
			__( 'Hi Admin,', 'elite-sports-connect' ),
			sprintf(
				__( 'A new student lead has been submitted by <strong>%s</strong> (%s).', 'elite-sports-connect' ),
				esc_html( $name ),
				esc_html( $email )
			),
			'<a href="' . esc_url( $edit_url ) . '" style="display:inline-block;padding:12px 28px;background:#F4A100;color:#0A192F;font-weight:700;text-decoration:none;border-radius:6px;">' .
				__( 'View Lead', 'elite-sports-connect' ) . '</a>'
		);
		$this->send( $admin_email, $admin_subject, $admin_body );
	}

	// ─── Coach Published ─────────────────────────────────────────────────────

	public function handle_status_transition( string $new_status, string $old_status, WP_Post $post ): void {
		if ( ! in_array( $post->post_type, [ 'coach', 'student_lead' ], true ) ) {
			return;
		}

		if ( $new_status === $old_status ) {
			return;
		}

		if ( 'coach' === $post->post_type && 'publish' === $new_status ) {
			$this->send_coach_approved_email( $post );
			return;
		}

		if ( 'student_lead' === $post->post_type && 'publish' === $new_status ) {
			$this->send_student_approved_email( $post );
			return;
		}

		if ( 'esc_rejected' === $new_status ) {
			$this->send_rejection_email( $post );
		}
	}

	// ─── Internal Helpers ─────────────────────────────────────────────────────

	private function send( string $to, string $subject, string $html_body ): bool {
		add_filter( 'wp_mail_content_type', [ $this, 'set_html_content_type' ] );
		$result = wp_mail( $to, $subject, $html_body );
		remove_filter( 'wp_mail_content_type', [ $this, 'set_html_content_type' ] );
		return $result;
	}

	public function set_html_content_type(): string {
		return 'text/html';
	}

	public function configure_phpmailer( $phpmailer ): void {
		$host = trim( (string) get_option( 'esc_smtp_host', '' ) );
		if ( '' === $host ) {
			return;
		}

		$phpmailer->isSMTP();
		$phpmailer->Host     = $host;
		$phpmailer->Port     = max( 1, absint( get_option( 'esc_smtp_port', 587 ) ) );
		$phpmailer->Username = (string) get_option( 'esc_smtp_username', '' );
		$phpmailer->Password = (string) get_option( 'esc_smtp_password', '' );
		$phpmailer->SMTPAuth = ( '' !== $phpmailer->Username || '' !== $phpmailer->Password );

		if ( 465 === (int) $phpmailer->Port ) {
			$phpmailer->SMTPSecure = 'ssl';
		} elseif ( in_array( (int) $phpmailer->Port, [ 587, 2525 ], true ) ) {
			$phpmailer->SMTPSecure = 'tls';
		} else {
			$phpmailer->SMTPSecure = '';
		}

		$from_email = $this->get_notification_email();
		$from_name  = get_bloginfo( 'name' );

		if ( is_email( $from_email ) ) {
			$phpmailer->setFrom( $from_email, $from_name, false );
		}
	}

	private function get_notification_email(): string {
		$support_email = sanitize_email( (string) get_option( 'esc_support_email', '' ) );

		return is_email( $support_email ) ? $support_email : get_option( 'admin_email' );
	}

	private function send_coach_approved_email( WP_Post $post ): void {
		$email     = get_post_meta( $post->ID, '_esc_email', true );
		$name      = $post->post_title;
		$site_name = get_bloginfo( 'name' );

		if ( ! is_email( $email ) ) {
			return;
		}

		$profile_url = get_permalink( $post->ID );
		$subject     = sprintf( __( '[%s] Welcome! Your coaching profile is now live', 'elite-sports-connect' ), $site_name );
		$body        = $this->wrap_template(
			__( 'Welcome to the Platform!', 'elite-sports-connect' ),
			sprintf( __( 'Hi %s,', 'elite-sports-connect' ), esc_html( $name ) ),
			sprintf(
				__( 'Exciting news — your coaching profile on <strong>%s</strong> has been approved and is now live! Athletes can now discover and connect with you.', 'elite-sports-connect' ),
				esc_html( $site_name )
			),
			'<a href="' . esc_url( $profile_url ) . '" style="display:inline-block;padding:12px 28px;background:#F4A100;color:#0A192F;font-weight:700;text-decoration:none;border-radius:6px;">' .
				__( 'View Your Profile', 'elite-sports-connect' ) . '</a>'
		);

		$this->send( $email, $subject, $body );
	}

	private function send_student_approved_email( WP_Post $post ): void {
		$email     = get_post_meta( $post->ID, '_esc_s_email', true );
		$name      = $post->post_title;
		$site_name = get_bloginfo( 'name' );

		if ( ! is_email( $email ) ) {
			return;
		}

		$subject = sprintf( __( '[%s] Your request has been approved', 'elite-sports-connect' ), $site_name );
		$body    = $this->wrap_template(
			__( 'Request Approved', 'elite-sports-connect' ),
			sprintf( __( 'Hi %s,', 'elite-sports-connect' ), esc_html( $name ) ),
			sprintf(
				__( 'Good news — your request on <strong>%s</strong> has been approved. Our team will now continue with the next steps for your application.', 'elite-sports-connect' ),
				esc_html( $site_name )
			),
			__( 'We will be in touch soon with the next update.', 'elite-sports-connect' )
		);

		$this->send( $email, $subject, $body );
	}

	private function send_rejection_email( WP_Post $post ): void {
		$site_name = get_bloginfo( 'name' );
		$name      = $post->post_title;

		if ( 'coach' === $post->post_type ) {
			$email   = get_post_meta( $post->ID, '_esc_email', true );
			$subject = sprintf( __( '[%s] Update on your coaching application', 'elite-sports-connect' ), $site_name );
			$body    = $this->wrap_template(
				__( 'Application Update', 'elite-sports-connect' ),
				sprintf( __( 'Hi %s,', 'elite-sports-connect' ), esc_html( $name ) ),
				sprintf(
					__( 'Thank you for your interest in joining <strong>%s</strong>. After review, we are unable to approve your coaching application at this time.', 'elite-sports-connect' ),
					esc_html( $site_name )
				),
				__( 'You are welcome to apply again in the future with updated details.', 'elite-sports-connect' )
			);
		} else {
			$email   = get_post_meta( $post->ID, '_esc_s_email', true );
			$subject = sprintf( __( '[%s] Update on your request', 'elite-sports-connect' ), $site_name );
			$body    = $this->wrap_template(
				__( 'Request Update', 'elite-sports-connect' ),
				sprintf( __( 'Hi %s,', 'elite-sports-connect' ), esc_html( $name ) ),
				sprintf(
					__( 'Thank you for submitting your request to <strong>%s</strong>. After review, we are unable to approve it at this time.', 'elite-sports-connect' ),
					esc_html( $site_name )
				),
				__( 'You can submit a new request later if your requirements change.', 'elite-sports-connect' )
			);
		}

		if ( is_email( $email ) ) {
			$this->send( $email, $subject, $body );
		}
	}

	/**
	 * Wraps content in the branded HTML email template.
	 */
	private function wrap_template(
		string $heading,
		string $greeting,
		string $body_line,
		string $action_html
	): string {
		$site_name = get_bloginfo( 'name' );
		$year      = gmdate( 'Y' );

		return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{$site_name}</title>
</head>
<body style="margin:0;padding:0;background:#F0F4F8;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#F0F4F8;padding:40px 0;">
    <tr>
      <td align="center">
        <table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;">
          <!-- Header -->
          <tr>
            <td style="background:#0A192F;padding:32px 40px;border-radius:12px 12px 0 0;text-align:center;">
              <h1 style="margin:0;color:#F4A100;font-size:24px;font-weight:800;letter-spacing:-0.5px;">{$site_name}</h1>
              <p style="margin:6px 0 0;color:#94A3B8;font-size:13px;letter-spacing:2px;text-transform:uppercase;">{$heading}</p>
            </td>
          </tr>
          <!-- Body -->
          <tr>
            <td style="background:#FFFFFF;padding:40px;border-radius:0 0 12px 12px;">
              <p style="margin:0 0 16px;color:#0A192F;font-size:16px;font-weight:600;">{$greeting}</p>
              <p style="margin:0 0 28px;color:#4A5568;font-size:15px;line-height:1.7;">{$body_line}</p>
              <p style="margin:0 0 28px;text-align:center;">{$action_html}</p>
              <hr style="border:none;border-top:1px solid #E2E8F0;margin:28px 0;">
              <p style="margin:0;color:#94A3B8;font-size:12px;text-align:center;">&copy; {$year} {$site_name}. All rights reserved.</p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
HTML;
	}
}

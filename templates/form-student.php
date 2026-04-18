<?php
/**
 * Template: Student Lead / Find-a-Coach Form
 *
 * Variables available:
 *   $atts['title']       — Form heading.
 *   $atts['subtitle']    — Form sub-heading.
 *   $atts['button_text'] — Submit button label.
 *   $atts['success_msg'] — Success message text.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$sports      = ESC_Forms::get_sports_list();
// Post to wp-admin/admin-post.php — WordPress's dedicated form handler endpoint.
$action_url  = esc_url( admin_url( 'admin-post.php' ) );

// Determine current status from query params (set after redirect).
$esc_status  = isset( $_GET['esc_status'] )  ? sanitize_text_field( wp_unslash( $_GET['esc_status'] ) )  : '';
$esc_form    = isset( $_GET['esc_form'] )    ? sanitize_text_field( wp_unslash( $_GET['esc_form'] ) )    : '';
$esc_message = isset( $_GET['esc_message'] ) ? esc_html( rawurldecode( sanitize_text_field( wp_unslash( $_GET['esc_message'] ) ) ) ) : '';
$is_success  = ( 'success' === $esc_status && 'student' === $esc_form );
$is_error    = ( 'error' === $esc_status );
$is_enabled  = ESC_Forms::is_form_enabled( 'student' );
?>

<div class="esc-wrap esc-wrap--form esc-wrap--student">
	<div class="esc-form-card esc-form-card--student">

		<div class="esc-form__header esc-form__header--student">
			<div class="esc-form__header-badge">
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5"/></svg>
			</div>
			<h2 class="esc-form__title"><?php echo esc_html( $atts['title'] ); ?></h2>
			<p class="esc-form__subtitle"><?php echo esc_html( $atts['subtitle'] ); ?></p>
		</div>

		<?php if ( ! $is_enabled ) : ?>

			<div class="esc-notice esc-notice--error" role="alert">
				<span class="esc-notice__icon">&#9888;</span>
				<?php echo esc_html( ESC_Forms::get_disabled_form_message( 'student' ) ); ?>
			</div>

		<?php elseif ( $is_success ) : ?>

			<!-- ── Success State ─── -->
			<div class="esc-success-state">
				<div class="esc-success-state__icon" aria-hidden="true">&#10003;</div>
				<h3 class="esc-success-state__title"><?php esc_html_e( 'Request Received!', 'elite-sports-connect' ); ?></h3>
				<p class="esc-success-state__text"><?php echo esc_html( $atts['success_msg'] ); ?></p>
				<a href="<?php echo esc_url( remove_query_arg( [ 'esc_status', 'esc_form', 'esc_message' ] ) ); ?>" class="esc-btn esc-btn--outline esc-btn--sm">
					<?php esc_html_e( 'Submit Another Request', 'elite-sports-connect' ); ?>
				</a>
			</div>

		<?php else : ?>

			<form class="esc-form" id="esc-student-form"
			      method="post"
			      action="<?php echo $action_url; ?>"
			      novalidate>

				<?php wp_nonce_field( 'esc_student_form', 'esc_student_nonce' ); ?>

				<!-- Routes this POST to admin_post_[nopriv_]esc_student_form -->
				<input type="hidden" name="action" value="esc_student_form">
				<!-- Absolute URL of this page so the handler can redirect back here -->
				<input type="hidden" name="esc_return_url" value="<?php echo esc_attr( esc_url( get_permalink() ) ); ?>">

				<!-- Honeypot -->
				<div class="esc-hp" aria-hidden="true">
					<input type="text" name="esc_website_hp" tabindex="-1" autocomplete="off">
				</div>

				<?php if ( $is_error ) : ?>
					<div class="esc-notice esc-notice--error" role="alert">
						<span class="esc-notice__icon">&#9888;</span>
						<?php echo $esc_message ?: esc_html__( 'An error occurred. Please try again.', 'elite-sports-connect' ); ?>
					</div>
				<?php endif; ?>

				<!-- ── Row: Name & Email ─── -->
				<div class="esc-form__row esc-form__row--2col">
					<div class="esc-form__group">
						<label class="esc-form__label" for="esc_name">
							<?php esc_html_e( 'Your Name', 'elite-sports-connect' ); ?>
							<span class="esc-required" aria-hidden="true">*</span>
						</label>
						<div class="esc-form__input-wrap">
							<span class="esc-form__input-icon">
								<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"/></svg>
							</span>
							<input class="esc-form__input"
							       type="text"
							       id="esc_name"
							       name="esc_name"
							       placeholder="<?php esc_attr_e( 'Jane Doe', 'elite-sports-connect' ); ?>"
							       required
							       autocomplete="name"
							       value="<?php echo esc_attr( $_POST['esc_name'] ?? '' ); ?>">
						</div>
					</div>

					<div class="esc-form__group">
						<label class="esc-form__label" for="esc_email">
							<?php esc_html_e( 'Email Address', 'elite-sports-connect' ); ?>
							<span class="esc-required" aria-hidden="true">*</span>
						</label>
						<div class="esc-form__input-wrap">
							<span class="esc-form__input-icon">
								<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/><path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/></svg>
							</span>
							<input class="esc-form__input"
							       type="email"
							       id="esc_email"
							       name="esc_email"
							       placeholder="<?php esc_attr_e( 'you@example.com', 'elite-sports-connect' ); ?>"
							       required
							       autocomplete="email"
							       value="<?php echo esc_attr( $_POST['esc_email'] ?? '' ); ?>">
						</div>
					</div>
				</div>

				<!-- ── Row: Phone & Location ─── -->
				<div class="esc-form__row esc-form__row--2col">
					<div class="esc-form__group">
						<label class="esc-form__label" for="esc_phone">
							<?php esc_html_e( 'Phone Number', 'elite-sports-connect' ); ?>
						</label>
						<div class="esc-form__input-wrap">
							<span class="esc-form__input-icon">
								<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/></svg>
							</span>
							<input class="esc-form__input"
							       type="tel"
							       id="esc_phone"
							       name="esc_phone"
							       placeholder="<?php esc_attr_e( '+1 555 000 0000', 'elite-sports-connect' ); ?>"
							       autocomplete="tel"
							       value="<?php echo esc_attr( $_POST['esc_phone'] ?? '' ); ?>">
						</div>
					</div>

					<div class="esc-form__group">
						<label class="esc-form__label" for="esc_location">
							<?php esc_html_e( 'Your Location', 'elite-sports-connect' ); ?>
						</label>
						<div class="esc-form__input-wrap">
							<span class="esc-form__input-icon">
								<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>
							</span>
							<input class="esc-form__input"
							       type="text"
							       id="esc_location"
							       name="esc_location"
							       placeholder="<?php esc_attr_e( 'Los Angeles, CA', 'elite-sports-connect' ); ?>"
							       value="<?php echo esc_attr( $_POST['esc_location'] ?? '' ); ?>">
						</div>
					</div>
				</div>

				<!-- ── Row: Preferred Sport ─── -->
				<div class="esc-form__row">
					<div class="esc-form__group">
						<label class="esc-form__label" for="esc_preferred_sport">
							<?php esc_html_e( 'Preferred Sport', 'elite-sports-connect' ); ?>
							<span class="esc-required" aria-hidden="true">*</span>
						</label>
						<div class="esc-form__input-wrap esc-form__input-wrap--select">
							<span class="esc-form__input-icon">
								<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-11.25a.75.75 0 00-1.5 0v4.59L7.3 9.24a.75.75 0 00-1.1 1.02l3.25 3.5a.75.75 0 001.1 0l3.25-3.5a.75.75 0 10-1.1-1.02l-1.95 2.1V6.75z" clip-rule="evenodd"/></svg>
							</span>
							<select class="esc-form__select" id="esc_preferred_sport" name="esc_preferred_sport" required>
								<option value=""><?php esc_html_e( 'Select your sport…', 'elite-sports-connect' ); ?></option>
								<?php foreach ( $sports as $sport ) : ?>
									<option value="<?php echo esc_attr( $sport ); ?>" <?php selected( $_POST['esc_preferred_sport'] ?? '', $sport ); ?>><?php echo esc_html( $sport ); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>
				</div>

				<!-- ── Row: Looking For ─── -->
				<div class="esc-form__row">
					<div class="esc-form__group">
						<label class="esc-form__label" for="esc_looking_for">
							<?php esc_html_e( 'What Are You Looking For?', 'elite-sports-connect' ); ?>
						</label>
						<textarea class="esc-form__textarea"
						          id="esc_looking_for"
						          name="esc_looking_for"
						          rows="4"
						          placeholder="<?php esc_attr_e( 'Describe your goals, skill level, availability, or any other details to help us find the right coach for you…', 'elite-sports-connect' ); ?>"><?php echo esc_textarea( $_POST['esc_looking_for'] ?? '' ); ?></textarea>
					</div>
				</div>

				<div class="esc-form__footer">
					<p class="esc-form__privacy">
						<?php
						printf(
							esc_html__( 'By submitting, you agree to our %s.', 'elite-sports-connect' ),
							'<a href="' . esc_url( get_privacy_policy_url() ) . '" target="_blank" rel="noopener">' . esc_html__( 'Privacy Policy', 'elite-sports-connect' ) . '</a>'
						);
						?>
					</p>
					<button class="esc-btn esc-btn--primary" type="submit" name="esc_student_submit">
						<span class="esc-btn__text"><?php echo esc_html( $atts['button_text'] ); ?></span>
						<span class="esc-btn__icon" aria-hidden="true">
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
						</span>
					</button>
				</div>

			</form>

		<?php endif; ?>

	</div>
</div>

<?php
/**
 * Template: Coach Registration Form
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

$sports     = ESC_Forms::get_sports_list();
$experience = ESC_Forms::get_experience_levels();
// Post to wp-admin/admin-post.php — WordPress's dedicated form handler endpoint.
$action_url = esc_url( admin_url( 'admin-post.php' ) );

// Determine current status from query params (set after redirect).
$esc_status  = isset( $_GET['esc_status'] )  ? sanitize_text_field( wp_unslash( $_GET['esc_status'] ) )  : '';
$esc_form    = isset( $_GET['esc_form'] )    ? sanitize_text_field( wp_unslash( $_GET['esc_form'] ) )    : '';
$esc_message = isset( $_GET['esc_message'] ) ? esc_html( rawurldecode( sanitize_text_field( wp_unslash( $_GET['esc_message'] ) ) ) ) : '';
$is_success  = ( 'success' === $esc_status && 'coach' === $esc_form );
$is_error    = ( 'error' === $esc_status );
$is_enabled  = ESC_Forms::is_form_enabled( 'coach' );
?>

<div class="esc-wrap esc-wrap--form">
	<div class="esc-form-card">

		<div class="esc-form__header">
			<div class="esc-form__header-badge">
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
			</div>
			<h2 class="esc-form__title"><?php echo esc_html( $atts['title'] ); ?></h2>
			<p class="esc-form__subtitle"><?php echo esc_html( $atts['subtitle'] ); ?></p>
		</div>

		<?php if ( ! $is_enabled ) : ?>

			<div class="esc-notice esc-notice--error" role="alert">
				<span class="esc-notice__icon">&#9888;</span>
				<?php echo esc_html( ESC_Forms::get_disabled_form_message( 'coach' ) ); ?>
			</div>

		<?php elseif ( $is_success ) : ?>

			<!-- ── Success State ─── -->
			<div class="esc-success-state">
				<div class="esc-success-state__icon" aria-hidden="true">&#10003;</div>
				<h3 class="esc-success-state__title"><?php esc_html_e( 'Application Submitted!', 'elite-sports-connect' ); ?></h3>
				<p class="esc-success-state__text"><?php echo esc_html( $atts['success_msg'] ); ?></p>
				<a href="<?php echo esc_url( remove_query_arg( [ 'esc_status', 'esc_form', 'esc_message' ] ) ); ?>" class="esc-btn esc-btn--outline esc-btn--sm">
					<?php esc_html_e( 'Submit Another Application', 'elite-sports-connect' ); ?>
				</a>
			</div>

		<?php else : ?>

			<form class="esc-form" id="esc-coach-form"
			      method="post"
			      action="<?php echo $action_url; ?>"
			      enctype="multipart/form-data"
			      novalidate>

				<?php wp_nonce_field( 'esc_coach_form', 'esc_coach_nonce' ); ?>

				<!-- Routes this POST to admin_post_[nopriv_]esc_coach_form -->
				<input type="hidden" name="action" value="esc_coach_form">
				<!-- Absolute URL of this page so the handler can redirect back here -->
				<input type="hidden" name="esc_return_url" value="<?php echo esc_attr( esc_url( get_permalink() ) ); ?>">

				<!-- Honeypot (hidden from real users) -->
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
							<?php esc_html_e( 'Full Name', 'elite-sports-connect' ); ?>
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
							       placeholder="<?php esc_attr_e( 'John Smith', 'elite-sports-connect' ); ?>"
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
							<?php esc_html_e( 'City / Location', 'elite-sports-connect' ); ?>
						</label>
						<div class="esc-form__input-wrap">
							<span class="esc-form__input-icon">
								<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>
							</span>
							<input class="esc-form__input"
							       type="text"
							       id="esc_location"
							       name="esc_location"
							       placeholder="<?php esc_attr_e( 'New York, NY', 'elite-sports-connect' ); ?>"
							       value="<?php echo esc_attr( $_POST['esc_location'] ?? '' ); ?>">
						</div>
					</div>
				</div>

				<!-- ── Row: Postal Code & Sport ─── -->
				<div class="esc-form__row esc-form__row--2col">
					<div class="esc-form__group">
						<label class="esc-form__label" for="esc_postal_code">
							<?php esc_html_e( 'Postal Code', 'elite-sports-connect' ); ?>
						</label>
						<div class="esc-form__input-wrap">
							<span class="esc-form__input-icon">
								<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L13 10.414V17a1 1 0 01-.553.894l-4 2A1 1 0 017 19v-8.586L3.293 6.707A1 1 0 013 6V3z"/></svg>
							</span>
							<input class="esc-form__input"
							       type="text"
							       id="esc_postal_code"
							       name="esc_postal_code"
							       placeholder="<?php esc_attr_e( '10001', 'elite-sports-connect' ); ?>"
							       value="<?php echo esc_attr( $_POST['esc_postal_code'] ?? '' ); ?>">
						</div>
					</div>

					<div class="esc-form__group">
						<label class="esc-form__label" for="esc_sport">
							<?php esc_html_e( 'Sport', 'elite-sports-connect' ); ?>
							<span class="esc-required" aria-hidden="true">*</span>
						</label>
						<div class="esc-form__input-wrap esc-form__input-wrap--select">
							<span class="esc-form__input-icon">
								<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-11.25a.75.75 0 00-1.5 0v4.59L7.3 9.24a.75.75 0 00-1.1 1.02l3.25 3.5a.75.75 0 001.1 0l3.25-3.5a.75.75 0 10-1.1-1.02l-1.95 2.1V6.75z" clip-rule="evenodd"/></svg>
							</span>
							<select class="esc-form__select" id="esc_sport" name="esc_sport" required>
								<option value=""><?php esc_html_e( 'Select your sport…', 'elite-sports-connect' ); ?></option>
								<?php foreach ( $sports as $sport ) : ?>
									<option value="<?php echo esc_attr( $sport ); ?>" <?php selected( $_POST['esc_sport'] ?? '', $sport ); ?>><?php echo esc_html( $sport ); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>
				</div>

				<!-- ── Row: Experience Level ─── -->
				<div class="esc-form__row">
					<div class="esc-form__group">
						<label class="esc-form__label">
							<?php esc_html_e( 'Experience Level', 'elite-sports-connect' ); ?>
							<span class="esc-required" aria-hidden="true">*</span>
						</label>
						<div class="esc-radio-group" role="radiogroup" aria-label="<?php esc_attr_e( 'Experience Level', 'elite-sports-connect' ); ?>">
							<?php foreach ( $experience as $level ) : ?>
								<label class="esc-radio-option">
									<input type="radio"
									       name="esc_experience"
									       value="<?php echo esc_attr( $level ); ?>"
									       <?php checked( $_POST['esc_experience'] ?? '', $level ); ?>>
									<span class="esc-radio-option__label"><?php echo esc_html( $level ); ?></span>
								</label>
							<?php endforeach; ?>
						</div>
					</div>
				</div>

				<!-- ── Row: Bio ─── -->
				<div class="esc-form__row">
					<div class="esc-form__group">
						<label class="esc-form__label" for="esc_bio">
							<?php esc_html_e( 'Bio / About You', 'elite-sports-connect' ); ?>
						</label>
						<textarea class="esc-form__textarea"
						          id="esc_bio"
						          name="esc_bio"
						          rows="5"
						          placeholder="<?php esc_attr_e( 'Tell athletes about your coaching philosophy, achievements, and what makes you unique…', 'elite-sports-connect' ); ?>"><?php echo esc_textarea( $_POST['esc_bio'] ?? '' ); ?></textarea>
					</div>
				</div>

				<!-- ── Row: Photo Upload ─── -->
				<div class="esc-form__row">
					<div class="esc-form__group">
						<label class="esc-form__label" for="esc_photo">
							<?php esc_html_e( 'Profile Photo', 'elite-sports-connect' ); ?>
						</label>
						<div class="esc-upload-zone" id="esc-upload-zone">
							<input type="file"
							       class="esc-upload-zone__input"
							       id="esc_photo"
							       name="esc_photo"
							       accept="image/jpeg,image/png,image/webp">
							<div class="esc-upload-zone__ui" aria-hidden="true">
								<svg class="esc-upload-zone__icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
								<p class="esc-upload-zone__text"><?php esc_html_e( 'Click to upload or drag & drop', 'elite-sports-connect' ); ?></p>
								<p class="esc-upload-zone__hint"><?php esc_html_e( 'JPG, PNG or WebP — max 5 MB', 'elite-sports-connect' ); ?></p>
							</div>
							<div class="esc-upload-zone__preview" id="esc-photo-preview" hidden>
								<img id="esc-photo-preview-img" src="" alt="<?php esc_attr_e( 'Preview', 'elite-sports-connect' ); ?>">
								<button type="button" class="esc-upload-zone__remove" id="esc-photo-remove" aria-label="<?php esc_attr_e( 'Remove photo', 'elite-sports-connect' ); ?>">&times;</button>
							</div>
						</div>
					</div>
				</div>

				<!-- ── Website (optional) ─── -->
				<div class="esc-form__row">
					<div class="esc-form__group">
						<label class="esc-form__label" for="esc_website">
							<?php esc_html_e( 'Website (optional)', 'elite-sports-connect' ); ?>
						</label>
						<div class="esc-form__input-wrap">
							<span class="esc-form__input-icon">
								<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM4.332 8.027a6.012 6.012 0 011.912-2.706C6.512 5.73 6.974 6 7.5 6A1.5 1.5 0 019 7.5V8a2 2 0 004 0 2 2 0 011.523-1.943A5.977 5.977 0 0116 10c0 .34-.028.675-.083 1H15a2 2 0 00-2 2v2.197A5.973 5.973 0 0110 16v-2a2 2 0 00-2-2 2 2 0 01-2-2 2 2 0 00-1.668-1.973z" clip-rule="evenodd"/></svg>
							</span>
							<input class="esc-form__input"
							       type="url"
							       id="esc_website"
							       name="esc_website"
							       placeholder="<?php esc_attr_e( 'https://yourwebsite.com', 'elite-sports-connect' ); ?>"
							       autocomplete="url"
							       value="<?php echo esc_attr( $_POST['esc_website'] ?? '' ); ?>">
						</div>
					</div>
				</div>

				<div class="esc-form__footer">
					<p class="esc-form__privacy">
						<?php
						printf(
							/* translators: 1: privacy policy link */
							esc_html__( 'By submitting, you agree to our %s.', 'elite-sports-connect' ),
							'<a href="' . esc_url( get_privacy_policy_url() ) . '" target="_blank" rel="noopener">' . esc_html__( 'Privacy Policy', 'elite-sports-connect' ) . '</a>'
						);
						?>
					</p>
					<button class="esc-btn esc-btn--primary" type="submit" name="esc_coach_submit">
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

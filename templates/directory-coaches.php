<?php
/**
 * Template: Coach Directory
 *
 * Variables available:
 *   $atts        — shortcode attributes.
 *   $coaches     — WP_Query object.
 *   $active_sport — currently filtered sport (string).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$sports        = ESC_Forms::get_sports_list();
$columns_class = 'esc-grid--col-' . (int) $atts['columns'];
$current_url   = strtok( $_SERVER['REQUEST_URI'] ?? '', '?' );
$layout_class  = 'esc-directory--layout-' . $atts['layout'];
$show_contact  = ( 'no' !== ( $atts['show_contact'] ?? 'yes' ) );
?>

<div class="esc-wrap esc-wrap--directory <?php echo esc_attr( $layout_class ); ?>">

	<!-- ── Section Heading ─── -->
	<div class="esc-directory__head">
		<h2 class="esc-directory__title"><?php echo esc_html( $atts['title'] ); ?></h2>
		<?php if ( $coaches->found_posts > 0 ) : ?>
			<p class="esc-directory__count">
				<?php
				printf(
					/* translators: number of coaches */
					esc_html( _n( '%s Coach', '%s Coaches', $coaches->found_posts, 'elite-sports-connect' ) ),
					'<strong>' . number_format_i18n( $coaches->found_posts ) . '</strong>'
				);
				?>
			</p>
		<?php endif; ?>
	</div>

	<!-- ── Sport Filter Bar ─── -->
	<?php if ( 'yes' === $atts['sport_filter'] ) : ?>
		<div class="esc-filter-bar" role="navigation" aria-label="<?php esc_attr_e( 'Filter by sport', 'elite-sports-connect' ); ?>">
			<a href="<?php echo esc_url( $current_url ); ?>"
			   class="esc-filter-bar__pill <?php echo empty( $active_sport ) ? 'is-active' : ''; ?>">
				<?php esc_html_e( 'All Sports', 'elite-sports-connect' ); ?>
			</a>
			<?php foreach ( $sports as $sport ) : ?>
				<a href="<?php echo esc_url( add_query_arg( 'esc_sport', rawurlencode( $sport ), $current_url ) ); ?>"
				   class="esc-filter-bar__pill <?php echo ( $active_sport === $sport ) ? 'is-active' : ''; ?>">
					<?php echo esc_html( $sport ); ?>
				</a>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<!-- ── Coach Grid ─── -->
	<?php if ( $coaches->have_posts() ) : ?>
		<div class="esc-grid <?php echo esc_attr( $columns_class ); ?>">
			<?php while ( $coaches->have_posts() ) : $coaches->the_post();
				$post_id    = get_the_ID();
				$meta       = ESC_CPT::get_coach_meta( $post_id );
				$photo_url  = has_post_thumbnail()
					? get_the_post_thumbnail_url( $post_id, 'medium' )
					: '';
				$feature_image = $photo_url ? $photo_url : 'data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 800 560%22%3E%3Crect width=%22800%22 height=%22560%22 fill=%22%23e2e8f0%22/%3E%3C/svg%3E';
				$bio = trim( (string) get_the_content() );
				if ( '' === $bio ) {
					$bio = trim( (string) get_the_excerpt() );
				}
			?>
				<?php if ( 'feature' === $atts['layout'] ) : ?>
					<article class="esc-coach-card esc-coach-card--feature" itemscope itemtype="https://schema.org/Person">
						<div class="esc-coach-card__photo-wrap esc-coach-card__photo-wrap--feature">
							<img class="esc-coach-card__photo"
							     src="<?php echo esc_url( $feature_image ); ?>"
							     alt="<?php echo esc_attr( get_the_title() ); ?>"
							     loading="lazy"
							     itemprop="image">
						</div>
						<div class="esc-coach-card__body esc-coach-card__body--feature">
							<h3 class="esc-coach-card__name" itemprop="name"><?php the_title(); ?></h3>
							<?php if ( ! empty( $meta['experience'] ) ) : ?>
								<p class="esc-coach-card__role"><?php echo esc_html( $meta['experience'] ); ?></p>
							<?php endif; ?>
							<?php if ( $bio ) : ?>
								<div class="esc-coach-card__bio esc-coach-card__bio--feature">
									<?php echo wp_kses_post( wpautop( $bio ) ); ?>
								</div>
							<?php endif; ?>
							<?php if ( $show_contact && ! empty( $meta['email'] ) ) : ?>
								<div class="esc-coach-card__footer esc-coach-card__footer--feature">
									<a class="esc-btn esc-btn--ghost esc-btn--sm"
									   href="mailto:<?php echo esc_attr( $meta['email'] ); ?>"
									   itemprop="email">
										<?php esc_html_e( 'Contact', 'elite-sports-connect' ); ?>
									</a>
								</div>
							<?php endif; ?>
						</div>
					</article>
				<?php elseif ( 'split' === $atts['layout'] ) : ?>
					<article class="esc-coach-card esc-coach-card--split" itemscope itemtype="https://schema.org/Person">
						<div class="esc-coach-card__photo-wrap esc-coach-card__photo-wrap--split">
							<img class="esc-coach-card__photo"
							     src="<?php echo esc_url( $feature_image ); ?>"
							     alt="<?php echo esc_attr( get_the_title() ); ?>"
							     loading="lazy"
							     itemprop="image">
						</div>
						<div class="esc-coach-card__body esc-coach-card__body--split">
							<h3 class="esc-coach-card__name" itemprop="name"><?php the_title(); ?></h3>
							<?php if ( ! empty( $meta['sport'] ) ) : ?>
								<div class="esc-coach-card__tags">
									<span class="esc-tag" itemprop="knowsAbout"><?php echo esc_html( $meta['sport'] ); ?></span>
								</div>
							<?php endif; ?>
							<?php if ( ! empty( $meta['location'] ) ) : ?>
								<p class="esc-coach-card__location" itemprop="address">
									<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true" class="esc-icon"><path fill-rule="evenodd" d="M3.5 3a4.5 4.5 0 119 0c0 2.32-1.813 4.553-3.352 6.113a1 1 0 01-1.296 0C6.313 7.553 3.5 5.32 3.5 3zM8 5a1.5 1.5 0 100-3 1.5 1.5 0 000 3z" clip-rule="evenodd"/><path d="M2 13.5A1.5 1.5 0 013.5 12h9a1.5 1.5 0 010 3h-9A1.5 1.5 0 012 13.5z"/></svg>
									<?php echo esc_html( $meta['location'] ); ?>
								</p>
							<?php endif; ?>
							<?php if ( $bio ) : ?>
								<div class="esc-coach-card__bio"><?php echo esc_html( wp_trim_words( wp_strip_all_tags( $bio ), 36 ) ); ?></div>
							<?php endif; ?>
							<?php if ( $show_contact && ! empty( $meta['email'] ) ) : ?>
								<div class="esc-coach-card__footer">
									<a class="esc-btn esc-btn--ghost esc-btn--sm"
									   href="mailto:<?php echo esc_attr( $meta['email'] ); ?>"
									   itemprop="email">
										<?php esc_html_e( 'Contact', 'elite-sports-connect' ); ?>
									</a>
								</div>
							<?php endif; ?>
						</div>
					</article>
				<?php elseif ( 'minimal' === $atts['layout'] ) : ?>
					<article class="esc-coach-card esc-coach-card--minimal" itemscope itemtype="https://schema.org/Person">
						<div class="esc-coach-card__photo-wrap esc-coach-card__photo-wrap--minimal">
							<img class="esc-coach-card__photo"
							     src="<?php echo esc_url( $feature_image ); ?>"
							     alt="<?php echo esc_attr( get_the_title() ); ?>"
							     loading="lazy"
							     itemprop="image">
						</div>
						<div class="esc-coach-card__body esc-coach-card__body--minimal">
							<h3 class="esc-coach-card__name" itemprop="name"><?php the_title(); ?></h3>
							<?php if ( ! empty( $meta['experience'] ) ) : ?>
								<p class="esc-coach-card__role"><?php echo esc_html( $meta['experience'] ); ?></p>
							<?php endif; ?>
							<?php if ( ! empty( $meta['sport'] ) ) : ?>
								<div class="esc-coach-card__tags">
									<span class="esc-tag" itemprop="knowsAbout"><?php echo esc_html( $meta['sport'] ); ?></span>
								</div>
							<?php endif; ?>
						</div>
						<?php if ( $show_contact && ! empty( $meta['email'] ) ) : ?>
							<div class="esc-coach-card__footer esc-coach-card__footer--minimal">
								<a class="esc-btn esc-btn--ghost esc-btn--sm"
								   href="mailto:<?php echo esc_attr( $meta['email'] ); ?>"
								   itemprop="email">
									<?php esc_html_e( 'Contact', 'elite-sports-connect' ); ?>
								</a>
							</div>
						<?php endif; ?>
					</article>
				<?php else : ?>
					<article class="esc-coach-card" itemscope itemtype="https://schema.org/Person">
						<div class="esc-coach-card__photo-wrap">
							<img class="esc-coach-card__photo"
							     src="<?php echo esc_url( $feature_image ); ?>"
							     alt="<?php echo esc_attr( get_the_title() ); ?>"
							     loading="lazy"
							     itemprop="image">
							<?php if ( ! empty( $meta['experience'] ) ) : ?>
								<span class="esc-coach-card__badge"><?php echo esc_html( $meta['experience'] ); ?></span>
							<?php endif; ?>
						</div>
						<div class="esc-coach-card__body">
							<h3 class="esc-coach-card__name" itemprop="name"><?php the_title(); ?></h3>
							<?php if ( ! empty( $meta['sport'] ) ) : ?>
								<div class="esc-coach-card__tags">
									<span class="esc-tag" itemprop="knowsAbout"><?php echo esc_html( $meta['sport'] ); ?></span>
								</div>
							<?php endif; ?>
							<?php if ( ! empty( $meta['location'] ) ) : ?>
								<p class="esc-coach-card__location" itemprop="address">
									<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true" class="esc-icon"><path fill-rule="evenodd" d="M3.5 3a4.5 4.5 0 119 0c0 2.32-1.813 4.553-3.352 6.113a1 1 0 01-1.296 0C6.313 7.553 3.5 5.32 3.5 3zM8 5a1.5 1.5 0 100-3 1.5 1.5 0 000 3z" clip-rule="evenodd"/><path d="M2 13.5A1.5 1.5 0 013.5 12h9a1.5 1.5 0 010 3h-9A1.5 1.5 0 012 13.5z"/></svg>
									<?php echo esc_html( $meta['location'] ); ?>
								</p>
							<?php endif; ?>
							<?php if ( $bio ) : ?>
								<div class="esc-coach-card__bio"><?php echo esc_html( wp_trim_words( wp_strip_all_tags( $bio ), 24 ) ); ?></div>
							<?php endif; ?>
						</div>
						<?php if ( $show_contact && ! empty( $meta['email'] ) ) : ?>
							<div class="esc-coach-card__footer">
								<a class="esc-btn esc-btn--ghost esc-btn--sm"
								   href="mailto:<?php echo esc_attr( $meta['email'] ); ?>"
								   itemprop="email">
									<?php esc_html_e( 'Contact', 'elite-sports-connect' ); ?>
								</a>
							</div>
						<?php endif; ?>
					</article>
				<?php endif; ?>
			<?php endwhile; ?>
		</div>

		<!-- ── Pagination ─── -->
		<?php if ( $coaches->max_num_pages > 1 ) : ?>
			<div class="esc-pagination">
				<?php
				echo wp_kses_post( paginate_links( [
					'total'     => $coaches->max_num_pages,
					'current'   => max( 1, get_query_var( 'paged' ) ),
					'prev_text' => '&larr; ' . __( 'Previous', 'elite-sports-connect' ),
					'next_text' => __( 'Next', 'elite-sports-connect' ) . ' &rarr;',
				] ) );
				?>
			</div>
		<?php endif; ?>

	<?php else : ?>
		<div class="esc-empty-state">
			<svg class="esc-empty-state__icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.182 16.318A4.486 4.486 0 0012.016 15a4.486 4.486 0 00-3.198 1.318M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/></svg>
			<h3 class="esc-empty-state__title"><?php esc_html_e( 'No coaches found', 'elite-sports-connect' ); ?></h3>
			<p class="esc-empty-state__text">
				<?php if ( $active_sport ) : ?>
					<?php
					printf(
						/* translators: sport name */
						esc_html__( 'There are currently no coaches listed for %s. Try selecting a different sport or browsing all coaches.', 'elite-sports-connect' ),
						'<strong>' . esc_html( $active_sport ) . '</strong>'
					);
					?>
					<br><br>
					<a href="<?php echo esc_url( $current_url ); ?>" class="esc-btn esc-btn--outline">
						<?php esc_html_e( 'View All Coaches', 'elite-sports-connect' ); ?>
					</a>
				<?php else : ?>
					<?php esc_html_e( 'No coaches are listed yet. Check back soon!', 'elite-sports-connect' ); ?>
				<?php endif; ?>
			</p>
		</div>
	<?php endif; ?>

</div>

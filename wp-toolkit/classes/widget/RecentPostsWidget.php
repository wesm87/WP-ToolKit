<?php
/**
 * Contains the RecentPostsWidget class.
 *
 * @package WP-ToolKit
 */

namespace WPTK\Widget;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * An example widget that shows your most recent posts.
 */
class RecentPostsWidget extends \WP_Widget {
	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			'cj_recent_posts_widget',
			__( 'ColorJar - Recent Posts', 'text_domain' )
		);
	}

	/**
	 * Outputs the widget HTML.
	 *
	 * @param array $args     The widgets args.
	 * @param array $instance The widget instance.
	 */
	public function widget( $args, $instance ) {
		$args = array(
			'posts_per_page' => $instance['numposts'],
		);
		$recent_posts_query = new \WP_Query( $args );
		?>
		<?php if ( $recent_posts_query->have_posts() ) : ?>
			<div class="widget">
				<h3 class="widget-title">Recent Posts</h3>
				<div class="content recent-posts">
					<?php while ( $recent_posts_query->have_posts() ) : ?>
						<?php $recent_posts_query->the_post(); ?>
						<div <?php \post_class( 'entry-container' ); ?>>
							<div class="entry recent-post">
								<a class="entry-title" href="<?php \the_permalink(); ?>">
									<?php \the_title(); ?>
								</a>
								<div class="entry-content">
									<?php \the_content(); ?>
								</div>
							</div>
						</div>
					<?php endwhile; ?>
				</div>
			</div>
		<?php endif; ?>
		<?php

	}

	/**
	 * Outputs the widget options form in the admin area.
	 *
	 * @param array $instance The widget instance.
	 */
	public function form( $instance ) {
		$numposts = $instance['numposts'] ?: 4;
		$numposts_field_name = $this->get_field_name( 'numposts' );
		?>
		<p>
			<label>
				<?php escape_html_e( 'Number of Posts:' ); ?>
				<input class="widefat" name="<?php echo esc_attr( $numposts_field_name ) ?>" type="number" value="<?php echo esc_attr( $numposts ); ?>">
			</label>
		</p>
		<?php
	}

	/**
	 * Updates the values in the widget instance with new ones set from the
	 * widget options form.
	 *
	 * @param array $new_instance The new widget instance data.
	 * @param array $old_instance The old widget instance data.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['numposts'] = is_numeric( $new_instance['numposts'] ) ? intval( $new_instance['numposts'] ) : 4;

		return $instance;
	}
}

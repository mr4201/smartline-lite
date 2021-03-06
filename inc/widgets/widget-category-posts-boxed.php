<?php

// Add Category Posts Boxed Widget
class Smartline_Category_Posts_Boxed_Widget extends WP_Widget {

	function __construct() {
		
		// Setup Widget
		$widget_ops = array(
			'classname' => 'smartline_category_posts_boxed', 
			'description' => esc_html__( 'Displays your posts from a selected category in a boxed layout. Please use this widget ONLY in the Magazine Homepage widget area.', 'smartline-lite' ),
			'customize_selective_refresh' => true,
		);
		parent::__construct('smartline_category_posts_boxed', sprintf( esc_html__( 'Category Posts: Boxed (%s)', 'smartline-lite' ), 'Smartline' ), $widget_ops);
		
		// Delete Widget Cache on certain actions
		add_action( 'save_post', array( $this, 'delete_widget_cache' ) );
		add_action( 'deleted_post', array( $this, 'delete_widget_cache' ) );
		add_action( 'switch_theme', array( $this, 'delete_widget_cache' ) );
		
	}

	public function delete_widget_cache() {
		
		wp_cache_delete('widget_smartline_category_posts_boxed', 'widget');
		
	}
	
	private function default_settings() {
	
		$defaults = array(
			'title'				=> '',
			'category'			=> 0,
			'category_link'		=> false,
			'postmeta'			=> 3
		);
		
		return $defaults;
		
	}
	
	// Display Widget
	function widget( $args, $instance ) {

		$cache = array();
				
		// Get Widget Object Cache
		if ( ! $this->is_preview() ) {
			$cache = wp_cache_get( 'widget_smartline_category_posts_boxed', 'widget' );
		}
		if ( ! is_array( $cache ) ) {
			$cache = array();
		}

		// Display Widget from Cache if exists
		if ( isset( $cache[ $this->id ] ) ) {
			echo $cache[ $this->id ];
			return;
		}
		
		// Start Output Buffering
		ob_start();
			
		// Get Widget Settings
		$settings = wp_parse_args( $instance, $this->default_settings() );
		
		// Output
		echo $args['before_widget'];
	?>
		<div id="widget-category-posts-boxed" class="widget-category-posts clearfix">

			<?php // Display Title
			$this->display_widget_title( $args, $settings ); ?>
			
			<div class="widget-category-posts-content">
			
				<?php $this->render( $settings ); ?>
				
			</div>
			
		</div>
	<?php
		echo $args['after_widget'];
		
		// Set Cache
		if ( ! $this->is_preview() ) {
			$cache[ $this->id ] = ob_get_flush();
			wp_cache_set( 'widget_smartline_category_posts_boxed', $cache, 'widget' );
		} else {
			ob_end_flush();
		}
	
	}
	
	// Render Widget Content
	function render( $settings ) {
		
		// Get latest posts from database
		$query_arguments = array(
			'posts_per_page' => 4,
			'ignore_sticky_posts' => true,
			'cat' => (int)$settings['category']
		);
		$posts_query = new WP_Query( $query_arguments );
		$i = 0;

		// Check if there are posts
		if( $posts_query->have_posts() ) :
		
			// Limit the number of words for the excerpt
			add_filter('excerpt_length', 'smartline_frontpage_category_excerpt_length');
			
			// Display Posts
			while( $posts_query->have_posts() ) :
				
				$posts_query->the_post(); 
				
				if(isset($i) and $i == 0) : ?>

					<article id="post-<?php the_ID(); ?>" <?php post_class('first-post big-post'); ?>>

						<a href="<?php the_permalink() ?>" rel="bookmark"><?php the_post_thumbnail('category_posts_wide_thumb'); ?></a>

						<?php the_title( sprintf( '<h2 class="entry-title post-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h2>' ); ?>

						<div class="entry-meta postmeta"><?php $this->display_postmeta( $settings ); ?></div>

						<div class="entry">
							<?php the_excerpt(); ?>
						</div>

					</article>

				<div class="small-posts more-posts clearfix">

				<?php else: ?>

					<article id="post-<?php the_ID(); ?>" <?php post_class('small-post clearfix'); ?>>

					<?php if ( '' != get_the_post_thumbnail() ) : ?>
						<a href="<?php the_permalink() ?>" rel="bookmark"><?php the_post_thumbnail('category_posts_small_thumb'); ?></a>
					<?php endif; ?>

						<div class="small-post-content">
							<?php the_title( sprintf( '<h2 class="entry-title post-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h2>' ); ?>
							<div class="entry-meta postmeta"><?php $this->display_postmeta( $settings ); ?></div>
						</div>

					</article>

				<?php
				endif; $i++;
				
			endwhile; ?>
			
				</div><!-- end .small-posts -->
				
			<?php
			// Remove excerpt filter
			remove_filter('excerpt_length', 'smartline_frontpage_category_excerpt_length');
			
		endif;
		
		// Reset Postdata
		wp_reset_postdata();

	}
	
	// Display Postmeta
	function display_postmeta( $settings ) {
		
		// Display Date unless deactivated
		if ( $settings['postmeta'] > 0 ) :
		
			smartline_meta_date();
					
		endif; 
		
		// Display Author unless deactivated
		if ( $settings['postmeta'] == 2 ) :	
		
			smartline_meta_author();
		
		endif; 
		
		// Display Comments
		if ( $settings['postmeta'] == 3 and comments_open() ) :
			
			smartline_meta_comments();
			
		endif;

	}
	
	// Display Widget Title
	function display_widget_title( $args, $settings ) {
		
		// Add Widget Title Filter
		$widget_title = apply_filters('widget_title', $settings['title'], $settings, $this->id_base);
		
		if( !empty( $widget_title ) ) :
		
			echo $args['before_title'];
			
			// Link Category Title
			if( $settings['category_link'] == true ) : 
			
				// Check if "All Categories" is selected
				if( $settings['category'] == 0 ) :
				
					$link_title = esc_html__( 'View all posts', 'smartline-lite' );
					
					// Set Link URL to always point to latest posts page
					if ( get_option( 'show_on_front' ) == 'page' ) :
						$link_url = esc_url( get_permalink( get_option( 'page_for_posts' ) ) );
					else : 
						$link_url =	esc_url( home_url('/') );
					endif;
					
				else :
					
					// Set Link URL and Title for Category
					$link_title = sprintf( esc_html__( 'View all posts from category %s', 'smartline-lite' ), get_cat_name( $settings['category'] ) );
					$link_url = esc_url( get_category_link( $settings['category'] ) );
					
				endif;
				
				// Display linked Widget Title
				echo '<a href="'. $link_url .'" title="'. $link_title . '">'. $widget_title . '</a>';
				echo '<a class="category-archive-link" href="'. $link_url .'" title="'. $link_title . '"><span class="genericon-next"></span></a>';
			
			else:
			
				echo $widget_title;
			
			endif;
			
			echo $args['after_title']; 
			
		endif;

	}

	function update( $new_instance, $old_instance ) {

		$instance = $old_instance;
		$instance['title'] = sanitize_text_field($new_instance['title'] );
		$instance['category'] = (int)$new_instance['category'];
		$instance['category_link'] = !empty($new_instance['category_link'] );
		$instance['postmeta'] = (int)$new_instance['postmeta'];
		
		$this->delete_widget_cache();
		
		return $instance;
	}

	function form( $instance ) {
		
		// Get Widget Settings
		$settings = wp_parse_args( $instance, $this->default_settings() ); 
		?>
		
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php esc_html_e( 'Title:', 'smartline-lite' ); ?>
				<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $settings['title']; ?>" />
			</label>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('category'); ?>"><?php esc_html_e( 'Category:', 'smartline-lite' ); ?></label><br/>
			<?php // Display Category Select
				$args = array(
					'show_option_all'    => esc_html__( 'All Categories', 'smartline-lite' ),
					'show_count' 		 => true,
					'hide_empty'		 => false,
					'selected'           => $settings['category'],
					'name'               => $this->get_field_name('category'),
					'id'                 => $this->get_field_id('category')
				);
				wp_dropdown_categories( $args ); 
			?>
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id('category_link'); ?>">
				<input class="checkbox" type="checkbox" <?php checked( $settings['category_link'] ) ; ?> id="<?php echo $this->get_field_id('category_link'); ?>" name="<?php echo $this->get_field_name('category_link'); ?>" />
				<?php esc_html_e( 'Link Widget Title to Category Archive page', 'smartline-lite' ); ?>
			</label>
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id( 'postmeta' ); ?>"><?php esc_html_e( 'Post Meta:', 'smartline-lite' ); ?></label><br/>
			<select id="<?php echo $this->get_field_id( 'postmeta' ); ?>" name="<?php echo $this->get_field_name( 'postmeta' ); ?>">
				<option value="0" <?php selected( $settings['postmeta'], 0); ?>><?php esc_html_e( 'Hide post meta', 'smartline-lite' ); ?></option>
				<option value="1" <?php selected( $settings['postmeta'], 1); ?>><?php esc_html_e( 'Display post date', 'smartline-lite' ); ?></option>
				<option value="2" <?php selected( $settings['postmeta'], 2); ?>><?php esc_html_e( 'Display date and author', 'smartline-lite' ); ?></option>
				<option value="3" <?php selected( $settings['postmeta'], 3); ?>><?php esc_html_e( 'Display date and comments', 'smartline-lite' ); ?></option>
			</select>
		</p>
		
<?php
	}
}

// Register Widget
add_action( 'widgets_init', 'smartline_register_category_posts_boxed_widget' );

function smartline_register_category_posts_boxed_widget() {

	register_widget('Smartline_Category_Posts_Boxed_Widget');
	
}
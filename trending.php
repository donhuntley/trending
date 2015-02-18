<?php
/**
 * Plugin Name: Trending
 * Plugin URI: http://github.com/donhuntley
 * Description: Widget listing top 5 most popular posts in order
 * Version: The plugin's version number. Example: 0.1
 * Author: Don Huntley
 * Author URI: http://firethornedesign.com
 * License: GPL2
 */

/**
 * Trending Posts Counter
 */
function my_trending_post_views($postID) {
        $total_key = 'views';
        // Get current 'views' field
        $total = get_post_meta( $postID, $total_key, true );
        // If current 'views' field is empty, set it to zero
        if ( $total == '' ) {
            delete_post_meta( $postID, $total_key );
            add_post_meta( $postID, $total_key, '0' );
        } else {
        // If current 'views' fild has a value, add 1 to that value
        $total++;
        update_post_meta ( $postID, $total_key, $total );
        }
} 
/**
 * Dynamically inject counter into single posts
 */
function my_count_trending_posts($post_id) {
    // Check that this is a single post and that the user is a visitor
    if ( !is_single() ) return;
    if (!is_user_logged_in() ) {
        // Get the post ID
        if ( empty ( $post_id) ) {
            global $post;
            $post_id = $post->ID;
        }
        // run post Popularity Counter on post
        my_trending_post_views($post_id);
    }
}        
add_action( 'wp_head', 'my_count_trending_posts' );   

/**
 * Add popular post function data to All Posts table
 */
function my_add_views_column($defaults)  {
    $defaults['post_views'] = 'View Count';
    return $defaults;
}
add_filter( 'manage_posts_columns', 'my_add_views_column');

function my_display_views($column_name) {
    if ( $column_name === 'post_views' ) {
        echo (int) get_post_meta( get_the_ID(),'views', true );       
    }
}
add_action( 'manage_posts_custom_column', 'my_display_views', 5, 2 );

/**
 * Adds Trending Posts widget.
 */
class trending_posts extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'trending_posts', // Base ID
			__( 'Trending Posts', 'text_domain' ), // Name
			array( 'description' => __( 'Displays the 5 most popular posts', 'text_domain' ), ) // Args
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		echo $args['before_widget'];
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
		}
		
                // Query goes here
                
                $query_args = array(
                    'post_type' => 'post',
                    'posts_per_page' => 5,
                    'meta_key' => 'views',
                    'orderby' => 'meta_value_num',
                    'order' => 'DESC',
                    'ignore_sticky_posts' => true
                );
                
                // The Query
                $the_query = new WP_Query( $query_args );

                // The Loop
                if ( $the_query->have_posts() ) {
                        $trending_num = 0;
                        echo '<ul>';
                        while ( $the_query->have_posts() ) {
                                $the_query->the_post();
                                $trending_num++;
                                echo '<li>';
                                echo '<a href="' . get_the_permalink() . '" rel="bookmarak">';
                                echo $trending_num . '.' . '  ';
                                echo get_the_title();
                                // echo ' (' . (int) get_post_meta(get_the_ID(), 'views', true ) . ')';
                                echo '</a>';
                                echo '</li>';
                        }
                        echo '</ul>';
                } else {
                        // no posts found
                }
                /* Restore original Post Data */
                wp_reset_postdata();

                    echo $args['after_widget'];
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Trending Posts', 'text_domain' );
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<?php 
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

		return $instance;
	}

} // class trending_posts

// register trending_posts widget
function register_trending_posts_widget() {
    register_widget( 'trending_posts' );
}
add_action( 'widgets_init', 'register_trending_posts_widget' );


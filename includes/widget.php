<?php
/**
 * List/Grid widget
 */
class BeRocket_cart_suggestion_Widget extends WP_Widget 
{
    public static $defaults = array(
        'title'       => '',
        'count'       => '4',
        'type'        => 'default',
        'add_to_cart' => '0',
        'slider_count'=> '3'
    );
	public function __construct() {
        parent::__construct("berocket_cart_suggestion_widget", "WooCommerce Cart Suggestions",
            array("description" => 'Display suggested products'));
    }
    /**
     * WordPress widget
     */
    public function widget($args, $instance)
    {
        $BeRocket_cart_suggestion = BeRocket_cart_suggestion::getInstance();
        $instance = wp_parse_args( (array) $instance, self::$defaults );
        $instance['title'] = apply_filters( 'widget_title', empty($instance['title']) ? '' : $instance['title'], $instance );
        $options = $BeRocket_cart_suggestion->get_option();
        $query_var = array();
        $query_var['title'] = apply_filters( 'cart_suggestion_widget_title', $instance['title'] );
        $query_var['count'] = apply_filters( 'cart_suggestion_widget_count', $instance['count'] );
        $query_var['type'] = apply_filters( 'cart_suggestion_widget_type', $instance['type'] );
        $query_var['add_to_cart'] = apply_filters( 'cart_suggestion_widget_add_to_cart', $instance['add_to_cart'] );
        $query_var['slider_count'] = apply_filters( 'cart_suggestion_widget_slider_count', $instance['slider_count'] );
        $query_var['args'] = $args;
        set_query_var('berocket_cart_suggestion_widget', $query_var);
        ob_start();
        $BeRocket_cart_suggestion->br_get_template_part( apply_filters( 'cart_suggestion_widget_template', 'widget' ) );
        $content = ob_get_clean();
        if( ! empty($content) ) {
            if( ! empty($args['before_widget']) ) echo $args['before_widget'];
            echo '<div class="woocommerce">';
            echo $content;
            echo '</div>';
            if( ! empty($args['after_widget']) ) echo $args['after_widget'];
        }
	}
    /**
     * Update widget settings
     */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['count'] = (int)$new_instance['count'];
		$instance['slider_count'] = (int)$new_instance['slider_count'];
        if( $instance['count'] < 1 ) {
            $instance['count'] = 1;
        }
		$instance['type'] = strip_tags( $new_instance['type'] );
        if( @ $new_instance['add_to_cart'] ) {
            $instance['add_to_cart'] = 1;
        } else {
            $instance['add_to_cart'] = 0;
        }
		return $instance;
	}
    /**
     * Widget settings form
     */
	public function form($instance)
	{
        $instance = wp_parse_args( (array) $instance, self::$defaults );
		$title = strip_tags($instance['title']);
		$count = strip_tags($instance['count']);
		$type = strip_tags($instance['type']);
		$add_to_cart = strip_tags($instance['add_to_cart']);
		$slider_count = strip_tags($instance['slider_count']);
		?>
        <div class="brcs_widget_setting">
		<p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" >
        </p>
		<p>
            <label for="<?php echo $this->get_field_id('count'); ?>"><?php _e('Products count:'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('count'); ?>" name="<?php echo $this->get_field_name('count'); ?>" type="number" min="1" value="<?php echo esc_attr( $count ); ?>" >
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('type'); ?>"><?php _e('Widget type:'); ?></label>
            <select class="brcs_wid_type" id="<?php echo $this->get_field_id('type'); ?>" name="<?php echo $this->get_field_name('type'); ?>">
                <?php
                    $types = array('default' => 'Default', 'image' => 'Image', 'image_title' => 'Image with Title', 'image_title_price' => 'Image with Title and Price', 'title' => 'Title', 'title_price' => 'Title with Price', 'slider' => 'Slider', 'slider_title' => 'Slider with title');
                    foreach( $types as $t_val => $t_name ) {
                        echo '<option value="', $t_val, '"', ($t_val == $type ? ' selected' : ''), '>', $t_name, '</option>';
                    }
                ?>
            </select>
        </p>
		<p>
            <label for="<?php echo $this->get_field_id('add_to_cart'); ?>"><?php _e('Add to cart button:'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('add_to_cart'); ?>" name="<?php echo $this->get_field_name('add_to_cart'); ?>" type="checkbox" value="1" <?php if( $add_to_cart ) echo 'checked'; ?>>
        </p>
        <p>
            <label><?php _e('Products per line:'); ?></label>
            <select name="<?php echo $this->get_field_name('slider_count'); ?>">
                <?php
                for($i = 0; $i < 10; $i++) {
                    echo '<option value="', $i, '"', ($slider_count == $i ? ' selected' : ''), '>', ($i + 1), '</option>';
                }
                ?>
            </select>
        </p>
        </div>
		<?php
	}
}
?>

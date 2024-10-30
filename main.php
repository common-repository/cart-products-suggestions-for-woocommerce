<?php
define( "BeRocket_cart_suggestion_domain", 'cart-products-suggestions-for-woocommerce'); 
define( "cart_suggestion_TEMPLATE_PATH", plugin_dir_path( __FILE__ ) . "templates/" );
load_plugin_textdomain('cart-products-suggestions-for-woocommerce', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/');
require_once(plugin_dir_path( __FILE__ ).'berocket/framework.php');
foreach (glob(__DIR__ . "/includes/*.php") as $filename)
{
    include_once($filename);
}
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

class BeRocket_cart_suggestion extends BeRocket_Framework {
    public static $settings_name = 'br-cart_suggestion-options';
    protected static $instance;
    protected $disable_settings_for_admin = array(
        array('script', 'js_page_load'),
    );
    function __construct () {
        $this->info = array(
            'id'          => 11,
            'lic_id'      => 71,
            'version'     => BeRocket_cart_suggestion_version,
            'plugin'      => '',
            'slug'        => '',
            'key'         => '',
            'name'        => '',
            'plugin_name' => 'cart_suggestion',
            'full_name'   => 'WooCommerce Cart Suggestions',
            'norm_name'   => 'Cart Suggestions',
            'price'       => '',
            'domain'      => 'cart-products-suggestions-for-woocommerce',
            'templates'   => cart_suggestion_TEMPLATE_PATH,
            'plugin_file' => BeRocket_cart_suggestion_file,
            'plugin_dir'  => __DIR__,
        );
        $this->defaults = array(
            'display_before_cart_table' => '0',
            'custom_css_class'          => '',
            'display_after_cart_table'  => '1',
            'display_after_cart_total'  => '0',
            'widget_style'              => 'default',
            'slide_count'               => '3',
            'suggestions_title'         => 'Maybe You want something from this...',
            'max_suggestions_count'     => '3',
            'custom_css'                => '',
            'script'                    => array(
                'js_page_load'              => '',
            ),
            'fontawesome_frontend_disable'    => '',
            'fontawesome_frontend_version'    => '',
        );
        $this->values = array(
            'settings_name' => 'br-cart_suggestion-options',
            'option_page'   => 'br-cart_suggestion',
            'premium_slug'  => 'woocommerce-cart-suggestions',
            'free_slug'     => 'cart-products-suggestions-for-woocommerce',
            'hpos_comp'     => true
        );
        $this->feature_list = array();
        $this->framework_data['fontawesome_frontend'] = true;
        if( method_exists($this, 'include_once_files') ) {
            $this->include_once_files();
        }
        if ( $this->init_validation() ) {
            new BeRocket_cart_suggestion_custom_post();
        }
        parent::__construct( $this );

        if ( $this->init_validation() ) {
            $options = $this->get_option();
            add_filter( 'woocommerce_add_to_cart_fragments', array( $this, 'woocommerce_add_to_cart_fragments' ), 10, 1 );
            add_filter ( 'BeRocket_updater_menu_order_custom_post', array($this, 'menu_order_custom_post') );
            add_action ( "widgets_init", array( $this, 'widgets_init' ) );
            add_shortcode( 'br_cart_suggestion', array( $this, 'shortcode' ) );
            require_once(plugin_dir_path( __FILE__ ).'includes/check/suggestion_check.php');
            add_action( 'divi_extensions_init', array($this, 'divi_initialize_extension') );
        }
    }
    function init_validation() {
        return ( ( is_plugin_active( 'woocommerce/woocommerce.php' ) || is_plugin_active_for_network( 'woocommerce/woocommerce.php' ) ) && 
            br_get_woocommerce_version() >= 2.1 );
    }
    public function update_from_older($last_version) {
        global $wpdb;
        $option = $this->get_option();
        if( $last_version == 0 ) {
            $BeRocket_cart_suggestion_custom_post = BeRocket_cart_suggestion_custom_post::getInstance();
            if( isset($option['default_suggest']) && is_array($option['default_suggest']) && count($option['default_suggest']) ) {
                $post_options = array(
                    'condition' => array(),
                    'condition_mode' => 'each',
                    'products' => $option['default_suggest']
                );
                $post_id = $BeRocket_cart_suggestion_custom_post->create_new_post(
                    array(
                        'post_title' => 'Default'
                    ),
                    $post_options
                );
                update_post_meta( $post_id, 'berocket_post_order', 10 );
            }
            if( isset($option['category_suggest']) && is_array($option['category_suggest']) ) {
                foreach($option['category_suggest'] as $category_id => $products) {
                    $category_id = (int)$category_id;
                    $term = get_term($category_id);
                    $products = @ $products['products'];
                    if( is_array($products) && count($products) && ! is_wp_error($term) && $term ) {
                        $post_options = array(
                            'condition' => array(
                                '1' => array(
                                    '1' => array(
                                        'equal' => 'equal',
                                        'type' => 'category',
                                        'category' => array(
                                            $category_id
                                        )
                                    )
                                )
                            ),
                            'condition_mode' => 'each',
                            'products' => $products
                        );
                        $post_id = $BeRocket_cart_suggestion_custom_post->create_new_post(
                            array(
                                'post_title' => 'Category: '.$term->name
                            ),
                            $post_options
                        );
                        update_post_meta( $post_id, 'berocket_post_order', 5 );
                    }
                }
            }
            if( isset($option['product_suggest']) && is_array($option['product_suggest']) ) {
                foreach($option['product_suggest'] as $products) {
                    $condition_products = @ $products['product_ids'];
                    $suggest_products = @ $products['products'];
                    if( is_array($suggest_products) && count($suggest_products) && is_array($condition_products) && count($condition_products) ) {
                        $post_options = array(
                            'condition' => array(
                                '1' => array(
                                    '1' => array(
                                        'equal' => 'equal',
                                        'type' => 'product',
                                        'product' => $condition_products
                                    )
                                )
                            ),
                            'condition_mode' => 'each',
                            'products' => $suggest_products
                        );
                        $post_id = $BeRocket_cart_suggestion_custom_post->create_new_post(
                            array(
                                'post_title' => 'Products: '.implode(', ', $condition_products)
                            ),
                            $post_options
                        );
                        update_post_meta( $post_id, 'berocket_post_order', 1 );
                    }
                }
            }
        }
        update_option("berocket_{$this->info['plugin_name']}_version", $this->info['version']);
    }
    public function woocommerce_add_to_cart_fragments( $fragments ) {
        ob_start();
        $this->after_cart();
        $fragments['div.br_cart_suggestions_cart'] = ob_get_clean();
        
        return $fragments;
        
    }
    public function widgets_init() {
        register_widget("berocket_cart_suggestion_widget");
    }
    public function shortcode($atts = array()) {
        if(isset($atts['slider_count'])) {
            $atts['slider_count'] = (int)$atts['slider_count'];
            $atts['slider_count']--;
            if($atts['slider_count'] < 0) {
                $atts['slider_count'] = 0;
            }
        }
        if(isset($atts['count'])) {
            $atts['count'] = (int)$atts['count'];
            $atts['count']--;
            if($atts['count'] < 1) {
                $atts['count'] = 1;
            }
        }
        ob_start();
        the_widget( 'berocket_cart_suggestion_widget', $atts );
        $return = ob_get_clean();
        return $return;
    }
    public function init () {
        parent::init();
        $last_version = get_option("berocket_{$this->info['plugin_name']}_version");
        if( $last_version === FALSE ) $last_version = 0;
        if ( version_compare($last_version, $this->info['version'], '<') ) {
            $this->update_from_older ( $last_version );
        }
        unset($last_version);
        $options = $this->get_option();
        wp_enqueue_script("jquery");
        wp_enqueue_script( 'berocket_cart_suggestion_frontend', plugins_url( 'js/suggestions.js', __FILE__ ), array( 'jquery' ), BeRocket_cart_suggestion_version );
        wp_register_style( 'berocket_cart_suggestion_style', plugins_url( 'css/frontend.css', __FILE__ ), "", BeRocket_cart_suggestion_version );
        wp_enqueue_style( 'berocket_cart_suggestion_style' );
        wp_register_style( 'berocket_cart_suggestion_slider', plugins_url( 'css/unslider.css', __FILE__ ) );
        wp_enqueue_style( 'berocket_cart_suggestion_slider' );
        wp_enqueue_script( 'berocket_cart_suggestion_slider_js', plugins_url( 'js/unslider-min.js', __FILE__ ), array( 'jquery' ) );
        if($options['display_before_cart_table']) {
            add_action( 'woocommerce_before_cart_table', array( $this, 'after_cart' ) );
        }
        if($options['display_after_cart_table']) {
            add_action( 'woocommerce_after_cart_table', array( $this, 'after_cart' ) );
        }
        if($options['display_after_cart_total']) {
            add_action( 'woocommerce_after_cart', array( $this, 'after_cart' ) );
        }
        if( isset($options['display_hooks']) && is_array($options['display_hooks']) ) {
            foreach($options['display_hooks'] as $hook) {
                if( $hook == 'before_the_content' ) {
                    add_filter('the_content', array($this, 'before_the_content'));
                } elseif( $hook == 'after_the_content' ) {
                    add_filter('the_content', array($this, 'after_the_content'));
                } else {
                    add_action( $hook, array( $this, 'after_cart' ) );
                }
            }
        }
    }
    public function before_the_content($content) {
        if( is_main_query() && is_cart() ) {
            remove_filter('the_content', array($this, 'before_the_content'));
            $add_filter = false;
            if( has_filter( 'the_content', array($this, 'after_the_content') ) ) {
                remove_filter('the_content', array($this, 'after_the_content'));
                $add_filter = true;
            }
            ob_start();
            $this->after_cart();
            $after_cart = ob_get_clean();
            $content = $after_cart . $content;
            if( $add_filter ) {
                add_filter('the_content', array($this, 'after_the_content'));
            }
        }
        return $content;
    }
    public function after_the_content($content) {
        if( is_main_query() && is_cart() ) {
            remove_filter('the_content', array($this, 'after_the_content'));
            $add_filter = false;
            if( has_filter( 'the_content', array($this, 'before_the_content') ) ) {
                remove_filter('the_content', array($this, 'before_the_content'));
                $add_filter = true;
            }
            ob_start();
            $this->after_cart();
            $after_cart = ob_get_clean();
            $content = $content . $after_cart;
            if( $add_filter ) {
                add_filter('the_content', array($this, 'before_the_content'));
            }
        }
        return $content;
    }
    public function after_cart() {
        $options = $this->get_option();
        $products = apply_filters('berocket_cart_suggestion_get_products', array(), $options['max_suggestions_count']);
        $additional = array('slider_count' => $options['slide_count']);
        ob_start();
        $this->print_products($products, $options['widget_style'], true, $additional);
        $products_list = ob_get_clean();
        echo '<div class="br_cart_suggestions_cart ' . (empty($options['custom_css_class']) ? '' : $options['custom_css_class']) . '">
            <div class="woocommerce">';
        if( ! empty( $products_list ) ) {
            if( isset($options['suggestions_title']) && $options['suggestions_title'] ) {
                echo '<h4>'.$options['suggestions_title'].'</h4>';
            }
            echo $products_list;
        }
        echo '</div></div>';
    }
    public function print_products ( $products, $display_type = false, $add_to_cart = false, $additional = array() ) {
        $options = $this->get_option();
        if (! empty($products) ) {
            $args = array(
                'post_type'         => array('product', 'product_variation'),
                'post__in'          => $products,
                'posts_per_page'    => '-1',
                'orderby'           => 'rand'
            );
        } else {
            return;
        }
        $brcs_slider_rand = rand();
        $loop = new WP_Query( $args );
        $slider_count_max = 3;
        ob_start();
        $products_displayed = 0;
        if( isset($additional['slider_count']) ) {
            $slider_count_max = $additional['slider_count'];
            echo '<style>.br_cart_suggestions.br_cart_suggestions_', $brcs_slider_rand, ' .brcs_product{box-sizing:border-box;width:'.(100 / ($additional['slider_count']+1)).'%!important;}</style>';
        }
        ?>
        <div class="br_cart_suggestions br_cart_suggestions_<?php echo $brcs_slider_rand; ?>">
        <?php
        if ($display_type === false || $display_type == 'default' ) {
            echo '<style>.brcs_products > * {display: inline-block;float:left;}</style>';
            add_filter ( 'post_class', array( $this, 'product_class' ), 9999, 3 );
            echo '<ul class="brcs_products">';
            $i = 0;

            if ($loop->have_posts()) : while ($loop->have_posts()) : $loop->the_post(); global $product, $post;
                $product = wc_get_product(get_the_ID());
                $post = get_post( get_the_ID() );
                if ( !$product->is_visible() ) continue;
                wc_get_template_part( 'content', 'product' );
                $products_displayed++;
                if( $slider_count_max <= $i ) {
                    $i = 0;
                    echo '<div style="clear: both;"></div>';
                } else {
                    $i++;
                }
            endwhile; endif;
            echo '</ul>';
            remove_filter ( 'post_class', array( __CLASS__, 'product_class' ), 9999, 3 );
        } elseif( $display_type == 'image' || $display_type == 'image_title' || $display_type == 'image_title_price' ) {
            ?>
            <ul class="brcs_image">
            <?php
                if ($loop->have_posts()) : while ($loop->have_posts()) : $loop->the_post(); global $product;
                    $product = wc_get_product(get_the_ID());
                    if ( !$product->is_visible() ) continue;
                    echo '<li class="brcs_product"><a href="', get_permalink(br_wc_get_product_id($product)), '">', woocommerce_get_product_thumbnail(), ($display_type == 'image_title' ? $product->get_title() : ($display_type == 'image_title_price' ? $product->get_title().' - '.( function_exists('wc_price') ? wc_price( $product->get_price() ) : woocommerce_price( $product->get_price() ) ) : '')), '</a>';
                    if ( $add_to_cart ) {
                        woocommerce_template_loop_add_to_cart();
                    }
                    $products_displayed++;
                    echo '</li>';
                endwhile; endif;
            ?>
            </ul>
            <?php
        } elseif( $display_type == 'title' || $display_type == 'title_price' ) {
            ?>
            <ul class="brcs_name">
            <?php
                if ($loop->have_posts()) : while ($loop->have_posts()) : $loop->the_post(); global $product;
                    $product = wc_get_product(get_the_ID());
                    if ( !$product->is_visible() ) continue;
                    echo '<li class="brcs_product"><a href="', get_permalink(br_wc_get_product_id($product)), '">', ($display_type == 'title' ? $product->get_title() : ($display_type == 'title_price' ? $product->get_title().' - '.( function_exists('wc_price') ? wc_price( $product->get_price() ) : woocommerce_price( $product->get_price() ) ) : '')), '</a>';
                    if ( $add_to_cart ) {
                        woocommerce_template_loop_add_to_cart();
                    }
                    $products_displayed++;
                    echo '</li>';
                endwhile; endif;
            ?>
            </ul>
            <?php
        } elseif( $display_type == 'slider' || $display_type == 'slider_title' ) {
            ?>
            <div class="brcs_slider_suggestion brcs_slider_<?php echo $brcs_slider_rand; ?>">
                <ul>
            <?php
                $slide_count = 0;
                if ($loop->have_posts()) : while ($loop->have_posts()) : $loop->the_post(); global $product;
                    $product = wc_get_product(get_the_ID());
                    if ( !$product->is_visible() ) continue;
                    if( $slide_count == 0 ) {
                        echo '<li class="brcs_slide">';
                    }
                    echo '<div class="brcs_product"><a href="', get_permalink(br_wc_get_product_id($product)), '">', woocommerce_get_product_thumbnail(), ($display_type == 'slider_title' ? $product->get_title() : ''), '</a>';
                    if ( $add_to_cart ) {
                        woocommerce_template_loop_add_to_cart();
                    }
                    echo '</div>';
                    if( $slide_count == $slider_count_max ) {
                        echo '</li>';
                        $slide_count = -1;
                    }
                    $products_displayed++;
                    $slide_count++;
                endwhile; 
                if( $slide_count != 0 ) {
                    echo '</li>';
                }
                endif;
            ?>
                </ul>
            </div>
            <?php
        }
        ?>
            <script>if( typeof brcs_generate_slider == 'function' ) brcs_generate_slider();</script>
        <div style="clear:both; height:1px;"></div>
        </div>
        <?php
        if( $products_displayed == 0 ) {
            ob_end_clean();
        } else {
            echo ob_get_clean();
        }
        wp_reset_query();
    }
    public function product_class($classes) {
        $classes[] = 'brcs_product';
        return $classes;
    }
    public function add_additional_suggest ( $current_array, $needed_count, $added_array, $exist_products ) {
        if( is_array($added_array) && is_array($exist_products) && count($exist_products) > 0 ) {
            if( ! isset($exist_products) || ! is_array($exist_products) ) {
                $exist_products = array();
            }
            $added_array = array_diff( $added_array, $current_array, $exist_products );
            $needed_count = $needed_count - count($current_array);
            if( count($added_array) < $needed_count ) {
                $current_array += array_merge($current_array, $added_array);
                $current_array = array_unique( $current_array );
            } else {
                $random = array_rand ( $added_array, $needed_count );
                if( is_array($random) ) {
                    foreach ( $random as $rand ) {
                        $current_array[] = $added_array[$rand];
                    }
                } else {
                    $current_array[] = $added_array[$random];
                }
            }
        }
        return $current_array;
    }
    public function set_styles () {
        $options = $this->get_option();
        parent::set_styles();
        if( ! empty($options['script']['js_page_load']) ) {
            echo '<script>jQuery(document).ready(function() {
                setTimeout(function() {
                    '.$options['script']['js_page_load'].'
                }, 1);
            });</script>';
        }
    }
    public function admin_settings( $tabs_info = array(), $data = array() ) {
        parent::admin_settings(
            array(
                'General' => array(
                    'icon' => 'cog',
                ),
                'Cart Suggestion' => array(
                    'icon' => 'plus-square',
                    'link' => admin_url( 'edit.php?post_type=br_suggestion' ),
                ),
                'Custom CSS/JS' => array(
                    'icon' => 'css3'
                ),
                'License' => array(
                    'icon' => 'unlock-alt',
                    'link' => admin_url( 'admin.php?page=berocket_account' ),
                ),
            ),
            array(
            'General' => array(
                'custom_css_class' => array(
                    "label"     => __('Custom CSS Class', 'cart-products-suggestions-for-woocommerce'),
                    "name"     => "custom_css_class",   
                    "type"     => "text",
                    "value"    => '',
                ),
                'display_position' => array(
                    "label"     => __('Display position', 'cart-products-suggestions-for-woocommerce'),
                    "items" => array(
                        array(
                            "name"     => "display_before_cart_table",
                            "type"     => "checkbox",
                            "value"    => '1',
                            "label_for" => __( 'Before cart table' , "cart-products-suggestions-for-woocommerce" ),
                        ),
                        array(
                            "name"     => "display_after_cart_table",
                            "type"     => "checkbox",
                            "value"    => '1',
                            "label_for" => __( 'After cart table' , "cart-products-suggestions-for-woocommerce" ),
                            "label_be_for" => '<br>',
                        ),
                        array(
                            "name"     => "display_after_cart_total",
                            "type"     => "checkbox",
                            "value"    => '1',
                            "label_for" => __( 'After cart total' , "cart-products-suggestions-for-woocommerce" ),
                            "label_be_for" => '<br>',
                        ),
                        array(
                            "name"     => array("display_hooks", '0'),
                            "type"     => "checkbox",
                            "value"    => 'woocommerce_before_cart',
                            "label_for" => __( 'Before cart table 2' , "cart-products-suggestions-for-woocommerce" ),
                            "label_be_for" => '<br>',
                        ),
                        array(
                            "name"     => array("display_hooks", '1'),
                            "type"     => "checkbox",
                            "value"    => 'before_the_content',
                            "label_for" => __( 'Before cart page content' , "cart-products-suggestions-for-woocommerce" ),
                            "label_be_for" => '<br>',
                        ),
                        array(
                            "name"     => array("display_hooks", '2'),
                            "type"     => "checkbox",
                            "value"    => 'after_the_content',
                            "label_for" => __( 'After cart page content' , "cart-products-suggestions-for-woocommerce" ),
                            "label_be_for" => '<br>',
                        ),
                    )
                ),
                'style' => array(
                    "label"     => __('Style', 'cart-products-suggestions-for-woocommerce'),
                    "name"     => "widget_style",
                    "type"     => "selectbox",
                    "options"  => array(
                        array('value' => 'default', 'text' => __('Default', 'cart-products-suggestions-for-woocommerce')),
                        array('value' => 'image', 'text' => __('Image', 'cart-products-suggestions-for-woocommerce')),
                        array('value' => 'image_title', 'text' => __('Image with Title', 'cart-products-suggestions-for-woocommerce')),
                        array('value' => 'image_title_price', 'text' => __('Image with Title and Price', 'cart-products-suggestions-for-woocommerce')),
                        array('value' => 'title', 'text' => __('Title', 'cart-products-suggestions-for-woocommerce')),
                        array('value' => 'title_price', 'text' => __('Title with Price', 'cart-products-suggestions-for-woocommerce')),
                        array('value' => 'slider', 'text' => __('Slider', 'cart-products-suggestions-for-woocommerce')),
                        array('value' => 'slider_title', 'text' => __('Slider with title', 'cart-products-suggestions-for-woocommerce')),
                    ),
                    "value"    => '',
                ),
                'slide_count' => array(
                    "label"     => __('Products per line', 'cart-products-suggestions-for-woocommerce'),
                    "name"     => "slide_count",
                    "type"     => "selectbox",
                    "options"  => array(
                        array('value' => '0', 'text' => __('1', 'cart-products-suggestions-for-woocommerce')),
                        array('value' => '1', 'text' => __('2', 'cart-products-suggestions-for-woocommerce')),
                        array('value' => '2', 'text' => __('3', 'cart-products-suggestions-for-woocommerce')),
                        array('value' => '3', 'text' => __('4', 'cart-products-suggestions-for-woocommerce')),
                        array('value' => '4', 'text' => __('5', 'cart-products-suggestions-for-woocommerce')),
                        array('value' => '5', 'text' => __('6', 'cart-products-suggestions-for-woocommerce')),
                        array('value' => '6', 'text' => __('7', 'cart-products-suggestions-for-woocommerce')),
                        array('value' => '7', 'text' => __('8', 'cart-products-suggestions-for-woocommerce')),
                        array('value' => '8', 'text' => __('9', 'cart-products-suggestions-for-woocommerce')),
                        array('value' => '9', 'text' => __('10', 'cart-products-suggestions-for-woocommerce')),
                    ),
                    "value"    => '',
                ),
                'suggestions_title' => array(
                    "label"     => __('Suggestions Title', 'cart-products-suggestions-for-woocommerce'),
                    "name"     => "suggestions_title",   
                    "type"     => "text",
                    "value"    => '',
                ),
                'max_suggestions_count' => array(
                    "label"     => __('Max Suggestions Count', 'cart-products-suggestions-for-woocommerce'),
                    "name"     => "max_suggestions_count",   
                    "type"     => "number",
                    "value"    => '',
                ),
                'remove_variation' => array(
                    "label"     => __('Same product variation', 'cart-products-suggestions-for-woocommerce'),
                    "label_for" => __( 'Remove variation from suggestions if another variation from same product in cart' , "cart-products-suggestions-for-woocommerce" ),
                    "name"      => "remove_variation",
                    "type"      => "checkbox",
                    "value"     => '1',
                ),
                'global_font_awesome_disable' => array(
                    "label"     => __( 'Disable Font Awesome', "cart-products-suggestions-for-woocommerce" ),
                    "type"      => "checkbox",
                    "name"      => "fontawesome_frontend_disable",
                    "value"     => '1',
                    'label_for' => __('Don\'t loading css file for Font Awesome on site front end. Use it only if you doesn\'t uses Font Awesome icons in widgets or you have Font Awesome in your theme.', 'cart-products-suggestions-for-woocommerce'),
                ),
                'global_fontawesome_version' => array(
                    "label"    => __( 'Font Awesome Version', "cart-products-suggestions-for-woocommerce" ),
                    "name"     => "fontawesome_frontend_version",
                    "type"     => "selectbox",
                    "options"  => array(
                        array('value' => '', 'text' => __('Font Awesome 4', 'cart-products-suggestions-for-woocommerce')),
                        array('value' => 'fontawesome5', 'text' => __('Font Awesome 5', 'cart-products-suggestions-for-woocommerce')),
                    ),
                    "value"    => '',
                    "label_for" => __('Version of Font Awesome that will be used on front end. Please select version that you have in your theme', 'cart-products-suggestions-for-woocommerce'),
                ),
                'shortcode' => array(
                    'label'     => '',
                    'section'   => 'shortcode'
                ),
            ),
            'Custom CSS/JS' => array(
                array(
                    "label"   => "Custom CSS",
                    "name"    => "custom_css",
                    "type"    => "textarea",
                    "value"   => "",
                ),
                array(
                    "label"   => "JavaScript on page load",
                    "name"    => array("script", "js_page_load"),
                    "type"    => "textarea",
                    "value"   => "",
                ),
            ),
        ) );
    }
    public function section_shortcode($option) {
        $html = '<th>Shortcode</th><td>
        <strong>[br_cart_suggestion title="title" type="default" count=4 add_to_cart=0 slide_count=4]</strong> - Display suggested products
        <p><strong>Parameters:</strong></p>
        <ul>
            <li><i>title</i> - text title (text)</li>
            <li><i>type</i> - display type(default/image/image_title/image_title_price/title/title_price)</li>
            <li><i>count</i> - count of products (number)</li>
            <li><i>add_to_cart</i> - display add to cart button or not (1/0)</li>
            <li><i>slide_count</i> - products displayed per line or per slide (number)</li>
        </ul>
        </td>';
        return print_r($html, true);
    }
    public function admin_init() {
        parent::admin_init();
        wp_enqueue_script( 'berocket_cart_suggestion_admin', plugins_url( 'js/admin.js', __FILE__ ), array( 'jquery' ), BeRocket_cart_suggestion_version );
        wp_register_style( 'berocket_cart_suggestion_admin_style', plugins_url( 'css/admin.css', __FILE__ ), "", BeRocket_cart_suggestion_version );
        wp_enqueue_style( 'berocket_cart_suggestion_admin_style' );
    }
    public function menu_order_custom_post($compatibility) {
        $compatibility['br_suggestion'] = 'br-cart_suggestion';
        return $compatibility;
    }
    public function divi_initialize_extension() {
        require_once plugin_dir_path( __FILE__ ) . 'divi/includes/CartSuggestionExtension.php';
    }
}

new BeRocket_cart_suggestion;

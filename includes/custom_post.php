<?php
class BeRocket_conditions_cart_suggestion extends BeRocket_conditions {
    public static function move_product_var_to_product($additional) {
        if( ! empty($additional['var_product_id']) ) {
            $additional['product_id'] = $additional['var_product_id'];
        }
        if( ! empty($additional['var_product']) ) {
            $additional['product'] = $additional['var_product'];
        }
        if( ! empty($additional['var_product_post']) ) {
            $additional['product_post'] = $additional['var_product_post'];
        }
        return $additional;
    }
    public static function check_prepare_data($show, $condition, $additional, $function) {
        $condition_mode = br_get_value_from_array($additional['settings_cart_suggestion'], 'condition_mode');
        if( $condition_mode == 'cart' ) {
            if( ! isset($additional['cart']) ) {
                return true;
            } else {
                foreach($additional['cart'] as $cart_item) {
                    $new_additional = $cart_item;
                    $new_additional['product_variables'] = $cart_item;
                    $new_additional['settings_cart_suggestion'] = $additional['settings_cart_suggestion'];
                    if( self::check_prepare_data_execute_after($show, $condition, $new_additional, $function) ) {
                        return true;
                    }
                }
                return false;
            }
        }
        if( ! isset($additional['cart']) ) {
            return self::check_prepare_data_execute_after($show, $condition, $additional, $function);
        } else {
            return true;
        }
    }
    public static function check_prepare_data_execute_after($show, $condition, $additional, $function) {
        if( method_exists(__CLASS__, $function.'2') ) {
            return self::{$function.'2'}($show, $condition, $additional);
        } else {
            $additional = self::move_product_var_to_product($additional);
            return parent::$function($show, $condition, $additional);
        }
    }
    public static function check_condition_product($show, $condition, $additional) {
        return self::check_prepare_data($show, $condition, $additional, __FUNCTION__);
    }
    public static function check_condition_product_sale($show, $condition, $additional) {
        return self::check_prepare_data($show, $condition, $additional, __FUNCTION__);
    }
    public static function check_condition_product_bestsellers($show, $condition, $additional) {
        return self::check_prepare_data($show, $condition, $additional, __FUNCTION__);
    }
    public static function check_condition_product_price($show, $condition, $additional) {
        return self::check_prepare_data($show, $condition, $additional, __FUNCTION__);
    }
    public static function check_condition_product_stockstatus($show, $condition, $additional) {
        return self::check_prepare_data($show, $condition, $additional, __FUNCTION__);
    }
    public static function check_condition_product_totalsales($show, $condition, $additional) {
        return self::check_prepare_data($show, $condition, $additional, __FUNCTION__);
    }
    public static function check_condition_product_attribute($show, $condition, $additional) {
        return self::check_prepare_data($show, $condition, $additional, __FUNCTION__);
    }
    public static function check_condition_product_age($show, $condition, $additional) {
        return self::check_prepare_data($show, $condition, $additional, __FUNCTION__);
    }
    public static function check_condition_product_saleprice($show, $condition, $additional) {
        return self::check_prepare_data($show, $condition, $additional, __FUNCTION__);
    }
    public static function check_condition_product_stockquantity($show, $condition, $additional) {
        return self::check_prepare_data($show, $condition, $additional, __FUNCTION__);
    }
    public static function check_condition_product_category($show, $condition, $additional) {
        return self::check_prepare_data($show, $condition, $additional, __FUNCTION__);
    }
    public static function check_condition_product_category2($show, $condition, $additional) {
        if ( $additional['product']->is_type( 'variation' ) ) {
            $additional['product_id'] = $additional['product']->get_parent_id();
        }
        $product_id = $additional['product_id'];
        if( ! is_array($condition['category']) ) {
            $condition['category'] = array($condition['category']);
        }
        $terms = get_the_terms( $product_id, 'product_cat' );
        if( is_array( $terms ) ) {
            foreach( $terms as $term ) {
                if( in_array($term->term_id, $condition['category']) ) {
                    $show = true;
                }
                if( ! empty($condition['subcats']) && ! $show ) {
                    foreach($condition['category'] as $category) {
                        $show = term_is_ancestor_of($category, $term->term_id, 'product_cat');
                        if( $show ) {
                            break;
                        }
                    }
                }
                if($show) break;
            }
        }
        if( $condition['equal'] == 'not_equal' ) {
            $show = ! $show;
        }
        return $show;
    }
    public static function check_condition_product_attribute2($show, $condition, $additional) {
        $terms = array();
        if ( $additional['product']->is_type( 'variation' ) ) {
            $var_attributes = $additional['product']->get_variation_attributes();
            if( ! empty($var_attributes['attribute_'.$condition['attribute']]) ) {
                $term = get_term_by('slug', $var_attributes['attribute_'.$condition['attribute']], $condition['attribute']);
                if( $term !== false ) {
                    $terms[] = $term;
                }
            }
        }
        if( ! count($terms) ) {
            $product_id = $additional['product_id'];
            if ( $additional['product']->is_type( 'variation' ) ) {
                $product_id = $additional['product']->get_parent_id();
            }
            $terms = get_the_terms( $additional['product_id'], $condition['attribute'] );
        }
        if( is_array( $terms ) ) {
            foreach( $terms as $term ) {
                if( $term->term_id == $condition['values'][$condition['attribute']]) {
                    $show = true;
                    break;
                }
            }
        }
        if( $condition['equal'] == 'not_equal' ) {
            $show = ! $show;
        }
        return $show;
    }
}
class BeRocket_cart_suggestion_custom_post extends BeRocket_custom_post_class {
    public $hook_name = 'berocket_cart_suggestion_custom_post';
    public $conditions;
    public $post_type_parameters = array(
        'sortable' => true,
        'can_be_disabled' => true
    );
    protected static $instance;
    function __construct() {
        add_action('BeRocket_framework_init_plugin', array($this, 'init_conditions'));
        $this->post_name = 'br_suggestion';
        $this->post_settings = array(
            'label' => __( 'Suggestion', 'cart-products-suggestions-for-woocommerce' ),
            'labels' => array(
                'name'               => __( 'Suggestions', 'cart-products-suggestions-for-woocommerce' ),
                'singular_name'      => __( 'Suggestion', 'cart-products-suggestions-for-woocommerce' ),
                'menu_name'          => _x( 'Suggestions', 'Admin menu name', 'cart-products-suggestions-for-woocommerce' ),
                'add_new'            => __( 'Add Suggestion', 'cart-products-suggestions-for-woocommerce' ),
                'add_new_item'       => __( 'Add New Suggestion', 'cart-products-suggestions-for-woocommerce' ),
                'edit'               => __( 'Edit', 'cart-products-suggestions-for-woocommerce' ),
                'edit_item'          => __( 'Edit Suggestion', 'cart-products-suggestions-for-woocommerce' ),
                'new_item'           => __( 'New Suggestion', 'cart-products-suggestions-for-woocommerce' ),
                'view'               => __( 'View Suggestions', 'cart-products-suggestions-for-woocommerce' ),
                'view_item'          => __( 'View Suggestion', 'cart-products-suggestions-for-woocommerce' ),
                'search_items'       => __( 'Search Suggestions', 'cart-products-suggestions-for-woocommerce' ),
                'not_found'          => __( 'No Suggestions found', 'cart-products-suggestions-for-woocommerce' ),
                'not_found_in_trash' => __( 'No Suggestions found in trash', 'cart-products-suggestions-for-woocommerce' ),
            ),
            'description'     => __( 'This is where you can add new suggestions that you can add to products.', 'cart-products-suggestions-for-woocommerce' ),
            'public'          => true,
            'show_ui'         => true,
            'capability_type' => 'post',
            'publicly_queryable'  => false,
            'exclude_from_search' => true,
            'show_in_menu'        => 'berocket_account',
            'hierarchical'        => false,
            'rewrite'             => false,
            'query_var'           => false,
            'supports'            => array( 'title' ),
            'show_in_nav_menus'   => false,
        );
        $this->default_settings = array(
            'condition'         => array(),
            'condition_mode'=> '',
            'products'=> array(),
        );
        $this->add_meta_box('conditions', __( 'Conditions', 'cart-products-suggestions-for-woocommerce' ));
        $this->add_meta_box('settings', __( 'Cart Suggestion Settings', 'cart-products-suggestions-for-woocommerce' ));
        $this->add_meta_box('description', __( 'Description', 'cart-products-suggestions-for-woocommerce' ), false, 'side');
        parent::__construct();

        add_filter('brfr_'.$this->hook_name.'_price_var', array($this, 'price_var'), 20, 4);
        add_filter('brfr_'.$this->hook_name.'_time_var', array($this, 'time_var'), 20, 4);
        add_filter('brfr_'.$this->hook_name.'_products_var', array($this, 'products_var'), 20, 4);
        add_filter('brfr_'.$this->hook_name.'_category_var', array($this, 'category_var'), 20, 4);
    }
    public function init_conditions($info) {
        if( $info['id'] == 11 ) {
            $this->conditions = new BeRocket_conditions_cart_suggestion($this->post_name.'[condition]', $this->hook_name, array(
                'condition_product',
                'condition_product_category',
                'condition_product_attribute',
                'condition_product_age',
                'condition_product_saleprice',
                'condition_product_sale',
                'condition_product_bestsellers',
                'condition_product_price',
                'condition_product_stockstatus',
                'condition_product_stockquantity',
                'condition_product_totalsales',
            ));
        }
    }
    public function conditions($post) {
        $options = $this->get_option( $post->ID );
        if( empty($options['condition']) ) {
            $options['condition'] = array();
        }
        echo $this->conditions->build($options['condition']);
        $echo = apply_filters('BeRocket_cart_suggestion_custom_post_after_conditions', array(), $post);
        $echo = implode($echo);
        echo $echo;
    }
    public function description($post) {
        $html = '<p>Conditions uses to get needed products from cart that will be used for other limitations</p>';
        $html .= '<p>Each tab has own replacement for text that you can use to display variable data</p>';
        echo $html;
    }
    public function settings($post) {
        $options = $this->get_option( $post->ID );
        $BeRocket_cart_suggestion = BeRocket_cart_suggestion::getInstance();
        $product_categories = get_terms( 'product_cat' );
        $categories = array(array('value' => '', 'text' => ''));
        foreach($product_categories as $category) {
            $categories[] = array('value' => $category->term_id, 'text' => $category->name);
        }
        echo '<div class="br_framework_settings br_alabel_settings">';
        $BeRocket_cart_suggestion->display_admin_settings(
            array(
                'Products' => array(
                    'icon' => 'inbox',
                ),
                'Cart Limit' => array(
                    'icon' => 'dollar',
                ),
            ),
            array(
                'Products' => array(
                    'products' => array(
                        "type"     => "products",
                        "label"    => __('Suggested Products', 'cart-products-suggestions-for-woocommerce'),
                        "name"     => "products",
                        "value"    => $options['products'],
                    ),
                    'products_count' => array(
                        'type'     => 'number',
                        "label"    => __('Count', 'cart-products-suggestions-for-woocommerce'),
                        "name"     => "products_count",
                        'value'    => "",
                        "extra"    => "min=1 placeholder='".__('all', 'cart-products-suggestions-for-woocommerce')."'",
                        "label_for"=> ' '.__('limit count of products that will be used for product list from this suggestion(get random products)', 'cart-products-suggestions-for-woocommerce')
                    )
                ),
                'Cart Limit' => array(
                    'price_minimum' => array(
                        'type'     => 'number',
                        "label"    => __('Cart Price Minimum', 'cart-products-suggestions-for-woocommerce'),
                        "name"     => "price_minimum",
                        'value'    => "",
                        "extra"    => "min=1 placeholder='".__('Disabled', 'cart-products-suggestions-for-woocommerce')."'",
                        "label_for"=> ' '.__('Display only when cart price bigger', 'cart-products-suggestions-for-woocommerce')
                    ),
                    'price_maximum' => array(
                        'type'     => 'number',
                        "label"    => __('Cart Price Maximum', 'cart-products-suggestions-for-woocommerce'),
                        "name"     => "price_maximum",
                        'value'    => "",
                        "extra"    => "min=1 placeholder='".__('Disabled', 'cart-products-suggestions-for-woocommerce')."'",
                        "label_for"=> ' '.__('Display only when cart price lower', 'cart-products-suggestions-for-woocommerce')
                    )
                ),
            ),
            array(
                'name_for_filters' => $this->hook_name,
                'hide_header' => true,
                'hide_form' => true,
                'hide_additional_blocks' => true,
                'hide_save_button' => true,
                'settings_name' => $this->post_name,
                'options' => $options
            )
        );
        echo '</div>';
    }
    public function get_option( $post_id ) {
        $options = parent::get_option( $post_id );
        $options = apply_filters('berocket_'.$this->post_name.'_get_option', $options, $post_id);
        return $options;
    }
    public function html_order_postion($post_id, $order) {
        $html = '';
        if( $order > 0 ) {
            $html .= '<a href="#order-up" class="berocket_post_set_new_order" data-post_id="'.$post_id.'" data-order="'.($order - 1).'"><i class="fa fa-arrow-up"></i></a>';
        }
        $html .= '<span class="berocket_post_set_new_order_input"><input type="number" min="0" value="'.$order.'"><a class="berocket_post_set_new_order_set fa fa-arrow-circle-right" data-post_id="'.$post_id.'" href="#order-set"></a></span>';
        $html .= '<a href="#order-up" class="berocket_post_set_new_order" data-post_id="'.$post_id.'" data-order="'.($order + 1).'"><i class="fa fa-arrow-down"></i></a>';
        return $html;
    }
    public function wc_save_product_without_check( $post_id, $post ) {
        parent::wc_save_product_without_check( $post_id, $post );
        $settings = get_post_meta( $post_id, $this->post_name, true );
        if( method_exists($this->conditions, 'save') ) {
            $settings['condition'] = $this->conditions->save($settings['condition'], $this->hook_name);
        }
        if( isset($settings['products']) && is_array($settings['products']) ) {
            $products = array();
            foreach( $settings['products'] as $product ) {
                $wc_product = wc_get_product($product);
                if( $wc_product->get_type() == 'grouped' ) {
                    $children = $wc_product->get_children();
                    if( ! is_array($children) ) {
                        $children = array();
                    }
                    $products = array_merge($products, $children);
                } else {
                    $products[] = $product;
                }
            }
            $settings['products'] = array_unique($products);
        }
        update_post_meta( $post_id, $this->post_name, $settings );
    }
}

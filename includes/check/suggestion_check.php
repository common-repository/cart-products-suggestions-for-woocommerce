<?php
class berocket_cart_suggestion_check_cond {
    function __construct() {
        $options = $this->get_option();
        add_filter('berocket_cart_suggestion_condition_mode_cart', array($this, 'condition_mode_normal'), 10, 5);
        add_filter('berocket_cart_suggestion_condition_mode_each', array($this, 'condition_mode_each'), 10, 5);
        add_filter('berocket_cart_suggestion_check_result_other', array($this, 'check_result_other'), 10, 2);
        add_filter('BeRocket_cart_suggestion_custom_post_after_conditions', array($this, 'condition_additional'), 10, 2);
        add_filter('berocket_cart_suggestion_get_products', array($this, 'check_cart_and_add_linked'), 10, 2);
        add_filter('berocket_cart_suggestion_check_cart_data', array($this, 'check_global_settings'), 10, 2);
    }
    function check_cart_and_add_linked($products = array(), $count = 4) {
        $cart = WC()->cart;
        if( empty($cart) ) {
            return false;
        }
        $get_cart = $cart->get_cart();
        $BeRocket_cart_suggestion_custom_post = BeRocket_cart_suggestion_custom_post::getInstance();
        $cart_suggestion_ids = $BeRocket_cart_suggestion_custom_post->get_custom_posts_frontend();
        $cart_suggestion_options = array();
        $cart_suggestion_quantity = array();
        foreach($cart_suggestion_ids as $cart_suggestion_id) {
            $options = $BeRocket_cart_suggestion_custom_post->get_option($cart_suggestion_id);
            if( ! is_array($options) ) {
                $options = array('condition' => array());
            } elseif( empty($options['condition']) ) {
                $options['condition'] = array();
            }
            if( ! empty($options['products']) && is_array($options['products']) && count($options['products']) ) {
                $cart_suggestion_options[$cart_suggestion_id] = $options;
                $cart_suggestion_quantity[$cart_suggestion_id] = array('quantity' => 0, 'products' => array());
            }
        }
        $cart_post_exist = array();
        $product_list = array();
        $linked_products =  array();
        foreach($get_cart as $cart_item_key => $values) {
            //INIT PRODUCT VARIABLES
            $_product = $values['data'];
            if ( $_product->is_type( 'variation' ) ) {
                $_product = wc_get_product($values['variation_id']);
                $_product_post = br_wc_get_product_post($_product);
                $_product_id = br_wc_get_product_id($_product);
                $cart_post_exist[] = $_product_id;
                $cart_post_exist[] = $_product_post->post_parent;
            } else {
                $_product_post = br_wc_get_product_post($_product);
                $_product_id = br_wc_get_product_id($_product);
                $cart_post_exist[] = $_product_id;
            }
            $_product_qty = $values['quantity'];
            $check_additional = array(
                'product' => $_product,
                'product_post' => $_product_post,
                'product_id' => $_product_id,
                'product_qty' => $_product_qty,
            );
            $product_list[$cart_item_key] = $check_additional;
        }
        foreach($product_list as $cart_item_key => $check_additional) {
            foreach($cart_suggestion_options as $cart_suggestion_id => $cart_suggestion_option) {
                $check_additional['settings_cart_suggestion'] = $cart_suggestion_option;
                if( BeRocket_conditions_cart_suggestion::check($cart_suggestion_option['condition'], 'berocket_cart_suggestion_custom_post', $check_additional)
                && ! empty($cart_suggestion_option['products']) && is_array($cart_suggestion_option['products']) && count($cart_suggestion_option['products']) ) {
                    $cart_suggestion_quantity[$cart_suggestion_id]['quantity'] += $check_additional['product_qty'];
                    $cart_suggestion_quantity[$cart_suggestion_id]['products'][$cart_item_key] = $check_additional;
                }
            }
        }
        $linked_products = apply_filters('berocket_cart_suggestion_check_linked_products', $linked_products);
        
        $return = apply_filters('berocket_cart_suggestion_check_result_other', array(), array(
            'cart_suggestion_options'    => $cart_suggestion_options,
            'product_list'               => $product_list,
            'cart_suggestion_quantity'   => $cart_suggestion_quantity,
            'linked_products'            => $linked_products,
            'count'                      => $count,
            'cart_post_exist'            => $cart_post_exist
        ));
        $return = array_unique($return);
        $return = array_slice($return, 0, $count);
        return $return;
    }
    function condition_mode_normal($force_products_list, $cart_suggestion_id, $cart_suggestion_option, $product_list, $cart_suggestion_quantity) {
        foreach($cart_suggestion_option['products'] as $product_id) {
            $product_data = array(
                'product_id'    => $product_id,
                'quantity'      => $this->calculate_quantity($cart_suggestion_quantity['quantity'], $cart_suggestion_option),
                'data'          => $cart_suggestion_option
            );
            $product_data['data']['cart_suggestion_id'] = $cart_suggestion_id;
            $product_data['data']['cart_suggestion'] = true;
            $variation = '';
            if ( 'product_variation' === get_post_type( $product_id ) ) {
                $variation = $product_id;
                $product_id   = wp_get_post_parent_id( $product_id );
            }
            $force_products_list[] = $product_data;
        }
        return $force_products_list;
    }
    function condition_mode_each($force_products_list, $cart_suggestion_id, $cart_suggestion_option, $product_list, $cart_suggestion_quantity) {
        foreach($cart_suggestion_option['products'] as $product_id) {
            $product_data = array(
                'product_id'    => $product_id,
                'quantity'      => $cart_suggestion_quantity['quantity'],
                'data'          => $cart_suggestion_option
            );
            $product_data['data']['cart_suggestion_id'] = $cart_suggestion_id;
            $product_data['data']['cart_suggestion'] = true;
            $variation = '';
            if ( 'product_variation' === get_post_type( $product_id ) ) {
                $variation = $product_id;
                $product_id   = wp_get_post_parent_id( $product_id );
            }
            foreach($cart_suggestion_quantity['products'] as $cart_item_key => $product_cart) {
                $each_product_data = $product_data;
                $each_product_data['quantity'] = $this->calculate_quantity($product_cart['product_qty'], $cart_suggestion_option);
                $force_products_list[] = $each_product_data;
            }
        }
        return $force_products_list;
    }
    function check_result_other($result, $data) {
        extract($data);
        $products_suggest = array();
        $settings = $this->get_option();
        foreach($cart_suggestion_options as $cart_suggestion_id => $cart_suggestion_option) {
            if ( ! isset($cart_suggestion_quantity[$cart_suggestion_id]) 
              || ! isset($cart_suggestion_quantity[$cart_suggestion_id]['products']) 
              || ! is_array($cart_suggestion_quantity[$cart_suggestion_id]['products'])
              || ! count($cart_suggestion_quantity[$cart_suggestion_id]['products'])
            ) continue;
            if( ! empty($cart_suggestion_option['products_count']) && intval($cart_suggestion_option['products_count']) && is_array($cart_suggestion_option['products'])
            && count($cart_suggestion_option['products']) && intval($cart_suggestion_option['products_count']) < count($cart_suggestion_option['products']) ) {
                $rands_i = array_rand($cart_suggestion_option['products'], intval($cart_suggestion_option['products_count']));
                if( ! is_array($rands_i) ) {
                    $rands_i = array($rands_i);
                }
                $new_products = array();
                foreach($rands_i as $rand_i) {
                    $new_products[] = $cart_suggestion_option['products'][$rand_i];
                }
                $cart_suggestion_option['products'] = $new_products;
            }
            $check_additional = array(
                'cart' => $cart_suggestion_quantity[$cart_suggestion_id]['products'],
                'settings_cart_suggestion' => $cart_suggestion_option
            );
            if( BeRocket_conditions_cart_suggestion::check($cart_suggestion_option['condition'], 'berocket_cart_suggestion_custom_post', $check_additional) 
                && apply_filters('berocket_cart_suggestion_check_cart_data', true, $cart_suggestion_option, $data) ) {
                $condition_mode = br_get_value_from_array($cart_suggestion_option, 'condition_mode');
                if( empty($condition_mode) ) $condition_mode = 'each';
                $force_products_list = apply_filters('berocket_cart_suggestion_condition_mode_'.$condition_mode, array(), $cart_suggestion_id, $cart_suggestion_option, $product_list, $cart_suggestion_quantity[$cart_suggestion_id]);
                foreach($force_products_list as $force_product) {
                    if( ! in_array($force_product['product_id'], $cart_post_exist) ) {
                        $wc_product = wc_get_product($force_product['product_id']);
                        if( $wc_product ) {
                            if( $wc_product->get_type() == 'variation' && ! empty($settings['remove_variation']) ) {
                                $wc_product_id = $wc_product->get_parent_id();
                                if( ! in_array($wc_product_id, $cart_post_exist) ) {
                                    $products_suggest[] = $force_product['product_id'];
                                }
                            } else {
                                $products_suggest[] = $force_product['product_id'];
                            }
                        }
                    }
                }
            }
            if( count($products_suggest) > $count ) {
                $products_suggest = array_diff($products_suggest, $cart_post_exist);
                if( count($products_suggest) > $count ) {
                    $products_suggest = array_values($products_suggest);
                    break;
                }
            }
        }
        return $products_suggest;
    }
    function calculate_quantity($qty, $options) {
        $quantity = apply_filters('berocket_calculate_quantity_cart_suggestion', $qty, $options, $qty);
        return $quantity;
    }
    public function condition_additional($echo, $post) {
        $BeRocket_cart_suggestion_custom_post = BeRocket_cart_suggestion_custom_post::getInstance();
        $options = $BeRocket_cart_suggestion_custom_post->get_option( $post->ID );
        $condition_mode = br_get_value_from_array($options, 'condition_mode');
        $echo['open_settings']          = '<h3>'.__('Condition Additional settings', 'cart-products-suggestions-for-woocommerce').'</h3><table>';
        $echo['condition_mode_open']    = '<tr><th>'.__('Condition Mode', 'cart-products-suggestions-for-woocommerce').'</th><td>';

        $echo['condition_mode_cart1']   = '<p><label>';
        $echo['condition_mode_cart2']   = '<input name="br_suggestion[condition_mode]" type="radio" value="cart"'.($condition_mode == 'cart' ? ' checked' : '').'>';
        $echo['condition_mode_cart3']   = __('Cart', 'cart-products-suggestions-for-woocommerce').'</label>
        <small>'.__('Condition will check all products in cart', 'cart-products-suggestions-for-woocommerce').'</small></p>';

        $echo['condition_mode_each1']   = '<p><label>';
        $echo['condition_mode_each2']   = '<input name="br_suggestion[condition_mode]" type="radio" value="each"'.(empty($condition_mode) || $condition_mode == 'each' ? ' checked' : '').'>';
        $echo['condition_mode_each3']   = __('Each product', 'cart-products-suggestions-for-woocommerce').'</label>
        <small>'.__('Condition will check each product', 'cart-products-suggestions-for-woocommerce').'</small></p>';

        $echo['condition_mode_close']   = '</td></tr>';
        $echo['close_settings']         = '</table>';
        return $echo;
    }
    public function get_option() {
        $BeRocket_cart_suggestion = BeRocket_cart_suggestion::getInstance();
        return $BeRocket_cart_suggestion->get_option();
    }
    public function check_global_settings($result, $cart_suggestion_option) {
        $cart = WC()->cart;
        if( ! empty($cart_suggestion_option['price_minimum']) ) {
            $cart_price = $cart->get_subtotal();
            if( floatval($cart_suggestion_option['price_minimum']) > $cart_price ) {
                return false;
            }
        }
        if( ! empty($cart_suggestion_option['price_maximum']) ) {
            $cart_price = $cart->get_subtotal();
            if( floatval($cart_suggestion_option['price_maximum']) < $cart_price ) {
                return false;
            }
        }
        return $result;
    }
}
new berocket_cart_suggestion_check_cond();

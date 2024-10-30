<?php 
$dplugin_name = 'WooCommerce Cart Suggestions';
$dplugin_link = 'http://berocket.com/product/woocommerce-cart-suggestions';
$dplugin_price = 20;
$dplugin_desc = '';
@ include 'settings_head.php';
?>
<div class="wrap br_settings br_cart_suggestion_settings">
    <?php settings_errors(); ?>

    <h2 class="nav-tab-wrapper">
        <a href="#general" class="nav-tab nav-tab-active general-tab" data-block="general"><?php _e('General', 'cart-products-suggestions-for-woocommerce') ?></a>
        <a href="#css" class="nav-tab css-tab" data-block="css"><?php _e('CSS/JavaScript', 'cart-products-suggestions-for-woocommerce') ?></a>
        <a href="#license" class="nav-tab license-tab" data-block="license"><?php _e('License', 'cart-products-suggestions-for-woocommerce') ?></a>
    </h2>

    <form class="cart_suggestion_submit_form" method="post" action="options.php">
        <?php 
        $BeRocket_cart_suggestion = BeRocket_cart_suggestion::getInstance();
        $options = $BeRocket_cart_suggestion->get_option(); ?>
        <div class="nav-block general-block nav-block-active">
            <table class="form-table license">
                <tr>
                    <th scope="row"><?php _e('Custom CSS Class', 'cart-products-suggestions-for-woocommerce') ?></th>
                    <td>
                        <input type="text" name="br-cart_suggestion-options[custom_css_class]" value="<?php echo $options['custom_css_class']; ?>">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Display position', 'cart-products-suggestions-for-woocommerce') ?></th>
                    <td>
                        <div><label><input type="checkbox" name="br-cart_suggestion-options[display_before_cart_table]" value="1"<?php if($options['display_before_cart_table']) echo ' checked'; ?>><?php _e('Before cart table', 'cart-products-suggestions-for-woocommerce') ?></label></div>
                        <div><label><input type="checkbox" name="br-cart_suggestion-options[display_after_cart_table]" value="1"<?php if($options['display_after_cart_table']) echo ' checked'; ?>><?php _e('After cart table', 'cart-products-suggestions-for-woocommerce') ?></label></div>
                        <div><label><input type="checkbox" name="br-cart_suggestion-options[display_after_cart_total]" value="1"<?php if($options['display_after_cart_total']) echo ' checked'; ?>><?php _e('After cart total', 'cart-products-suggestions-for-woocommerce') ?></label></div>
                        <?php
                        $hooks = array(
                            'woocommerce_before_cart' => __('Before cart table 2', 'cart-products-suggestions-for-woocommerce'),
                            'before_the_content' => __('Before cart page content', 'cart-products-suggestions-for-woocommerce'),
                            'after_the_content'  => __('After cart page content', 'cart-products-suggestions-for-woocommerce'),
                        );
                        foreach($hooks as $hook => $hook_name) {
                            echo '<div><label><input type="checkbox" name="br-cart_suggestion-options[display_hooks][]" value="'.$hook.'"' . ( isset($options['display_hooks']) && is_array($options['display_hooks']) && in_array($hook, $options['display_hooks']) ? ' checked' : '') . '>' . $hook_name . '</label></div>';
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Style', 'cart-products-suggestions-for-woocommerce') ?></th>
                    <td>
                        <div class="brcs_widget_setting">
                            <select class="brcs_wid_type" name="br-cart_suggestion-options[widget_style]">
                            <?php
                                $types = array('default' => 'Default', 'image' => 'Image', 'image_title' => 'Image with Title', 'image_title_price' => 'Image with Title and Price', 'title' => 'Title', 'title_price' => 'Title with Price', 'slider' => 'Slider', 'slider_title' => 'Slider with title');
                                foreach( $types as $t_val => $t_name ) {
                                    echo '<option value="', $t_val, '"', ($t_val == $options['widget_style'] ? ' selected' : ''), '>', $t_name, '</option>';
                                }
                            ?>
                            </select>
                            <p>
                                <label><?php _e('Products per line:', 'cart-products-suggestions-for-woocommerce'); ?></label>
                                <select name="br-cart_suggestion-options[slide_count]">
                                    <?php
                                    for($i = 0; $i < 10; $i++) {
                                        echo '<option value="', $i, '"', ($options['slide_count'] == $i ? ' selected' : ''), '>', ($i + 1), '</option>';
                                    }
                                    ?>
                                </select>
                            </p>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Suggestions Title', 'cart-products-suggestions-for-woocommerce') ?></th>
                    <td>
                        <input class="regular-text" type="text" name="br-cart_suggestion-options[suggestions_title]" value="<?php echo $options['suggestions_title']; ?>">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Max Suggestions Count', 'cart-products-suggestions-for-woocommerce') ?></th>
                    <td>
                        <input type="number" name="br-cart_suggestion-options[max_suggestions_count]" value="<?php echo $options['max_suggestions_count']; ?>">
                    </td>
                </tr>
                <?php
                if( defined('ICL_LANGUAGE_CODE') ) {
                    echo '<tr><th colspan=2><p class="notice notice-error">', __('Please use products and categories on default language', 'cart-products-suggestions-for-woocommerce'), '</p></th></tr>';
                }
                ?>
                <tr>
                    <th scope="row"><?php _e('Default Suggestions', 'cart-products-suggestions-for-woocommerce') ?></th>
                    <td>
                        <?php br_generate_product_selector( array( 'option' => $options['default_suggest'], 'block_name' => 'default_suggest', 'name' => 'br-cart_suggestion-options[default_suggest][]' ) ); ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Categories', 'cart-products-suggestions-for-woocommerce') ?></th>
                    <td>
                        <div class="br_add_suggestion_to_specific br_add_suggestion_to_specific_category">
                            <table class="wp-list-table plugins">
                                <thead>
                                    <tr><th colspan="2">Category</th><td colspan="2">Products Suggestions</td></tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    if( isset($options['category_suggest']) && is_array($options['category_suggest']) ) {
                                        foreach($options['category_suggest'] as $category) {
                                            $cat = get_term( $category['category'], 'product_cat' );
                                            if(isset($cat)) {
                                                $html = '<tr class="cat_exist_id cat_exist_'.$category['category'].'"><td class="move_suggestions"><i class="fa fa-th"></i></td><td><input class="cat_suggest_position" type="hidden" value="" name="br-cart_suggestion-options[category_suggest]['.$category['category'].'][position]"><input type="hidden" value="'.$category['category'].'" name="br-cart_suggestion-options[category_suggest]['.$category['category'].'][category]">'.$cat->name.'</td><td>';
                                                $html .= br_generate_product_selector( array( 'return' => true, 'option' => (isset($category['products']) ? $category['products'] : array()), 'block_name' => 'category_suggest', 'name' => 'br-cart_suggestion-options[category_suggest]['.$category['category'].'][products][]' ) );
                                                $html .= '</td><td class="cat_suggest_remove"><button type="button" class="cat_suggest_remove_button">Remove</button></td></tr>';
                                                echo $html;
                                            }
                                        }
                                    }
                                    ?>
                                </tbody>
                                <tfoot>
                                    <tr><td colspan="4">Drag and drop to sort</td></tr>
                                </tfoot>
                            </table>
                            <?php
                            $product_categories = get_terms( 'product_cat' );
                            if( isset($product_categories) && is_array($product_categories) && count($product_categories) > 0 ) {
                            ?>
                            <select class="category_suggest">
                            <?php
                            foreach($product_categories as $category) {
                                echo '<option value="', $category->term_id, '">', $category->name, '</option>';
                            }
                            ?>
                            </select>
                            <span class="button add_category_suggest">Add Category</span>
                            <?php 
                            } else {
                                _e('There are no Categories. Please add one', 'cart-products-suggestions-for-woocommerce');
                                echo ' <a href="'.admin_url( 'edit-tags.php?taxonomy=product_cat&post_type=product' ).'">'.__('here', 'cart-products-suggestions-for-woocommerce').'</a>';
                            }
                            $category_product_search = br_generate_product_selector( array( 'return' => true, 'block_name' => 'category_suggest', 'name' => 'br-cart_suggestion-options[category_suggest][%cat_id%][products][]' ) ); ?>
                            <script>
                                var category_product_search = <?php echo json_encode($category_product_search); ?>;
                            </script>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Products', 'cart-products-suggestions-for-woocommerce') ?></th>
                    <td>
                        <div class="br_add_suggestion_to_specific br_add_suggestion_to_specific_product">
                            <table class="wp-list-table plugins">
                                <thead>
                                    <tr><td colspan="2">Products</td><td colspan="2">Products Suggestions</td></tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    if( isset($options['product_suggest']) && is_array($options['product_suggest']) ) {
                                        foreach($options['product_suggest'] as $category) {
                                            if( isset($category['product_ids']) && is_array($category['product_ids']) ) {
                                                $html = '<tr class="cat_exist_id"><td class="move_suggestions"><i class="fa fa-th"></i></td><td><input class="product_suggest_position" type="hidden" value="" data-name="br-cart_suggestion-options[product_suggest][%position%][position]" name="br-cart_suggestion-options[product_suggest][%position%][position]">';
                                                $html .= br_generate_product_selector( array( 'return' => true, 'option' => $category['product_ids'], 'block_name' => 'product_suggest', 'name' => 'br-cart_suggestion-options[product_suggest][%position%][product_ids][]' ) );
                                                $html .= '</td><td>';
                                                $html .= br_generate_product_selector( array( 'return' => true, 'option' => (isset($category['products']) ? $category['products'] : array()), 'block_name' => 'product_suggest_2', 'name' => 'br-cart_suggestion-options[product_suggest][%position%][products][]' ) );
                                                $html .= '</td><td class="cat_suggest_remove"><button type="button" class="cat_suggest_remove_button">Remove</button></td></tr>';
                                                echo $html;
                                            }
                                        }
                                    }
                                    ?>
                                </tbody>
                                <tfoot>
                                    <tr><td colspan="4">Drag and drop to sort</td></tr>
                                </tfoot>
                            </table>
                            <script>
                                
                            var reload_sortable = function() {
                                jQuery('.br_add_suggestion_to_specific table tr td').css('width', '');
                                jQuery('.br_add_suggestion_to_specific table tr td').each(function( i, o ) {
                                    jQuery(o).css('width', jQuery(o).css('width'));
                                });
                            }
                            jQuery(function() {
                                jQuery( ".br_add_suggestion_to_specific table tbody" ).sortable({
                                    axis: "y",
                                    helper: "clone",
                                    opacity: 0.7,
                                    start: function( event, ui ) {
                                        ui.placeholder.css('height', ui.helper.height());
                                    },
                                    handle: '.move_suggestions'
                                });
                                jQuery(window).resize(function(event) {
                                    reload_sortable();
                                });
                                reload_sortable();
                            });
                            </script>
                            <span class="button add_product_suggest">Add Product</span>
                            <?php $product_product_search = br_generate_product_selector( array( 'return' => true, 'block_name' => 'product_suggest', 'name' => 'br-cart_suggestion-options[product_suggest][%position%][product_ids][]' ) );
                            $product_product_search_2 = br_generate_product_selector( array( 'return' => true, 'block_name' => 'product_suggest_2', 'name' => 'br-cart_suggestion-options[product_suggest][%position%][products][]' ) ); ?>
                            <script>
                                var product_product_search = <?php echo json_encode($product_product_search); ?>;
                                var product_product_search_2 = <?php echo json_encode($product_product_search_2); ?>;
                            </script>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th><?php _e('Shortcode', 'cart-products-suggestions-for-woocommerce') ?></th>
                    <td>
                        <div>You can use shortcode for display products</div>
                        <pre><code>[br_cart_suggestion title="" type="" count="" add_to_cart="0"]</code></pre>
                        <ul>
                            <li><strong>title</strong> - any text that you whant for title</li>
                            <li><strong>type</strong> - types of widget:
                            <ul style="padding: 0.5em 0 1em 1em;">
                                <li><em>default</em> - like products on your shop</li>
                                <li><em>image</em> - only products image</li>
                                <li><em>image_title</em> - products image and title</li>
                                <li><em>image_title_price</em> - products image, title and price</li>
                                <li><em>title</em> - only products title</li>
                                <li><em>title_price</em> - products title and price</li>
                            </ul></li>
                            <li><strong>count</strong> - count of products to display</li>
                            <li><strong>add_to_cart</strong> - display add to cart button after products</li>
                        </ul>
                    </td>
                </tr>
            </table>
        </div>
        <div class="nav-block css-block">
            <table class="form-table license">
                <tr>
                    <th scope="row"><?php _e('Custom CSS', 'cart-products-suggestions-for-woocommerce') ?></th>
                    <td>
                        <textarea name="br-cart_suggestion-options[custom_css]"><?php echo $options['custom_css']?></textarea>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('JavaScript on page load', 'cart-products-suggestions-for-woocommerce') ?></th>
                    <td>
                        <textarea name="br-cart_suggestion-options[script][js_page_load]"><?php echo (empty($options['script']['js_page_load']) ? '' : $options['script']['js_page_load']); ?></textarea>
                    </td>
                </tr>
            </table>
        </div>
        <div class="nav-block license-block">
            <table class="form-table license">
                <tr>
                    <th scope="row"><?php _e('Plugin key', 'cart-products-suggestions-for-woocommerce') ?></th>
                    <td>
                        <input id="berocket_product_key" size="50" name="br-cart_suggestion-options[plugin_key]" type='text' value='<?php echo $options['plugin_key']?>'/>
                        <br />
                        <span style="color:#666666;margin-left:2px;"><?php _e('Key for plugin from BeRocket.com', 'cart-products-suggestions-for-woocommerce') ?></span>
                        <br />
                        <input class="berocket_test_account_product button-secondary" data-id="11" type="button" value="Test">
                        <div class="berocket_test_result"></div>
                    </td>
                </tr>
            </table>
        </div>
        <input type="submit" class="button-primary" value="<?php _e('Save Changes', 'cart-products-suggestions-for-woocommerce') ?>" />
    </form>
</div>
<?php
$feature_list = array();
@ include 'settings_footer.php';
?>

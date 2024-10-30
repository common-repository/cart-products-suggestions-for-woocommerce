<?php 
extract($berocket_cart_suggestion_widget);
$BeRocket_cart_suggestion = BeRocket_cart_suggestion::getInstance();
$products = apply_filters('berocket_cart_suggestion_get_products', array(), $count);
$additional = array('slider_count' => $slider_count);
ob_start();
$BeRocket_cart_suggestion->print_products($products, $type, $add_to_cart, $additional);
$suggested = ob_get_clean();
if( $suggested ) {
    if( $title ) echo $args['before_title'].$title.$args['after_title']; 
    echo $suggested;
}
?>

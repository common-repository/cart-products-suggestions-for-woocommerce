<?php
class BeRocket_cart_suggestion_Paid extends BeRocket_plugin_variations {
    public $plugin_name = 'cart_suggestion';
    public $version_number = 15;
    public function __construct() {
        $this->info = array(
            'id'          => 11,
            'lic_id'      => 71,
            'version'     => BeRocket_cart_suggestion_version,
            'plugin_name' => 'cart_suggestion',
            'domain'      => 'cart-products-suggestions-for-woocommerce',
            'templates'   => cart_suggestion_TEMPLATE_PATH,
        );
        $this->values = array(
            'settings_name' => 'br-cart_suggestion-options',
            'option_page'   => 'br-cart_suggestion',
            'premium_slug'  => 'woocommerce-cart-suggestions',
            'free_slug'     => 'cart-products-suggestions-for-woocommerce',
        );
        $this->defaults = array();
        parent::__construct();
    }
}
new BeRocket_cart_suggestion_Paid();
?>

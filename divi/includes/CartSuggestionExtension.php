<?php

class BACN_CartSuggestion_DiviExtension extends DiviExtension {
	public $gettext_domain = 'brcs-cart-suggestion';
	public $name = 'brcs-cart-suggestion';
	public $version = '1.0.0';
    public $props = array();
	public function __construct( $name = 'brcs-cart-suggestion', $args = array() ) {
		$this->plugin_dir     = plugin_dir_path( __FILE__ );
		$this->plugin_dir_url = plugin_dir_url( $this->plugin_dir );

		parent::__construct( $name, $args );
        add_action('wp_ajax_brcs_cart_suggestion', array($this, 'cart_suggestion'));
	}
    public function cart_suggestion() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die();
        }
        $atts = berocket_sanitize_array($_POST);
        $atts = self::convert_on_off($atts);
        echo do_shortcode('[br_cart_suggestion add_to_cart="'.(empty($atts['add_to_cart']) ? '0' : '1').'" type="'.(empty($atts['type']) ? 'default' : $atts['type']).'" count="'.(empty($atts['count']) ? '5' : intval($atts['count']) + 1).'" slider_count="'.(empty($atts['slider_count']) ? '0' : intval($atts['slider_count'])).'"]');
        wp_die();
    }
	public function wp_hook_enqueue_scripts() {
		if ( $this->_debug ) {
			$this->_enqueue_debug_bundles();
		} else {
			$this->_enqueue_bundles();
		}

		if ( et_core_is_fb_enabled() && ! et_builder_bfb_enabled() ) {
			$this->_enqueue_backend_styles();
		}

		// Normalize the extension name to get actual script name. For example from 'divi-custom-modules' to `DiviCustomModules`.
		$extension_name = str_replace( ' ', '', ucwords( str_replace( '-', ' ', $this->name ) ) );

		// Enqueue frontend bundle's data.
		if ( ! empty( $this->_frontend_js_data ) ) {
			wp_localize_script( "{$this->name}-frontend-bundle", "{$extension_name}FrontendData", $this->_frontend_js_data );
		}

		// Enqueue builder bundle's data.
		if ( et_core_is_fb_enabled() && ! empty( $this->_builder_js_data ) ) {
			wp_localize_script( "{$this->name}-builder-bundle", "{$extension_name}BuilderData", $this->_builder_js_data );
		}
	} 
    public static function convert_on_off($atts) {
        foreach($atts as &$attr) {
            if( $attr === 'on' || $attr === 'off' ) {
                $attr = ( $attr === 'on' ? TRUE : FALSE );
            }
        }
        return $atts;
    }
}

new BACN_CartSuggestion_DiviExtension;

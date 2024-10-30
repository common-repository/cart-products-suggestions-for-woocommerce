<?php

class ET_Builder_Module_br_cart_suggestion extends ET_Builder_Module {

	public $slug       = 'et_pb_br_cart_suggestion';
	public $vb_support = 'on';

	protected $module_credits = array(
		'module_uri' => '',
		'author'     => '',
		'author_uri' => '',
	);

	public function init() {
        $this->name             = __('Cart Suggestion', 'cart-products-suggestions-for-woocommerce' );
		$this->folder_name = 'et_pb_berocket_modules';
		$this->main_css_element = '%%order_class%%';
        
        $this->fields_defaults = array(
            'count' => array('4'),
            'type' => array('default'),
            'add_to_cart' => array('on'),
            'slider_count' => array('4'),
        );

		$this->advanced_fields = array(
			'fonts'           => array(
				'title'   => array(
					'label'        => esc_html__( 'Product Title', 'cart-products-suggestions-for-woocommerce' ),
					'css'          => array(
						'main'      => "{$this->main_css_element} .brcs_products .brcs_product h1, {$this->main_css_element} .brcs_products .brcs_product h2, {$this->main_css_element} .brcs_products .brcs_product h3, {$this->main_css_element} .brcs_products .brcs_product h4, {$this->main_css_element} .brcs_products .brcs_product h5",
						'important' => true,
					),
                    'hide_font_size' => true,
                    'hide_letter_spacing' => true,
                    'hide_line_height' => true,
                    'hide_text_shadow' => true,
				),
				'price'   => array(
					'label'        => esc_html__( 'Product Price', 'cart-products-suggestions-for-woocommerce' ),
					'css'          => array(
						'main'      => "{$this->main_css_element} .brcs_products .brcs_product .price",
						'important' => true,
					),
                    'hide_font_size' => true,
                    'hide_letter_spacing' => true,
                    'hide_line_height' => true,
                    'hide_text_shadow' => true,
				),
			),
			'link_options'  => false,
			'visibility'    => false,
			'text'          => false,
			'transform'     => false,
			'animation'     => false,
			'background'    => false,
			'borders'       => false,
			'box_shadow'    => false,
			'button'        => false,
			'filters'       => false,
			'margin_padding'=> false,
			'max_width'     => false,
		);
	}

    function get_fields() {
        $fields = array(
            'count' => array(
                'label'             => esc_html__( 'Products count', 'cart-products-suggestions-for-woocommerce' ),
                'type'              => 'number'
            ),
            'type' => array(
                "label"           => esc_html__( 'Widget type', 'cart-products-suggestions-for-woocommerce' ),
                'type'            => 'select',
                'options'         => array(
                    'default' => esc_html__( 'Default', 'cart-products-suggestions-for-woocommerce' ),
                    'image' => esc_html__( 'Image', 'cart-products-suggestions-for-woocommerce' ),
                    'image_title' => esc_html__( 'Image with Title', 'cart-products-suggestions-for-woocommerce' ),
                    'image_title_price' => esc_html__( 'Image with Title and Price', 'cart-products-suggestions-for-woocommerce' ),
                    'title' => esc_html__( 'Title', 'cart-products-suggestions-for-woocommerce' ),
                    'title_price' => esc_html__( 'Title with Price', 'cart-products-suggestions-for-woocommerce' ),
                    'slider' => esc_html__( 'Slider', 'cart-products-suggestions-for-woocommerce' ),
                    'slider_title' => esc_html__( 'Slider with title', 'cart-products-suggestions-for-woocommerce' ),
                    'image' => esc_html__( 'Image', 'cart-products-suggestions-for-woocommerce' ),
                    'image' => esc_html__( 'Image', 'cart-products-suggestions-for-woocommerce' ),
                    'image' => esc_html__( 'Image', 'cart-products-suggestions-for-woocommerce' ),
                    'slider' => esc_html__( 'Slider', 'cart-products-suggestions-for-woocommerce' ),
                )
            ),
            'add_to_cart' => array(
                "label"             => esc_html__( 'Add to cart button', 'cart-products-suggestions-for-woocommerce' ),
                'type'              => 'yes_no_button',
                'options'           => array(
                    'off' => esc_html__( "No", 'et_builder' ),
                    'on'  => esc_html__( 'Yes', 'et_builder' ),
                ),
            ),
            'slider_count' => array(
                'label'             => esc_html__( 'Products per line', 'cart-products-suggestions-for-woocommerce' ),
                'type'              => 'number'
            ),
        );

        return $fields;
    }

    function render( $atts, $content = null, $function_name = '' ) {
        $atts = BACN_CartSuggestion_DiviExtension::convert_on_off($atts);
        return do_shortcode('[br_cart_suggestion add_to_cart="'.(empty($atts['add_to_cart']) ? '0' : '1').'" type="'.(empty($atts['type']) ? 'default' : $atts['type']).'" count="'.(empty($atts['count']) ? '5' : intval($atts['count']) + 1).'" slider_count="'.(empty($atts['slider_count']) ? '0' : intval($atts['slider_count'])).'"]');
    }
}

new ET_Builder_Module_br_cart_suggestion;

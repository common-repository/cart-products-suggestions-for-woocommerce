var brcs_generate_slider;
(function ($){
    $(document).ready( function () {
        brcs_generate_slider = function() {
            $('.brcs_slider_suggestion:not(.brcs_unslided)').unslider({
            arrows: {
                prev: '<a class="unslider-arrow prev"></a>',
                next: '<a class="unslider-arrow next"></a>',
            },
            autoplay: true}).addClass('brcs_unslided');
        }
        brcs_generate_slider();
        jQuery( document ).on( "ajaxComplete", brcs_generate_slider);
        document.addEventListener("br_update_cart_suggestion", brcs_generate_slider);
        $('body').on('added_to_cart',function(){
            if( $('.br_cart_suggestions_cart').length > 0 ) {
                $.get(location.href, function(data) {
                    if( $(data).find('.br_cart_suggestions_cart').length > 0 ) {
                        $('.br_cart_suggestions_cart').html($(data).find('.br_cart_suggestions_cart').html());
                    } else {
                        $('.br_cart_suggestions_cart').html('');
                    }
                });
            }
        });
    });
})(jQuery);

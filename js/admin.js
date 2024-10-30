(function ($){
    $(document).ready( function () {
        $(document).on('click', '.berocket_post_set_new_order, .berocket_post_set_new_order_set', function(event) {
            event.preventDefault();
            var $this = $(this);
            var post_id = $(this).data('post_id');
            if( $this.is('.berocket_post_set_new_order_set') ) {
                var order = $this.parents('.berocket_post_set_new_order_input').first().find('input').val();
            } else {
                var order = $(this).data('order');
            }
            $.post(location.href, {braction:'suggestion_change_order', suggestion_id:post_id, new_order:order}, function(html) {
                var $html = jQuery(html);
                var $tbody = $html.find('.berocket_post_set_new_order').first().parents('tbody').first();
                $('.berocket_post_set_new_order').first().parents('tbody').first().replaceWith($tbody);
            });
        });
    });
})(jQuery);

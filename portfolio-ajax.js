jQuery(document).ready(function($) {
    function loadPortfolioItems(paged = 1, category = 'all') {
        $.ajax({
            url: portfolio_ajax_params.ajax_url,
            type: 'post',
            data: {
                action: 'load_portfolio_items',
                paged: paged,
                category: category
            },
            success: function(response) {
                $('#portfolio-items').html(response);
                lightbox.init();
            }
        });
    }

    $('#portfolio-tabs li').click(function() {
        var category = $(this).data('category');
        $('#portfolio-tabs li').removeClass('active');
        $(this).addClass('active');
        loadPortfolioItems(1, category);
    });

    $(document).on('click', '.portfolio-page', function(e) {
        e.preventDefault();
        var paged = $(this).data('page');
        var category = $('#portfolio-tabs li.active').data('category');
        loadPortfolioItems(paged, category);
    });

    loadPortfolioItems();
});

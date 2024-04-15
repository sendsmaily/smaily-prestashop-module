const { rssBaseURL } = jsVariables;

$(() => {
    $("#copy-rss-url-button").click(function (e) {
        e.preventDefault();
        var url = $("#rss #rss-feed-url").text();
        navigator.clipboard.writeText(url);
    });

    // Generate RSS product feed URL if options change.
    $(".smaily-rss-options").change(function (event) {
        var rss_url_base = rssBaseURL + '?';
        var url_parameters = {};

        var rss_limit = $('#rss #form_product_limit').val();
        if (rss_limit != "") {
            url_parameters.limit = rss_limit;
        }

        var rss_sort_by = $('#rss #form_sort_by').val();
        if (rss_sort_by != "") {
            url_parameters.sort_by = rss_sort_by;
        }

        var rss_sort_order = $('#rss #form_sort_order').val();
        if (rss_sort_order != "") {
            url_parameters.sort_order = rss_sort_order;
        }

        var rss_category_id = $('#rss #form_product_category_id').val();
        if (rss_category_id != "") {
            url_parameters.category_id = rss_category_id;
        }

        $('#rss-feed-url').html(rss_url_base + $.param(url_parameters));
    });
});

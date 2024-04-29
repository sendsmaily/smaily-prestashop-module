/*
 * 2024 Smaily
 *
 * NOTICE OF LICENSE
 *
 * Smaily for PrestaShop is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * Smaily for PrestaShop is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Smaily for PrestaShop. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author    Smaily <info@smaily.com>
 * @copyright 2024 Smaily
 * @license   GPL3
 */
const { rssBaseURL } = jsVariables;

$(() => {
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

function copyURL(e, selector) {
    e.preventDefault();
    var url = $(selector).text();
    navigator.clipboard.writeText(url);
}

"use strict";
/**
 * 2018 Smaily
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
 * @copyright 2018 Smaily
 * @license   GPL3
 */
$(document).ready(function() {
  // Hide spinner.
  $("#smaily-spinner").hide();
  // Active tab handler
  $("#myTab a").click(function(e) {
    e.preventDefault();
    $(this).tab("show");
  });
  // Handles validation of credentials.
  $("#smaily-validate-credentials").on("click", function() {
    var subdomain = $("#SMAILY_SUBDOMAIN").val();
    var username = $("#SMAILY_USERNAME").val();
    var password = $("#SMAILY_PASSWORD").val();
    // Show spinner.
    $("#smaily-spinner").show();
    $.ajax({
      type: "POST",
      dataType: "json",
      url: controller_url,
      data: {
        ajax: true,
        controller: "AdminSmailyforPrestashopAjax",
        action: "SmailyValidate",
        token: $("#mymodule_wrapper").attr("data-token"),
        subdomain: subdomain,
        username: username,
        password: password
      },
      success: function success(result) {
        // Hide spinner.
        $("#smaily-spinner").hide();
        //Display error messages above form.
        if (result["error"]) {
          displayMessage(result["error"], true);
        }
        //Sucess message.
        if (result["success"] === true) {
          // Display success message.
          displayMessage(smailymessages.credentials_validated);
          // Hide validate section
          $("#smaily-validate-form-group").hide();
          // Check if there are any autoresponders.
          if (result["autoresponders"].length > 0) {
            // Append received autoresponders to Select Autoresponder options.
            $.each(result["autoresponders"], function(index, item) {
              $("#SMAILY_CART_AUTORESPONDER").append(
                $("<option>", {
                  value: JSON.stringify({
                    name: item["title"],
                    id: item["id"]
                  }),
                  text: item["title"]
                })
              );
            });
          } else {
            // When no autoresponders created display message.
            $("#SMAILY_CART_AUTORESPONDER").append(
              $("<option>")
                .val("")
                .text(smailymessages.no_autoresponders)
            );
          }
        }
      },
      error: function error() {
        $("#smaily-spinner").hide();
        displayMessage(smailymessages.no_connection, true);
      }
    });
  });

  // Generate RSS product feed URL if options change.
  $(".smaily-rss-options").change(function(event) {
    var rss_url_base = smaiy_rss_url + '?';
    var parameters = {};

    var rss_limit = $('#SMAILY_RSS_LIMIT').val();
    if (rss_limit != "") {
      parameters.limit = rss_limit;
    }

    var rss_order_by = $('#SMAILY_RSS_ORDER_BY').val();
    if (rss_order_by != "") {
      parameters.order_by = rss_order_by;
    }

    var rss_order_way = $('#SMAILY_RSS_ORDER_WAY').val();
    if (rss_order_way != "") {
      parameters.order_way = rss_order_way;
    }

    var rss_category = $('#SMAILY_RSS_CATEGORY_ID').val();
    if (rss_category != "all_products") {
      parameters.id_category = rss_category;
    }

    $('#smaily-rss-feed-url').html(rss_url_base + $.param(parameters));
  });

  // Load autoresponders when visiting settings page.
  (function() {
    // Check if credentials are set.
    var subdomain = $("#SMAILY_SUBDOMAIN").val();
    var username = $("#SMAILY_USERNAME").val();
    var password = $("#SMAILY_PASSWORD").val();
    // Continue if credentials are set.
    if (subdomain == "" || username == "" || password == "") {
      return;
    }
    // Show spinner
    $("#smaily-spinner").show();
    // Make ajax call to controller.
    $.ajax({
      type: "POST",
      dataType: "json",
      url: controller_url,
      data: {
        ajax: true,
        controller: "AdminSmailyforPrestashopAjax",
        action: "GetAutoresponders",
        token: $("#mymodule_wrapper").attr("data-token")
      },
      success: function success(result) {
        $("#smaily-spinner").hide();
        //Display error messages above form.
        if (result["error"]) {
          displayMessage(result["error"], true);
        }
        if (result["success"] === true) {
          // Check if there are any autoresponders.
          if (result["autoresponders"].length > 0) {
            // Append autoresponder to cart autoresponders list.
            $.each(result["autoresponders"], function(index, item) {
              $("#SMAILY_CART_AUTORESPONDER").append(
                $("<option>", {
                  value: JSON.stringify({
                    name: item["title"],
                    id: item["id"]
                  }),
                  text: item["title"]
                })
              );
            });
          } else {
            // When no autoresponders created display message.
            $("#SMAILY_CART_AUTORESPONDER").append(
              $("<option>")
                .val("")
                .text(smailymessages.no_autoresponders)
            );
          }
        }
      },
      error: function error() {
        $("#smaily-spinner").hide();
        displayMessage(smailymessages.no_connection, true);
      }
    });
  })();

  // Function to display messages in smaily-messages block.
  function displayMessage(message) {
    var error =
      arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;

    var messageBlock = document.createElement("div");

    if (error) {
      messageBlock.classList.add("module_error");
      messageBlock.classList.add("alert");
      messageBlock.classList.add("alert-danger");
    } else {
      messageBlock.classList.add("module_success");
      messageBlock.classList.add("alert");
      messageBlock.classList.add("alert-success");
    }
    // Message text.
    messageBlock.innerHTML = message;
    // Close button.
    var button = document.createElement("button");
    button.classList.add("close");
    button.innerHTML = "x";
    button.onclick = function() {
      $(this)
        .closest("div")
        .hide();
    };

    messageBlock.appendChild(button);
    // Append message to display
    document.querySelector("#smaily-messages").appendChild(messageBlock);
  }
});

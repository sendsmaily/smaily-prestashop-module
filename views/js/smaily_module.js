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
  // Stop user from submitting form with enter key to make api validation mandatory
  $("#smaily_configuration_form").bind("keypress", function(e) {
    if (e.keyCode == 13) {
      return false;
    }
  });
  // Handles validation of autoresponder details and
  // displays second part of settings form based of response from smaily autoresponder API
  $("#smaily-validate-autoresponder").on("click", function() {
    let subdomain = $("#SMAILY_SUBDOMAIN").val();
    let username = $("#SMAILY_USERNAME").val();
    let password = $("#SMAILY_PASSWORD").val();
    $.ajax({
      type: "POST",
      dataType: "json",
      url: "admin-ajax.php",
      data: {
        ajax: true,
        controller: "AdminSmailyforPrestashopAjax",
        action: "SmailyValidate",
        token: $("#mymodule_wrapper").attr("data-token"),
        subdomain: subdomain,
        username: username,
        password: password
      },
      success: function(result) {
        //Display error messages above form.
        if (result["error"]) {
          $errorMessage =
            '<button  type="button" class="close" data-dismiss="alert">×</button>' +
            result["error"];
          $("#smaily_errormessages").html($errorMessage);
          $("#smaily_errormessages").show();
        }
        //Sucess message.
        if (result["success"] === true) {
          // Hide error messages.
          $("#smaily_errormessages").hide();
          // Hide validate section
          $("#smaily-validate-form-group").hide();
          // Append received autoresponders to Select Autoresponder options.
          $.each(result["autoresponders"], (index, item) => {
            $("#SMAILY_AUTORESPONDER").append(
              $("<option>", {
                value: JSON.stringify({ name: item["name"], id: item["id"] }),
                text: item["name"]
              })
            );
          });
          // Show second part of the form.
          $("#smaily_autoresponders").show();
          // Show save button.
          $(".panel-footer").show();
        }
      },
      error: function(error) {
        console.log(error);
      }
    });
  });
  // If autoresponders allready validated call smaily api to populate autoresponders list
  (function() {
    let subdomain = $("#SMAILY_SUBDOMAIN").val();
    let username = $("#SMAILY_USERNAME").val();
    let password = $("#SMAILY_PASSWORD").val();
    if (subdomain !== "" && password !== "" && username !== "") {
      $.ajax({
        type: "POST",
        dataType: "json",
        url: "admin-ajax.php",
        data: {
          ajax: true,
          controller: "AdminSmailyforPrestashopAjax",
          action: "SmailyValidate",
          token: $("#mymodule_wrapper").attr("data-token"),
          subdomain: subdomain,
          username: username,
          password: password
        },
        success: function(result) {
          //Display error messages above form.
          if (result["error"]) {
            $errorMessage =
              '<button  type="button" class="close" data-dismiss="alert">×</button>' +
              result["error"];
            $("#smaily_errormessages").html($errorMessage);
            $("#smaily_errormessages").show();
          }
          if (result["success"] === true) {
            // Hide error messages.
            $("#smaily_errormessages").hide();
            // Append received autoresponders to Select Autoresponder options.
            $.each(result["autoresponders"], (index, item) => {
              $("#SMAILY_AUTORESPONDER").append(
                $("<option>", {
                  value: JSON.stringify({ name: item["name"], id: item["id"] }),
                  text: item["name"]
                })
              );
            });
            // Append autoresponder to cart autoresponders list.
            $.each(result["autoresponders"], (index, item) => {
              $("#SMAILY_CART_AUTORESPONDER").append(
                $("<option>", {
                  value: JSON.stringify({ name: item["name"], id: item["id"] }),
                  text: item["name"]
                })
              );
            });
          }
        },
        error: function(error) {
          $errorMessage =
            '<button  type="button" class="close" data-dismiss="alert">×</button>' +
            "There seems to be some problem with connecting to Smaily!";
          $("#smaily_errormessages").html($errorMessage);
          $("#smaily_errormessages").show();
          console.log(error);
        }
      });
    }
  })();
});

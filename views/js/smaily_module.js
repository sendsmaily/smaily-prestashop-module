/**
 * 2007-2018 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2018 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
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
});

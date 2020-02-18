/**
 * @file
 */

(function ($, Drupal, drupalSettings) {
  'use strict';

  Drupal.behaviors.filefield_sources_assetbank_redirect = {
    attach: function () {
      let callbackUrl = '?callbackurl=' + window.location.protocol + '//' + window.location.host + Drupal.url('select_image');
        // let assetBankURL = $("input[name=assetbank_host]").val() + callbackUrl;
      let assetBankUrl = 'https://dmam.uwaterloo.ca/asset-bank/action/selectImageForCms' + callbackUrl;
      console.log(assetBankUrl);

      $(".assetbank-selection").on("click", function(event){
        window.open(assetBankUrl, "assetbank", 'width=800, height=1000, location=yes,resizable=no,scrollbars=yes,status=yes, toolbar=no, menubar=no');
        window.addEventListener('message', function (ev) {
          console.log('assetbank');
          console.log(ev.data);
          // $("#assetbank_url").attr('value', ev.data);
          // $("input[name=assetbank_url]").attr('value', ev.data);
          // $("input[name=assetbank_url]").val(ev.data);
          $("#assetbank_url").val(ev.data);
          // $(".assetbank_url_textfield").css("display", "block");
          // $(".form-actions").removeAttr("hidden");
        });

        event.preventDefault();
      });
    }
  };
})(jQuery, Drupal, drupalSettings);



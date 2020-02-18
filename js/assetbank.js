/**
 * @file
 */

(function ($, Drupal, drupalSettings) {
  'use strict';

  Drupal.behaviors.filefield_sources_assetbank_redirect = {
    attach: function () {
      let callbackUrl = "?callbackurl=" + Drupal.url.toAbsolute('/select_image');
      let assetBankUrl = drupalSettings.assetbank.host + callbackUrl;

      $(".assetbank-selection").on("click", function(event){
        window.open(assetBankUrl, "assetbank", 'width=800,height=1000,location=yes,resizable=no,scrollbars=yes,status=yes,toolbar=no,menubar=no');
        window.addEventListener('message', function (ev) {
          $("#assetbank_url").val(ev.data);
        });

        event.preventDefault();
      });
    }
  };
})(jQuery, Drupal, drupalSettings);



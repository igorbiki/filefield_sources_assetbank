/**
 * @file
 */
(function ($) {
  $(function () {
    var callbackUrl = "?callbackurl=" + window.location.protocol + '//' + window.location.hostname + Drupal.settings.basePath + 'select_image';
    var assetBankURL = $("input[name=assetbank_openUrl]").val() + callbackUrl;
    var tmpImageSource = Drupal.settings.filefield_sources_assetbank.tmpImageSource;
    $(".field-type-image").on("click", ".assetbankSelection", function(event) {
      if (!tmpImageSource) {
        alert('Unknown Temporary Image Server.  You must set this in your configuration.');
      } else {
        window.open(assetBankURL, "assetbank", 'width=800, height=1000, location=yes,resizable=no,scrollbars=yes,status=yes, toolbar=no, menubar=no');
        window.addEventListener('message', function (ev) {
          if (ev.data.match("^" + tmpImageSource)) {
            $(".assetbank_url_textfield").attr('value', ev.data);
            $('.url-block').removeAttr('hidden');
          }
        });
        event.preventDefault();
      }
    });
  });
}(jQuery));

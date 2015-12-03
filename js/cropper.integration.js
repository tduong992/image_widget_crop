/**
 * @file cropper.integration.js
 *
 * Defines the behaviors needed for cropper integration.
 *
 */
(function ($, Drupal, drupalSettings) {
  "use strict";

  Drupal.behaviors.cropperIntegraion = {
    attach: function (context) {
      var cropperSelector = ".image-style-crop-thumbnail";
      var verticalTabs = $(".crop-wrapper .vertical-tabs__menu-item");

      verticalTabs.click(function () {
        var tabId = $(this).find("a").attr("href");
        var cropper = $(tabId).find(cropperSelector);
        cropper.each(function () {
          $(this).cropper({
            aspectRatio: eval($(this).data("ratio")),
            background: false,
            zoomable: false,
            viewMode: 3,
            crop: function (e) {
              //@TODO: set the values dor the input elements.
              /*console.log(e.x);
               console.log(e.y);
               console.log(e.width);
               console.log(e.height);
               console.log(e.rotate);
               console.log(e.scaleX);
               console.log(e.scaleY);*/
            }
          });
        });
      });
    }
  };


}(jQuery, Drupal, drupalSettings));

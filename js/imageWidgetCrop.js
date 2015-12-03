/**
 * @file cropper.integration.js
 *
 * Defines the behaviors needed for cropper integration.
 *
 */
(function ($, Drupal, drupalSettings) {
  "use strict";

  Drupal.imageWidgetCrop = {};

  /**
   * Initialize the cropper
   */
  Drupal.imageWidgetCrop.initialize = function(context) {
    var cropperSelector = ".image-style-crop-thumbnail";
    var verticalTabs = $(".vertical-tabs", context);
    var verticalTabsMenuItem = verticalTabs.find(".vertical-tabs__menu-item");

    var visibleCroppers = verticalTabs.find('.vertical-tabs__pane:first-child').find(cropperSelector);
    visibleCroppers.each(function(){
      Drupal.imageWidgetCrop.initializeCropper(this, $(this).data('ratio'));
    });

    verticalTabsMenuItem.click(function () {
      var tabId = $(this).find("a").attr("href");
      var cropper = $(tabId).find(cropperSelector);
      Drupal.imageWidgetCrop.initializeCropper(cropper, $(cropper).data('ratio'));
    });
  }

  /**
   * Reacts on "entities selected" event.
   *
   * @param element
   *   Element to initialize cropper on.
   * @param ratio
   *   The ratio of the image
   */
  Drupal.imageWidgetCrop.initializeCropper = function(element, ratio) {
    $(element).cropper({
      aspectRatio: eval(ratio),
      background: false,
      zoomable: false,
      viewMode: 3,
      autoCropArea: 1,
      //autoCrop:false,
      crop: function (e) {
        //@TODO: set the values dor the input elements.
        /*var values = $(element).sibling('.crop-preview-wrapper-value')
        values.find('')
        console.log(e.x);
         console.log(e.y);
         console.log(e.width);
         console.log(e.height);
         console.log(e.rotate);
         console.log(e.scaleX);
         console.log(e.scaleY);*/
      }
    });
  }

  Drupal.behaviors.imageWidgetCrop = {
    attach: function (context) {
      Drupal.imageWidgetCrop.initialize(context);
    }
  }

}(jQuery, Drupal, drupalSettings));

/**
 * @file imageWidgetCrop.js
 *
 * Defines the behaviors needed for cropper integration.
 *
 */
(function ($, Drupal, drupalSettings) {
  'use strict';

  Drupal.imageWidgetCrop = {};

  /**
   * Initialize cropper on the ImageWidgetCrop widget.
   *
   * @param context
   *   Element to initialize cropper on.
   */
  Drupal.imageWidgetCrop.initialize = function (context) {
    var cropperSelector = '.image-style-crop-thumbnail';
    var $verticalTabs = $('.vertical-tabs', context);
    var $verticalTabsMenuItem = $verticalTabs.find('.vertical-tabs__menu-item');

    var visibleCroppers = $verticalTabs.find('.vertical-tabs__pane:first-child').find(cropperSelector);
    visibleCroppers.each(function () {
      Drupal.imageWidgetCrop.initializeCropper($(this), $(this).data('ratio'));
    });

    $verticalTabsMenuItem.click(function () {
      var tabId = $(this).find('a').attr('href');
      var $cropper = $(tabId).find(cropperSelector);
      Drupal.imageWidgetCrop.initializeCropper($cropper, $cropper.data('ratio'));
    });
  };

  /**
   * Initialize cropper on an element.
   *
   * @param $element
   *   Element to initialize cropper on.
   * @param ratio
   *   The ratio of the image
   */
  Drupal.imageWidgetCrop.initializeCropper = function ($element, ratio) {
    var data = null;
    var $values = $element.siblings('.crop-preview-wrapper-value');

    if (parseInt($values.find('.crop-applied').val()) === 1) {
      data = {
        x: parseInt($values.find('.crop-x').val()),
        y: parseInt($values.find('.crop-y').val()),
        width: parseInt($values.find('.crop-width').val()),
        height: parseInt($values.find('.crop-height').val()),
        rotate: 0,
        scaleX: 1,
        scaleY: 1
      };
    }

    $element.cropper({
      // @TODO: This is evil.
      aspectRatio: eval(ratio),
      background: false,
      zoomable: false,
      viewMode: 3,
      autoCropArea: 1,
      data: data,
      cropend: function (e) {
        var data = $(this).cropper('getData');
        $values.find('.crop-x').val(data.x);
        $values.find('.crop-y').val(data.y);
        $values.find('.crop-width').val(data.width);
        $values.find('.crop-height').val(data.height);
        $values.find('.crop-applied').val(1);
      }
    });
  };

  Drupal.behaviors.imageWidgetCrop = {
    attach: function (context) {
      Drupal.imageWidgetCrop.initialize(context);
    }
  };

}(jQuery, Drupal, drupalSettings));

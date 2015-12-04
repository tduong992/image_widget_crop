/**
 * @file imageWidgetCrop.js
 *
 * Defines the behaviors needed for cropper integration.
 *
 */
(function ($, Drupal, drupalSettings) {
  'use strict';

  var cropperSelector = '.image-style-crop-thumbnail';
  var cropperValuesSelector = '.crop-preview-wrapper-value';
  var cropWrapperSelector = '.crop-wrapper';
  var verticalTabsSelector = '.vertical-tabs';
  var verticalTabsMenuItemSelector = '.vertical-tabs__menu-item';
  var resetSelector = '.crop-reset';

  Drupal.imageWidgetCrop = {};

  /**
   * Initialize cropper on the ImageWidgetCrop widget.
   *
   * @param context
   *   Element to initialize cropper on.
   */
  Drupal.imageWidgetCrop.initialize = function (context) {
    var $cropWrapper = $(cropWrapperSelector, context);
    var $verticalTabs = $(verticalTabsSelector, context);
    var $verticalTabsMenuItem = $verticalTabs.find(verticalTabsMenuItemSelector);
    var $reset = $(resetSelector, context);

    // @TODO: This event fires too early. The cropper element is not visible yet. This is why we need the setTimeout() workaround. Additionally it also fires when hiding and on page load
    $cropWrapper.on('toggle', function () {
      var $this = $(this);
      setTimeout(function () {
        Drupal.imageWidgetCrop.initializeCropperOnChildren($this);
      }, 10);
    });

    // @TODO: This event fires too early. The cropper element is not visible yet. This is why we need the setTimeout() workaround.
    $verticalTabsMenuItem.click(function () {
      var tabId = $(this).find('a').attr('href');
      var $cropper = $(tabId).find(cropperSelector);
      Drupal.imageWidgetCrop.initializeCropper($cropper, $cropper.data('ratio'));
    });

    $reset.on('click', function (e) {
      e.preventDefault();
      var $element = $(this).siblings(cropperSelector);
      Drupal.imageWidgetCrop.reset($element);
      return false;
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
    var $values = $element.siblings(cropperValuesSelector);

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
        Drupal.imageWidgetCrop.updateCropSummaries($element);
      }
    });
  };

  /**
   * Initialize cropper on all children of an element.
   *
   * @param $element
   *   Element to initialize cropper on its children.
   */
  Drupal.imageWidgetCrop.initializeCropperOnChildren = function ($element) {
    var visibleCropper = $element.find(cropperSelector + ':visible');
    Drupal.imageWidgetCrop.initializeCropper($(visibleCropper), $(visibleCropper).data('ratio'));
  };

  /**
   * Update crop summaries after cropping cas been set or reset.
   *
   * @param $element
   *   The element cropping on which has been changed
   */
  Drupal.imageWidgetCrop.updateCropSummaries = function ($element) {
    var $values = $element.siblings(cropperValuesSelector);
    var croppingApplied = parseInt($values.find('.crop-applied').val());
    var wrapperText = Drupal.t('Crop image');
    var summaryText = Drupal.t('No cropping applied');
    if (croppingApplied) {
      wrapperText = Drupal.t('Crop image (cropping applied)');
      summaryText = Drupal.t('Cropping applied');
    }
    $element.parents(cropWrapperSelector).children('summary').text(wrapperText);

    $element.closest('details').drupalSetSummary(function (context) {
      return summaryText;
    });
  };

  /**
   * Update crop summaries after cropping cas been set or reset.
   */
  Drupal.imageWidgetCrop.updateAllCropSummaries = function () {
    var $elements = $(cropperSelector);
    $elements.each(function () {
      Drupal.imageWidgetCrop.updateCropSummaries($(this));
    });
  };

  /**
   * Reset cropping for an element
   *
   * @param $element
   *   The element to reset cropping on.
   */
  Drupal.imageWidgetCrop.reset = function ($element) {
    var $values = $element.siblings(cropperValuesSelector);
    $element.cropper('reset');
    $values.find('.crop-x').val('');
    $values.find('.crop-y').val('');
    $values.find('.crop-width').val('');
    $values.find('.crop-height').val('');
    $values.find('.crop-applied').val(0);
    Drupal.imageWidgetCrop.updateCropSummaries($element);
  };

  Drupal.behaviors.imageWidgetCrop = {
    attach: function (context) {
      Drupal.imageWidgetCrop.initialize(context);
    }
  };

  Drupal.imageWidgetCrop.updateAllCropSummaries();

}(jQuery, Drupal, drupalSettings));

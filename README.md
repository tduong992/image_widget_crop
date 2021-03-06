ImageWidgetCrop module
======================

Provides an interface for using the features of the [Crop API]. Module is still 
under heavy development.

[Crop API]: https://github.com/drupal-media/crop

Try me
------
You can Test ImageWidgetCrop in action directly with the sub-module,
"ImageWidgetCrop example" to test different usecases of this module.

Installation
------------
1. Download and extract the module to your sites/all/modules/contrib folder.
2. Enable the module on the Drupal Modules page (admin/modules) or using
   $ drush en

The module is currently using Cropper as a library to display the cropping widget.
To properly configure it, do the following:

* Local library:
  1. Download the latest version of Cropper at
     https://github.com/fengyuanchen/cropper.
  2. Copy the dist folder into:
     - /libraries/cropper or
     - /sites/default/libraries/cropper or
     - /sites/EXAMPLE/libraries/cropper or
     - /sites/all/libraries/cropper
  3. Enable the libraries module.

* External library:
  1. Set the external URL for the minified version of the library and CSS file in
     Image Crop Widget settings (/admin/config/media/crop-widget), found at
     https://cdnjs.com/libraries/cropper.

 NOTE: The external library is set by default when you enable the module.

Configuration
-------------

* Create a Crop Type (`admin/structure/crop`)
* Create ImageStyles  
    * add Manual crop effect, using your Crop Type,
      (to apply your crop selection).
* Create an Image field.
* In its form display, at `admin/structure/types/manage/page/form-display`:
    * set the widget for your field to ImageWidgetCrop 
    * at select your crop types in the Crop settings list. You can configure 
      the widget to create different crops on each crop types. For example, if 
      you have an editorial site, you need to display an image on different 
      places. With this option, you can set an optimal crop zone for each of the
      image styles applied to the image
* Set the display formatter Image and choose your image style,
  or responsive image styles.
* Go add an image with your widget and crop your picture,
  by crop types used for this image.

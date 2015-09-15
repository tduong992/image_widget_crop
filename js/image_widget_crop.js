/**
 * @file
 * Provides JavaScript additions to the managed file field type.
 *
 * This file provides progress bar support (if available), popup windows for
 * file previews, and disabling of other file fields during Ajax uploads (which
 * prevents separate file fields from accidentally uploading files).
 */

(function ($, Drupal) {

    "use strict";

    /**
     * Attach behaviors to links within managed file elements.
     *
     * @type {Drupal~behavior}
     */
    Drupal.behaviors.image_widget_crop = {
        attach: function (context, settings) {
            var path = settings.path.currentPath;
            var array = $(context).find('div.js-form-managed-file > div > ul > div.preview-wrapper-crop li');
            var edit = path.search('edit');
            array.each(function (i, l) {
                var posx1 = $(this).find('input.crop-x1');
                var posy1 = $(this).find('input.crop-y1');
                var posx2 = $(this).find('input.crop-x2');
                var posy2 = $(this).find('input.crop-y2');
                var cropw = $(this).find('input.crop-crop-w');
                var croph = $(this).find('input.crop-crop-h');
                var img = $(this).find('img');

                var width = $(this).find('input.crop-thumb-w');
                var height = $(this).find('input.crop-thumb-h');

                if (edit > -1) {
                    $(img).imgAreaSelect({
                        aspectRatio: $(this).data('ratio'),
                        handles: true,
                        movable: true,
                        onSelectEnd: function (img, selection) {

                            // Calculate X1 / Y1 position of crop zone.
                            $(posx1).val(selection.x1);
                            $(posy1).val(selection.y1);

                            // Calculate X2 / Y2 position of crop zone.
                            $(posx2).val(selection.x2);
                            $(posy2).val(selection.y2);

                            // Calculate width / height size of crop zone.
                            $(cropw).val(selection.width);
                            $(croph).val(selection.height);

                            // Get size of thumbnail in UI.
                            $(width).val(img.width);
                            $(height).val(img.height);

                        },
                        x1: posx1.val(),
                        y1: posy1.val(),
                        x2: posx2.val(),
                        y2: posy2.val()
                    });
                }
                else {
                    $(img).imgAreaSelect({
                        aspectRatio: $(this).data('ratio'),
                        handles: true,
                        movable: true,
                        onSelectEnd: function (img, selection) {

                            // Calculate X1 / Y1 position of crop zone.
                            $(posx1).val(selection.x1);
                            $(posy1).val(selection.y1);

                            // Calculate X2 / Y2 position of crop zone.
                            $(posx2).val(selection.x2);
                            $(posy2).val(selection.y2);

                            // Calculate width / height size of crop zone.
                            $(cropw).val(selection.width);
                            $(croph).val(selection.height);

                            // Get size of thumbnail in UI.
                            $(width).val(img.width);
                            $(height).val(img.height);

                        }
                    });
                }
            });
        }
    };

})(jQuery, Drupal);

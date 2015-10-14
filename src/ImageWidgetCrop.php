<?php

/**
 * @file
 * Contains of \Drupal\image_widget_crop\ImageWidgetCrop.
 */

namespace Drupal\image_widget_crop;

use Drupal\image\Entity\ImageStyle;
use Drupal\crop\Entity\CropType;

/**
 * ImageWidgetCrop calculation class.
 */
class ImageWidgetCrop {

  /**
   * Get original size of a thumbnail image.
   *
   * @param array $properties
   *   All properties returned by the crop plugin (js),
   *   and the size of thumbnail image.
   * @param array|mixed $field_value
   *   An array of values for the contained properties of image_crop widget.
   * @param CropType $crop_type
   *   The entity CropType.
   * @param bool $edit
   *   The action form.
   */
  public function cropByImageStyle(array $properties, $field_value, CropType $crop_type, $edit) {
    // Get Original sizes and position of crop zone.
    $crop_properties = $this->getCropOriginalDimension($field_value['height'], $properties);
    // Get all imagesStyle used this crop_type.
    $image_styles = $this->getImageStylesByCrop($crop_type->id());

    if (isset($edit)) {
      // @TODO use injection.
      $crop = \Drupal::service('entity.manager')
        ->getStorage('crop')->loadByProperties([
          'type' => $crop_type->id(),
          'uri' => $field_value['file-uri'],
        ]);

      if (!empty($crop)) {
        /** @var \Drupal\crop\Entity\Crop $crop_entity */
        foreach ($crop as $crop_id => $crop_entity) {
          $crop_position = $crop_entity->position();
          $crop_size = $crop_entity->size();
          $old_crop = array_merge($crop_position, $crop_size);
          // Verify if the crop (dimensions / positions) have changed.
          if (($crop_properties['x'] == $old_crop['x'] && $crop_properties['width'] == $old_crop['width']) && ($crop_properties['y'] == $old_crop['y'] && $crop_properties['height'] == $old_crop['height'])) {
            return;
          }
          else {
            // Parse all properties if this crop have changed.
            foreach ($crop_properties as $crop_coordinate => $value) {
              // Edit the crop properties if he have changed.
              $crop[$crop_id]->set($crop_coordinate, $value, TRUE)
                ->save();
            }

            foreach ($image_styles as $image_style) {
              // Flush the cache of this ImageStyle.
              $image_style->flush($field_value['file-uri']);
            }
          }
        }
      }
      else {
        $this->saveCrop($crop_properties, $field_value, $image_styles, $crop_type);
      }
    }
    else {
      $this->saveCrop($crop_properties, $field_value, $image_styles, $crop_type);
    }
  }

  /**
   * Get the size and position of the crop.
   *
   * @param int $original_height
   *   The original height of image.
   * @param array $properties
   *   The original height of image.
   *
   * @return array<double>
   *   The data dimensions (width & height) into this ImageStyle.
   */
  public function getCropOriginalDimension($original_height, array $properties) {
    $delta = $original_height / $properties['thumb-h'];

    // Get Center coordinate of crop zone.
    $axis_coordinate = $this->getAxisCoordinates(
      ['x' => $properties['x1'], 'y' => $properties['y1']],
      ['width' => $properties['crop-w'], 'height' => $properties['crop-h']]
    );

    // Calculate coordinates (position & sizes) of crop zone.
    $crop_coordinates = $this->getCoordinates([
      'width' => $properties['crop-w'],
      'height' => $properties['crop-h'],
      'x' => $axis_coordinate['x'],
      'y' => $axis_coordinate['y'],
    ], $delta);

    return $crop_coordinates;
  }

  /**
   * Get center of crop selection.
   *
   * @param int[] $axis
   *   Coordinates of x-axis & y-axis.
   * @param array $crop_selection
   *   Coordinates of crop selection (width & height).
   *
   * @return array<integer>
   *   Coordinates (x-axis & y-axis) of crop selection zone.
   */
  public function getAxisCoordinates(array $axis, array $crop_selection) {
    return [
      'x' => (int) $axis['x'] + ($crop_selection['width'] / 2),
      'y' => (int) $axis['y'] + ($crop_selection['height'] / 2),
    ];
  }

  /**
   * Calculate all coordinates for apply crop into original picture.
   *
   * @param array $properties
   *   All properties returned by the crop plugin (js),
   *   and the size of thumbnail image.
   * @param int $delta
   *   The calculated difference between original height and thumbnail height.
   *
   * @return array<double>
   *   Coordinates (x & y or width & height) of crop.
   */
  public function getCoordinates(array $properties, $delta) {
    $original_coordinates = [];

    foreach ($properties as $key => $coordinate) {
      if (isset($coordinate) && $coordinate >= 0) {
        $original_coordinates[$key] = round($coordinate * $delta);
      }
    }

    return $original_coordinates;
  }

  /**
   * Get the imageStyle using this crop_type.
   *
   * @param string $crop_type_name
   *   The id of the current crop_type entity.
   *
   * @return array
   *   All imageStyle used this crop_type.
   */
  public function getImageStylesByCrop($crop_type_name) {
    $styles = [];
    $image_styles = ImageStyle::loadMultiple();

    foreach ($image_styles as $image_style) {
      /* @var  \Drupal\image\ImageEffectInterface $effect */
      foreach ($image_style->getEffects() as $uuid => $effect) {
        if ($effect instanceof \Drupal\crop\Plugin\ImageEffect\CropEffect) {
          if ($image_style->getEffect($uuid)
              ->getConfiguration()['data']['crop_type'] == $crop_type_name
          ) {
            $styles[] = $image_style;
          }
        }
      }
    }

    return $styles;
  }

  /**
   * Save the crop when this crop not exist.
   *
   * @param double[] $crop_properties
   *   The properties of the crop applied to the original image (dimensions).
   * @param array|mixed $field_value
   *   An array of values for the contained properties of image_crop widget.
   * @param array $image_styles
   *   The machine name of ImageStyle.
   * @param string $crop_type
   *   The name of Crop type.
   */
  public function saveCrop(array $crop_properties, $field_value, array $image_styles, $crop_type) {
    /** @var \Drupal\image\Entity\ImageStyle $image_style */
    foreach ($image_styles as $image_style) {
      $values = [
        'type' => $crop_type->id(),
        'entity_id' => $field_value['file-id'],
        'entity_type' => 'file',
        'uri' => $field_value['file-uri'],
        'x' => $crop_properties['x'],
        'y' => $crop_properties['y'],
        'width' => $crop_properties['width'],
        'height' => $crop_properties['height'],
        'image_style' => $image_style->getName(),
      ];

      // Save crop with previous values.
      // @TODO use injection.
      /** @var \Drupal\crop\CropInterface $crop */
      $crop = \Drupal::entityManager()->getStorage('crop')->create($values);
      $crop->save();

      // Generate the image derivate uri.
      $destination_uri = $image_style->buildUri($field_value['file-uri']);

      // Create a derivate of the original image with a good uri.
      $image_style->createDerivative($field_value['file-uri'], $destination_uri);

      // Flush the cache of this ImageStyle.
      $image_style->flush($field_value['file-uri']);
    }
  }

  /**
   * Delete the crop when user delete it.
   *
   * @param string $file_uri
   *   Uri of image uploaded by user.
   * @param \Drupal\crop\Entity\CropType $crop_type
   *   The CropType object.
   */
  public function deleteCrop($file_uri, CropType $crop_type) {
    $image_styles = $this->getImageStylesByCrop($crop_type->id());
    /** @var \Drupal\image\Entity\ImageStyle $image_style */
    foreach ($image_styles as $image_style) {
      // @TODO use injection.
      /** @var \Drupal\crop\CropInterface $crop */
      $crop = \Drupal::service('entity.manager')
        ->getStorage('crop')->loadByProperties([
          'type' => $crop_type->id(),
          'uri' => $file_uri,
          'image_style' => $image_style->getName(),
        ]);

      if (isset($crop)) {
        // @TODO use injection.
        /** @var \Drupal\crop\CropInterface $crop */
        $crop_storage = \Drupal::entityManager()->getStorage('crop');
        $crop_storage->delete($crop);

        // Flush the cache of this ImageStyle.
        $image_style->flush($file_uri);
      }
    }
  }

}

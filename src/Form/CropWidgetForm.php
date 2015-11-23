<?php

/**
 * @file
 * Contains \Drupal\image_widget_crop\Form\CropWidgetForm.
 */

namespace Drupal\image_widget_crop\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\ConfigFormBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure ImageWidgetCrop general settings for this site.
 */
class CropWidgetForm extends ConfigFormBase {

  /**
   * Constructs a CropWidgetForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    parent::__construct($config_factory);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static (
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'crop_widget_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['iwc.crop-widget'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\Core\Config\Config $config */
    $config = $this->config('iwc.crop-widget');
    $form['crop_upload_location'] = array(
      '#type' => 'textfield',
      '#title' => t('Image upload location path'),
      '#default_value' => !empty($config->get('crop_upload_location')) ? $config->get('crop_upload_location') : 'public://crop/pictures/',
      '#maxlength' => 255,
      '#description' => t('A local file system path where croped images files will be stored. Spécify the location of files instead of \'sites/default/files\' folder')
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\Core\Config\Config $config */
    $config = $this->config('iwc.crop-widget')
      ->set('crop_upload_location', $form_state->getValue('crop_upload_location'));
    $config->save();

    parent::submitForm($form, $form_state);
  }

}

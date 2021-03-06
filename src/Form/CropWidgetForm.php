<?php

/**
 * @file
 * Contains \Drupal\image_widget_crop\Form\CropWidgetForm.
 */

namespace Drupal\image_widget_crop\Form;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\ConfigFormBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure ImageWidgetCrop general settings for this site.
 */
class CropWidgetForm extends ConfigFormBase {

  /**
   * The settings of image_widget_crop configuration.
   *
   * @var array
   *
   * @see \Drupal\Core\Config\Config
   */
  protected $settings;

  /**
   * Constructs a CropWidgetForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    parent::__construct($config_factory);
    $this->settings = $this->config('image_widget_crop.settings');
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
    return 'image_widget_crop_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['image_widget_crop.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $url = 'https://cdnjs.com/libraries/cropper';
    $form['library_url'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Remote URL for the Cropper library'),
      '#description' => $this->t('Set the URL for a Web-Hosted Cropper library (minified), or leave empty if using the library locally. You can retrieve the library from <a href="@url">Cropper CDN</a>.', array(
        '@url' => $url,
      )),
      '#default_value' => $this->settings->get('settings.library_url'),
    );

    $form['css_url'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Remote URL for the Cropper CSS file'),
      '#description' => $this->t('Set the URL for a Web-Hosted Cropper CSS file (minified), or leave empty if using the CSS file locally. You can retrieve the CSS file from <a href="@url">Cropper CDN</a>.', array(
        '@url' => $url,
      )),
      '#default_value' => $this->settings->get('settings.css_url'),
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * Validation for cropper library.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    // TODO: Change the autogenerated stub.
    if (\Drupal::moduleHandler()->moduleExists('libraries')) {
      $directory = libraries_get_path('cropper');
      $library = 'cropper.min.js';
      $css = 'cropper.min.css';
      if (!file_exists($directory . '/' . $library) || !file_exists($directory . '/' . $css)) {
        $form_state->setErrorByName('plugin', t('Either the library file or the CSS file is not present in the directory %directory.', array(
          '%directory' => $directory,
        )));
      }
    }
    else {
      if (empty($form_state->getValue('library_url')) || empty($form_state->getValue('css_url'))) {
        $form_state->setErrorByName('plugin', t('Either set the library and CSS locally (in /libraries/cropper) and enable the libraries module or enter the remote URLs below.'));
      }
      $cropper_cdn_url = 'https://cdnjs.com/libraries/cropper';
      if (!empty($form_state->getValue('library_url'))) {
        // Check if the name of the library in the remote URL is as expected.
        $library_url = $form_state->getValue('library_url');
        if (parse_url($library_url, PHP_URL_HOST) && parse_url($library_url, PHP_URL_PATH)) {
          $js = pathinfo($library_url, PATHINFO_BASENAME);
          if (!preg_match('/^cropper\.min\.js$/', $js)) {
            $form_state->setErrorByName('plugin', t('The naming of the library is unexpected. Double check that this is the real Cropper library. The URL for the minimized version of the library can be found on <a href="@url">Cropper CDN</a>.', ['@url' => $cropper_cdn_url]), 'warning');
          }
        }
        else {
          $form_state->setErrorByName('plugin', t('The remote URL for the library is unexpected. Please, provide the correct URL to the minimized version of the library found on <a href="@url">Cropper CDN</a>.', ['@url' => $cropper_cdn_url]), 'error');
        }
      }
      elseif (!empty($form_state->getValue('css_url'))) {
        // Check if the name of the library in the remote URL is as expected.
        $css_url = $form_state->getValue('css_url');
        if (parse_url($css_url, PHP_URL_HOST) && parse_url($css_url, PHP_URL_PATH)) {
          $css = pathinfo($css_url, PATHINFO_BASENAME);
          if (!preg_match('/^cropper\.min\.css$/', $css)) {
            $form_state->setErrorByName('plugin', t('The naming of the CSS is unexpected. Double check that this is the real Cropper CSS file. The URL for the minimized version of the CSS fuke can be found on <a href="@url">Cropper CDN</a>.', ['@url' => $cropper_cdn_url]), 'warning');
          }
        }
        else {
          $form_state->setErrorByName('plugin', t('The remote URL for the CSS file is unexpected. Please, provide the correct URL to the minimized version of the CSS file found on <a href="@url">Cropper CDN</a>.', ['@url' => $cropper_cdn_url]), 'error');
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    // We need to rebuild the library cache if we switch from remote to local
    // library or vice-versa.
    Cache::invalidateTags(['library_info']);

    $this->settings
      ->set("settings.library_url", $form_state->getValue('library_url'))
      ->set('settings.css_url', $form_state->getValue('css_url'));

    $this->settings->save();
  }

}

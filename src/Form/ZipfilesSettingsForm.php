<?php

namespace Drupal\zipfiles\Form;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Menu\MenuLinkManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class AdminToolbarSettingsForm. The config form for the admin_toolbar module.
 *
 * @package Drupal\admin_toolbar\Form
 */
class ZipfilesSettingsForm extends ConfigFormBase {

  /**
   * {@inheritDoc}
   */
  protected function getEditableConfigNames() {
    return [
      'zipfiles.settings',
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function getFormId() {
    return 'zipfiles_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('zipfiles.settings');
    $filename = $config->get('filename');
    $form['filename'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Filename'),
      '#description' => $this->t('Enter the name to use in the generated file, you can use tokens if available.'),
      '#default_value' => $filename,
    ];
    if (\Drupal::service('module_handler')->moduleExists('token')) {
      // your code here
      $form['token_help'] = [
        '#theme' => 'token_tree_link',
        '#token_types' => ['user', 'date', 'node'],
      ];
    }


    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

   $this->config('zipfiles.settings')
      ->set('filename', $form_state->getValue('filename'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}


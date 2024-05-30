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
    $save = $config->get('save') ?? 0;
    $save_uri = $config->get('save_uri');

    $form['filename'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Filename'),
      '#description' => $this->t('Enter the name to use in the generated file, you can use tokens if available.'),
      '#default_value' => $filename,
    ];
    if (\Drupal::service('module_handler')->moduleExists('token')) {
      $form['token_help'] = [
        '#theme' => 'token_tree_link',
        '#token_types' => ['user', 'date', 'node'],
      ];
    }
    $form['save'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Save file permanently'),
      '#description' => $this->t('If not active, the file is saved temporarily'),
      '#default_value' => $save,
    ];
    $form['save_uri'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Save in'),
      '#description' => $this->t('Enter the uri where the file will be saved. For example "public://zipfiles" or "private://zipfiles". Save in public is not recomended.'),
      '#default_value' => $save_uri,
      '#states' => [
        'visible' => [
          ':input[name="save"]' => ['checked' => TRUE],
        ],
      ]
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

    if ($form_state->getValue('save') && empty($save_uri = $form_state->getValue('save_uri'))) {
      $form_state->setErrorByName('save_uri', $this->t('You cannot leave this field empty.'));
    }
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $save_uri = NULL;
    if ($form_state->getValue('save')) {
      $save_uri = $form_state->getValue('save_uri');
    }

    $this->config('zipfiles.settings')
      ->set('filename', $form_state->getValue('filename'))
      ->set('save', $form_state->getValue('save'))
      ->set('save_uri', $save_uri)
      ->save();
    parent::submitForm($form, $form_state);
  }

}

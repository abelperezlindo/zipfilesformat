<?php

namespace Drupal\zipfiles\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\file\Plugin\Field\FieldFormatter\GenericFileFormatter;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'file_default' formatter.
 *
 * @FieldFormatter(
 *   id = "file_zipfiles_default",
 *   label = @Translation("Generic ZIP file"),
 *   field_types = {
 *     "file"
 *   }
 * )
 */
class ZipFilesFormatter extends GenericFileFormatter {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = parent::viewElements($items, $langcode);
    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);

    $form['if_two_or_more_one'] = [
      '#title' => $this->t('Generate link if have two or more files'),
      '#description' => $this->t('Replace the file name by its description when available'),
      '#type' => 'checkbox',
      '#default_value' => $this->getSetting('if_two_or_more_one'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    $settings = parent::defaultSettings();
    $settings['if_two_or_more_one'] = FALSE;
    return $settings;
  }

}

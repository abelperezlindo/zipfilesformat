<?php

namespace Drupal\zipfiles\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\file\Plugin\Field\FieldFormatter\GenericFileFormatter;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\Link;

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
    if (!$this->getSetting('always_generate_link') && $items->count() == 1) {
      return parent::viewElements($items, $langcode);
    }

    $node = $items->getEntity();
    $params = ['field_name' => $items->getName(), 'nid' => $node->id()];
    $url = Url::fromRoute('zipfiles.download', $params);
    $link = Link::fromTextAndUrl($this->t('Download all'), $url);

    return $link->toRenderable();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);

    $form['always_generate_link'] = [
      '#title' => $this->t('Always generate ZIP link'),
      '#description' => $this->t('If not active, the link is only generated if the field has more than one file'),
      '#type' => 'checkbox',
      '#default_value' => $this->getSetting('always_generate_link'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    $settings = parent::defaultSettings();
    $settings['always_generate_link'] = FALSE;
    return $settings;
  }

}

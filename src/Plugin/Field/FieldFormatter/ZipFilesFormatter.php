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
 *   module = "zipfiles",
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
    if ($this->getSetting('no_link_when_only_one') && $items->count() == 1) {
      return $elements;
    }
    if ($this->getSetting('only_show_zip_link')) {
      unset($elements);
      $elements = [];
    }

    $label = $this->getSetting('link_text_label');
    $node = $items->getEntity();
    $params = ['field_name' => $items->getName(), 'nid' => $node->id()];
    $url = Url::fromRoute('zipfiles.download', $params);
    $link = Link::fromTextAndUrl($label, $url);
    $elements[] = $link->toRenderable();

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);

    $form['only_show_zip_link'] = [
      '#title' => $this->t('Only show the link to the compressed file'),
      '#type' => 'checkbox',
      '#default_value' => $this->getSetting('only_show_zip_link'),
    ];
    $form['no_link_when_only_one'] = [
      '#title' => $this->t('Don\'t show when there is only one file'),
      '#type' => 'checkbox',
      '#default_value' => $this->getSetting('no_link_when_only_one'),
    ];
    $form['link_text_label'] = [
      '#title' => $this->t('Don\'t show when there is only one file'),
      '#type' => 'textfield',
      '#default_value' => $this->getSetting('link_text_label'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'only_show_zip_link' => FALSE,
      'no_link_when_only_one_file' => TRUE,
      'link_text_label' => 'Download all',
    ] + parent::defaultSettings();
  }

}

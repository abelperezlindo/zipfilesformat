<?php

namespace Drupal\zip_field_files\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\file\Plugin\Field\FieldFormatter\GenericFileFormatter;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\Link;

/**
 * Plugin implementation of the 'file_default' formatter.
 *
 * @FieldFormatter(
 *   id = "file_zip_field_files_default",
 *   module = "zip_field_files",
 *   label = @Translation("ZIP file"),
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
    $url = Url::fromRoute('zip_field_files.download', $params);
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
      '#description' => $this->t('By default, this formatter displays a link to each file and at the end the link to download all of them inside a zip. If you check this option, only the link to download the compressed file will be displayed.'),
      '#default_value' => $this->getSetting('only_show_zip_link'),
    ];
    $form['no_link_when_only_one'] = [
      '#title' => $this->t('Don\'t show when there is only one file'),
      '#description' => $this->t('If there is only one file in the field, it may not make sense to download it in a zip file.'),
      '#type' => 'checkbox',
      '#default_value' => $this->getSetting('no_link_when_only_one'),
    ];
    $form['link_text_label'] = [
      '#title' => $this->t('Text for zip link'),
      '#description' => $this->t('You can add the text you want to display in the download link.'),
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

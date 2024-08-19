<?php

namespace Drupal\Tests\zipfiles\Functional;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Tests\file\Functional\FileFieldTestBase;

/**
 * Skeleton functional test.
 */
class FieldFormatterTest extends FileFieldTestBase {

  const FIELD_NAME = 'field_multi_files';

  /**
   * The theme to install as the default for testing.
   *
   * When using the default testing install profile we need to specify
   * which theme to use when running functional tests.
   *
   * For tests that do not rely on any specific markup, or at least not Drupal
   * core markup, use 'stark'. For tests that rely on core markup use 'stable'.
   *
   * @link https://www.drupal.org/node/3083055
   *
   * @var string
   */
  protected $defaultTheme = 'stark';

  /**
   * Modules to install.
   *
   * This array of modules will be enabled when the fixture Drupal site is
   * built. We leave it empty here because this is a skeleton test. A typical
   * test will enable basic modules like node and user.
   *
   * @var string[]
   */
  protected static $modules = [
    'node',
    'zipfiles',
    'user',
    'field',
    'field_ui',
    'file_test',
    'image',
  ];

  /**
   * Fixture user with administrative powers.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $webUser;

  /**
   * {@inheritdoc}
   *
   * The setUp() method is run before every other test method, so commonalities
   * should go here.
   */


  /**
   * The Entity View Display for the article node type.
   *
   * @var \Drupal\Core\Entity\Entity\EntityViewDisplay
   */
  protected EntityViewDisplay $display;

  /**
   * The Entity Form Display for the article node type.
   *
   * @var \Drupal\Core\Entity\Entity\EntityFormDisplay
   */
  protected EntityFormDisplay $form;

  /**
   * Set up.
   */
  protected function setUp(): void {
    parent::setUp();
    $entityTypeManager = $this->container->get('entity_type.manager');
    // Create an field zipfiles type.
    FieldStorageConfig::create([
      'field_name' => self::FIELD_NAME,
      'entity_type' => 'node',
      'type' => 'file',
      'cardinality' => -1,
      'settings' => [
        'uri_scheme' => 'public',
      ],
    ])->save();

    // Add this field in test content type.
    FieldConfig::create([
      'field_name' => self::FIELD_NAME,
      'label' => 'Files',
      'description' => 'Files 2 zip',
      'entity_type' => 'node',
      'bundle' => 'article',
    ])->save();

    $this->form = $entityTypeManager->getStorage('entity_form_display')
      ->load('node.article.default');
    $this->display = $entityTypeManager->getStorage('entity_view_display')
      ->load('node.article.default');

    $this->form->setComponent(self::FIELD_NAME, [
      'type' => 'file_generic',
      'settings' => [
        'throbber' => TRUE,
      ],
    ])->save();

    $this->display->setComponent(self::FIELD_NAME, [
      'type' => 'file_zipfiles_default',
      'weight' => 10,
      'label' => 'hidden',
    ])->save();
  }

  /**
   * Test download zip compresed file.
   */
  public function testUploadNodeFilesAndDownloadZip() {
    // Display creation form.
    $this->drupalGet('node/add/article');
    $this->assertSession()->responseContains(self::FIELD_NAME);

    $file_system = \Drupal::service('file_system');

    // Create a node with three (3) test files.
    $text_files = $this->drupalGetTestFiles('text');
    $this->drupalGet("node/add/article");
    $this->submitForm(["files[" . self::FIELD_NAME . "_0][]" => $file_system->realpath($text_files[0]->uri)], 'Upload');
    $this->submitForm(["files[" . self::FIELD_NAME . "_1][]" => $file_system->realpath($text_files[1]->uri)], 'Upload');
    $edit = [
      'title[0][value]' => $this->randomMachineName(),
      "files[" . self::FIELD_NAME . "_2][]" => $file_system->realpath($text_files[1]->uri),
    ];
    $this->submitForm($edit, 'Save');

    $this->drupalGet('/node/1');
    $this->assertSession()->responseContains('Download all');
    $this->clickLink('Download all');
    // Is zip in content type??
    $returned_header = $this->getSession()->getResponseHeader('Content-Type');
    $this->assertSame('application/zip', $returned_header);
  }

}

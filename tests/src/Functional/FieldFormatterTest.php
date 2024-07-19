<?php

namespace Drupal\Tests\zipfiles\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Tests\file\Functional\FileFieldTestBase;
use Drupal\Tests\TestFileCreationTrait;

/**
 * Skeleton functional test.
 *
 * This test shows you how you can add a failing test in order to make sure the
 * test you've set up is discovered by the test runners. The test is not
 * initially set up to fail. You have to modify the testFail() method to see the
 * fail.
 *
 * Each part of this test is required, and has comments explaining why it's
 * there.
 *
 * You can run this test using either phpunit or run-tests.sh. To use phpunit to
 * make sure the test is discovered, you can call it like this:
 *
 * @code
 * cd drupal/root
 * SIMPLETEST_BASE_URL=http://example.com/ ./vendor/bin/phpunit -c core/ --testsuite functional --filter SkeletonTest
 * @endcode
 *
 * For run-tests.sh:
 *
 * @code
 * cd drupal/root
 * php ./core/scripts/run-tests.sh --browser --url http://example.com --types PHPUnit-Functional testing_example
 * @endcode
 *
 * All tests must have a least one group annotation. run-tests.sh uses the first
 * one only.
 *
 * @group testing_example
 * @group examples
 *
 * @ingroup testing_example
 */
class FieldFormatterTest extends FileFieldTestBase {

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
   *
   * @todo place this in base test class.c
   * see https://git.drupalcode.org/project/color_field/-/tree/3.0.x/tests/src/Functional?ref_type=heads
   * see https://git.drupalcode.org/project/svg_image_field/-/tree/2.3.x/tests/src/Functional?ref_type=heads
   * see https://git.drupalcode.org/project/file_download_link/-/blob/2.0.x/tests/src/Functional/FileDownloadLinkFormTest.php?ref_type=heads
   */
  protected function setUp(): void {
    parent::setUp();

    //$this->drupalCreateContentType(['type' => 'article']);
    // Create users.
    $this->webUser = $this->drupalCreateUser([
      'access administration pages',
      'view the administration theme',
      'administer permissions',
      'administer nodes',
      'administer content types',
      'create article content',
    ]);

    //$this->drupalLogin($this->webUser);
    $entityTypeManager = $this->container->get('entity_type.manager');
    // Create an field zipfiles type.
    FieldStorageConfig::create([
      'field_name' => 'field_files_to_zipear',
      'entity_type' => 'node',
      'type' => 'file',
      'cardinality' => -1,
      'settings' => [
        'uri_scheme' => 'public',
      ],
    ])->save();

    // Add this field in test content type.
    FieldConfig::create([
      'field_name' => 'field_files_to_zipear',
      'label' => 'Zipear',
      'description' => 'files 2 zipear',
      'entity_type' => 'node',
      'bundle' => 'article',

    ])->save();

    $this->form = $entityTypeManager->getStorage('entity_form_display')
      ->load('node.article.default');
    $this->display = $entityTypeManager->getStorage('entity_view_display')
      ->load('node.article.default');

    $this->form->setComponent('field_files_to_zipear', [
      'type' => 'file_generic',
      'settings' => [
        'throbber' => TRUE,
      ],
    ])->save();

    $this->display->setComponent('field_files_to_zipear', [
      'type' => 'file_zipfiles_default',
      'weight' => 10,
      'label' => 'hidden',
    ])->save();

    // Display creation form.
    $this->drupalGet('node/add/article');
    $this->assertSession()->responseContains('field_files_to_zipear');

    $file_system = \Drupal::service('file_system');

    // Create a multivalue File field with 'node/[node:nid]' as the File path
    // and '[file:fid].txt' as the File name.
    $field_name = 'field_files_to_zipear';

    // Create a node with three (3) test files.
    $text_files = $this->drupalGetTestFiles('text');
    $this->drupalGet("node/add/article");
    $this->submitForm(["files[{$field_name}_0][]" => $file_system->realpath($text_files[0]->uri)], 'Upload');
    $this->submitForm(["files[{$field_name}_1][]" => $file_system->realpath($text_files[1]->uri)], 'Upload');
    $edit = [
      'title[0][value]' => $this->randomMachineName(),
      "files[{$field_name}_2][]" => $file_system->realpath($text_files[1]->uri),
    ];
    $this->submitForm($edit, 'Save');


    $session = $this->assertSession();
    $this->drupalGet('/node/1');
    $this->assertSession()->responseContains('Download all');
    $this->drupalGet('/admin/structure/types/manage/article/fields');
  }

  /**
   * This test method fails, so we can be sure our test is discovered.
   */
  public function testFail() {

    $this->assertTrue(TRUE);
  }

  /**
   * Test download zip compresed file.
   */
  public function testDownloadCompresed() {

    $this->assertTrue(TRUE);
  }

  protected function resourcesUrl(): string {
    return \Drupal::request()->getSchemeAndHttpHost() . '/' . $this->getModulePath('zipfiles') . '/tests/resources';
  }

  protected function resourcesPath(): string {
    $absolute = realpath(getcwd());
    return $absolute . '/' . $this->getModulePath('zipfiles') . '/tests/resources';
  }

  protected function getUrisOfTestFiles() {
    $resources_path  =$this->resourcesPath() . '/';
    return [
      $resources_path . 'text_file.txt',
      $resources_path . 'pdf_file.pdf',
      $resources_path . 'png_file.png',
      $resources_path . 'jpeg_file.jpeg',
    ];
  }
}

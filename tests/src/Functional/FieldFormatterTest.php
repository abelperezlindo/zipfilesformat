<?php

namespace Drupal\Tests\zipfiles\Functional;

use Drupal\Tests\BrowserTestBase;

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
class FieldFormatterTest extends BrowserTestBase {

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
  protected static $modules = ['node', 'user', 'field', 'file_test', 'image'];

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
  protected function setUp(): void {
    parent::setUp();

    $this->createContentType(['type' => 'test_content_type']);
    // Create users.
    $this->webUser = $this->drupalCreateUser([
      'access administration pages',
      'view the administration theme',
      'administer permissions',
      'administer nodes',
      'administer content types',
      'create test_content_type content',
    ]);

    $this->drupalLogin($this->webUser);
    $this->drupalGet('/admin/structure/types/manage/test_content_type/fields');

    //$this->drupalGet('/admin/structure/types/manage/test_content_type/fields');

    /** @var \Drupal\Tests\WebAssert $assert */
    //$assert = $this->assertSession();
    //$assert->buttonExists('Create a new field');
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

}

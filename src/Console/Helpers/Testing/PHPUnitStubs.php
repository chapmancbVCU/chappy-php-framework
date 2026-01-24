<?php
declare(strict_types=1);
namespace Console\Helpers\Testing;

/**
 * Collection of template stub files for feature and unit test files.
 */
class PHPUnitStubs {
    /**
     * The template for a new Feature test class that extends ApplicationTestCase.
     *
     * @param string $testName The name of the TestCase class.
     * @return string The contents for the new TestCase class.
     */
    public static function featureTestStub(string $testName): string {
        return <<<PHP
<?php
namespace Tests\Feature;
use Core\Lib\Testing\ApplicationTestCase;

/**
 * Unit tests
 */
class {$testName} extends ApplicationTestCase {
    /**
     * Example for testing home page.
     *
     * @return void
     */
    public function test_homepage_loads_successfully(): void
    {
        \$response = \$this->get('/');
        \$response->assertStatus(200);
    }
}
PHP;
    }

    /**
     * The template for a new Unit Test class that extends TestCase.
     *
     * @param string $testName The name of the TestCase class.
     * @return string The contents for the new TestCase class.
     */
    public static function unitTestStub(string $testName): string {
        return <<<PHP
<?php
namespace Tests\Unit;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests
 */
class {$testName} extends TestCase {
    /**
    * Example test.
    *
    * @return void
    */
    public function test_example(): void
    {
        \$this->assertTrue(true);
    }
}
PHP;
    }
}
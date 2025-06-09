<?php
declare(strict_types=1);
namespace Core\Lib\Testing;

use PHPUnit\Framework\Assert;


/**
 * A simplified test response wrapper that mimics HTTP-style responses for use in feature tests.
 *
 * This class is used to encapsulate content and status from simulated controller executions,
 * and provides assertion methods to verify test outcomes such as HTTP status or content presence.
 *
 * Example usage in a test:
 *   $response = $this->get('/');
 *   $response->assertStatus(200);
 *   $response->assertSee('Welcome');
 */

class TestResponse
{
    /**
     * The response body content.
     *
     * @var string
     */
    protected string $content;

    /**
     * The simulated HTTP status code.
     *
     * @var int
     */
    protected int $status;

    /**
     * Constructs a new TestResponse instance.
     *
     * @param string $content The response body (typically HTML or JSON).
     * @param int $status The HTTP status code (default is 200).
     */
    public function __construct(string $content, int $status = 200)
    {
        $this->content = $content;
        $this->status = $status;
    }

    /**
     * Asserts that the given text is not present in the response content.
     *
     * @param string $text The string that should not appear in the content
     * @return void
     */
    public function assertDontSee(string $text): void
    {
        Assert::assertStringNotContainsString(
            $text,
            $this->content,
            "Unexpected text '{$text}' found in response."
        );
    }

    /**
     * Asserts that the response content is a valid JSON string and that it
     * contains the specified keys and values.
     *
     * @param array $expected An associative array of expected key-value pairs
     * @return void
     */
    public function assertJson(array $expected): void
    {
        $decoded = json_decode($this->content, true);
        Assert::assertIsArray($decoded, 'Response content is not valid JSON.');

        foreach ($expected as $key => $value) {
            Assert::assertArrayHasKey($key, $decoded, "Key '{$key}' not found in JSON response.");
            Assert::assertSame($value, $decoded[$key], "Mismatched value for key '{$key}' in JSON.");
        }
    }

    /**
     * Asserts that the response content contains the given text.
     *
     * @param string $text The text expected to be found in the response content.
     * @return void
     */
    public function assertSee(string $text): void
    {
        Assert::assertStringContainsString(
            $text,
            $this->content,
            "Did not see expected text '{$text}' in response."
        );
    }

    /**
     * Asserts that the response status matches the expected value.
     *
     * @param int $expected The expected HTTP status code.
     * @return void
     */
    public function assertStatus(int $expected): void
    {
        Assert::assertSame(
            $expected,
            $this->status,
            "Expected response status {$expected} but got {$this->status}."
        );
    }

    /**
     * Returns the response content.
     *
     * @return string The body of the response.
     */
    public function getContent(): string
    {
        return $this->content;
    }
}

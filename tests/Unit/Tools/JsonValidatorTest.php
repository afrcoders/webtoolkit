<?php

namespace Tests\Unit\Tools;

use Tests\TestCase;

class JsonValidatorTest extends TestCase
{
    public function test_validates_correct_json(): void
    {
        $json = '{"name":"John","age":30}';
        $decoded = json_decode($json);

        $this->assertNotNull($decoded);
        $this->assertEquals(JSON_ERROR_NONE, json_last_error());
    }

    public function test_detects_invalid_json(): void
    {
        $invalidJson = '{"name":"John",age:30}';
        $decoded = json_decode($invalidJson);

        $this->assertNull($decoded);
        $this->assertNotEquals(JSON_ERROR_NONE, json_last_error());
    }

    public function test_validates_json_array(): void
    {
        $json = '[1,2,3,4,5]';
        $decoded = json_decode($json);

        $this->assertNotNull($decoded);
        $this->assertIsArray($decoded);
    }

    public function test_validates_nested_json(): void
    {
        $json = '{"user":{"name":"John","address":{"city":"NYC"}}}';
        $decoded = json_decode($json);

        $this->assertNotNull($decoded);
        $this->assertEquals('NYC', $decoded->user->address->city);
    }

    public function test_formats_json_with_pretty_print(): void
    {
        $json = '{"name":"John","age":30}';
        $decoded = json_decode($json);
        $formatted = json_encode($decoded, JSON_PRETTY_PRINT);

        $this->assertStringContainsString("\n", $formatted);
        $this->assertStringContainsString('    ', $formatted);
    }

    public function test_handles_empty_json_object(): void
    {
        $json = '{}';
        $decoded = json_decode($json);

        $this->assertNotNull($decoded);
        $this->assertIsObject($decoded);
    }

    public function test_handles_empty_json_array(): void
    {
        $json = '[]';
        $decoded = json_decode($json);

        $this->assertNotNull($decoded);
        $this->assertIsArray($decoded);
        $this->assertEmpty($decoded);
    }

    public function test_handles_json_with_unicode(): void
    {
        $json = '{"message":"Hello 世界"}';
        $decoded = json_decode($json);

        $this->assertNotNull($decoded);
        $this->assertEquals('Hello 世界', $decoded->message);
    }
}

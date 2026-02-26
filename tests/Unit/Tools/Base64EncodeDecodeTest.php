<?php

namespace Tests\Unit\Tools;

use Tests\TestCase;

class Base64EncodeDecodeTest extends TestCase
{
    public function test_encodes_string_to_base64(): void
    {
        $input = 'Hello World';
        $encoded = base64_encode($input);

        $this->assertEquals('SGVsbG8gV29ybGQ=', $encoded);
    }

    public function test_decodes_base64_to_string(): void
    {
        $encoded = 'SGVsbG8gV29ybGQ=';
        $decoded = base64_decode($encoded);

        $this->assertEquals('Hello World', $decoded);
    }

    public function test_encode_decode_roundtrip(): void
    {
        $original = 'Test string with special chars: @#$%^&*()';
        $encoded = base64_encode($original);
        $decoded = base64_decode($encoded);

        $this->assertEquals($original, $decoded);
    }

    public function test_handles_empty_string(): void
    {
        $encoded = base64_encode('');
        $this->assertEquals('', $encoded);

        $decoded = base64_decode('');
        $this->assertEquals('', $decoded);
    }

    public function test_handles_unicode_characters(): void
    {
        $input = 'Hello 世界 🌍';
        $encoded = base64_encode($input);
        $decoded = base64_decode($encoded);

        $this->assertEquals($input, $decoded);
    }

    public function test_handles_json_content(): void
    {
        $json = '{"name":"John","age":30}';
        $encoded = base64_encode($json);
        $decoded = base64_decode($encoded);

        $this->assertEquals($json, $decoded);
        $this->assertJson($decoded);
    }

    public function test_handles_long_content(): void
    {
        $longString = str_repeat('Lorem ipsum dolor sit amet. ', 100);
        $encoded = base64_encode($longString);
        $decoded = base64_decode($encoded);

        $this->assertEquals($longString, $decoded);
    }
}

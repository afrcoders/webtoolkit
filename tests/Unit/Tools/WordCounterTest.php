<?php

namespace Tests\Unit\Tools;

use Tests\TestCase;

class WordCounterTest extends TestCase
{
    public function test_counts_words_correctly(): void
    {
        $text = 'Hello world this is a test';
        $count = str_word_count($text);

        $this->assertEquals(6, $count);
    }

    public function test_counts_characters_correctly(): void
    {
        $text = 'Hello World';
        $count = strlen($text);

        $this->assertEquals(11, $count);
    }

    public function test_counts_characters_without_spaces(): void
    {
        $text = 'Hello World';
        $countWithoutSpaces = strlen($text) - substr_count($text, ' ');

        $this->assertEquals(10, $countWithoutSpaces);
    }

    public function test_counts_sentences_correctly(): void
    {
        $text = 'First sentence. Second sentence. Third sentence.';
        $sentences = count(explode('.', rtrim($text, '.')));

        $this->assertEquals(3, $sentences);
    }

    public function test_handles_empty_string(): void
    {
        $text = '';
        $count = str_word_count($text);

        $this->assertEquals(0, $count);
    }

    public function test_handles_single_word(): void
    {
        $text = 'Word';
        $count = str_word_count($text);

        $this->assertEquals(1, $count);
    }

    public function test_handles_text_with_punctuation(): void
    {
        $text = "Hello, world! How are you?";
        $count = str_word_count($text);

        $this->assertEquals(5, $count);
    }

    public function test_handles_multiline_text(): void
    {
        $text = "Line one\nLine two\nLine three";
        $count = str_word_count($text);

        $this->assertEquals(6, $count);
    }
}

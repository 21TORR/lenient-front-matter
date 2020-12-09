<?php declare(strict_types=1);

namespace Tests\Torr\LenientFrontMatter\Parser;

use PHPUnit\Framework\TestCase;
use Torr\LenientFrontMatter\Exception\InvalidSeparatorException;
use Torr\LenientFrontMatter\Parser\LenientFrontMatterParser;

final class LenientFrontMatterParserTest extends TestCase
{
	/**
	 */
	public function provideParsing () : iterable
	{
		yield "no front matter" => [
			<<<'TEXT'
test
TEXT,
			"test",
			[],
		];

		yield "simple case" => [
			<<<'TEXT'
Key: Value
---
test
TEXT,
			"test",
			[
				"Key" => "Value",
			],
		];

		yield "trimming of content" => [
			<<<'TEXT'
Key: Value    
---
  test
TEXT,
			"test",
			[
				"Key" => "Value",
			],
		];

		yield "allow more separator dashes by default" => [
			<<<'TEXT'
Key: Value    
-------------------
  test
TEXT,
			"test",
			[
				"Key" => "Value",
			],
		];

		yield "various key value cases" => [
			<<<'TEXT'
   Key:      Value    
Key2:Value2
Key3:   Value3
invalid
Key: Overwritten
Key4:
Key5: Colons: are: allowed.
---
  test
TEXT,
			"test",
			[
				"Key" => "Overwritten",
				"Key2" => "Value2",
				"Key3" => "Value3",
				"Key4" => "",
				"Key5" => "Colons: are: allowed.",
			],
		];
	}

	/**
	 * @dataProvider provideParsing
	 */
	public function testParsing (string $input, string $expectedContent, array $expectedFrontMatter) : void
	{
		$parser = new LenientFrontMatterParser();
		$result = $parser->parse($input);

		self::assertSame($expectedContent, $result->getContent());
		self::assertEqualsCanonicalizing($expectedFrontMatter, $result->getFrontMatter());
	}

	/**
	 *
	 */
	public function testCustomSeparator () : void
	{
		$parser = new LenientFrontMatterParser("###+");
		$result = $parser->parse(
			<<<'TEXT'
o: hai
###
text
TEXT
);

		self::assertSame("text", $result->getContent());
		self::assertEqualsCanonicalizing([
			"o" => "hai",
		], $result->getFrontMatter());
	}

	/**
	 *
	 */
	public function testInvalidSeparatorTilde () : void
	{
		$this->expectException(InvalidSeparatorException::class);
		new LenientFrontMatterParser("~");
	}

	/**
	 *
	 */
	public function testInvalidSeparatorOther () : void
	{
		$this->expectException(InvalidSeparatorException::class);
		$parser = new LenientFrontMatterParser("[");
		$parser->parse("test");
	}
}

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
				"key" => "Value",
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
				"key" => "Value",
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
				"key" => "Value",
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
				"key" => "Overwritten",
				"key2" => "Value2",
				"key3" => "Value3",
				"key4" => "",
				"key5" => "Colons: are: allowed.",
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
		self::assertEquals($expectedFrontMatter, $result->getFrontMatter());
	}

	/**
	 *
	 */
	public function testCustomSeparator () : void
	{
		$content = <<<'CONTENT'
o: hai
###
text
CONTENT;

		$parser = new LenientFrontMatterParser([], "###+");
		$result = $parser->parse($content);

		self::assertSame("text", $result->getContent());
		self::assertEquals([
			"o" => "hai",
		], $result->getFrontMatter());
	}

	/**
	 *
	 */
	public function testInvalidSeparatorTilde () : void
	{
		$this->expectException(InvalidSeparatorException::class);
		new LenientFrontMatterParser([], "~");
	}

	/**
	 *
	 */
	public function testInvalidSeparatorOther () : void
	{
		$this->expectException(InvalidSeparatorException::class);
		$parser = new LenientFrontMatterParser([], "[");
		$parser->parse("test");
	}

	/**
	 */
	public function provideKeyNormalization () : iterable
	{
		yield ["O"];
		yield ["TEST"];
		yield ["test"];
		yield ["UnReGuLaR"];
		yield ["UNREGULAR"];
		yield ["unregular"];
	}


	/**
	 * @dataProvider provideKeyNormalization
	 */
	public function testKeyNormalization (string $key) : void
	{
		$content = <<<'CONTENT'
o: hai
TEST: hai
UnReGuLaR: hai
---
text
CONTENT;

		$parser = new LenientFrontMatterParser();
		$result = $parser->parse($content);

		self::assertSame("hai", $result->getFrontMatterValue($key));
	}


	/**
	 */
	public function testKeyMapping () : void
	{
		$content = <<<'CONTENT'
o: hai
TEST: hai
UnReGuLaR: hai
---
text
CONTENT;

		$parser = new LenientFrontMatterParser([
			"o" => "mapped-o",
			"test" => "mapped-test",
			"unregular" => "mapped-unregular",
		]);
		$result = $parser->parse($content);

		self::assertEquals([
			"mapped-o" => "hai",
			"mapped-test" => "hai",
			"mapped-unregular" => "hai",
		], $result->getFrontMatter());
	}
}

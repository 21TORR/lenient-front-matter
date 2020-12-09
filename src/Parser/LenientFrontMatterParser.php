<?php declare(strict_types=1);

namespace Torr\LenientFrontMatter\Parser;

use Torr\LenientFrontMatter\Data\ParsedContent;
use Torr\LenientFrontMatter\Exception\InvalidSeparatorException;

final class LenientFrontMatterParser
{
	/**
	 * A partial regex expression that matches the separator char.
	 * This separator needs to stand in its own line.
	 *
	 * @var string
	 */
	private $separator;

	/**
	 * Allows to remap keys.
	 * The key is the value as written by the users, and the value is the transformed key.
	 *
	 * You must use the normalized key here.
	 *
	 * @var array<string, string>
	 */
	private $keyMapping;


	/**
	 */
	public function __construct (
		array $keyMapping = [],
		string $separator = "---+"
	)
	{
		$this->separator = $separator;

		if (false !== \strpos($separator, "~"))
		{
			throw new InvalidSeparatorException(\sprintf(
				"The separator may not use the tilde '~' symbol, given: '%s'",
				$separator
			));
		}
		$this->keyMapping = $keyMapping;
	}

	/**
	 */
	public function parse (string $value) : ParsedContent
	{
		$value = \trim($value);

		$matches = @\preg_split(
			"~^{$this->separator}\\s*$~m",
			$value,
			2
		);

		if (\PREG_NO_ERROR !== \preg_last_error())
		{
			throw new InvalidSeparatorException(\sprintf(
				"Parsing front matter failed due to preg error: %s",
				\preg_last_error_msg()
			));
		}

		if (2 !== \count($matches))
		{
			return new ParsedContent($matches[0] ?? $value, []);
		}

		return new ParsedContent(
			\trim($matches[1]),
			$this->parseFrontMatter($matches[0])
		);
	}


	/**
	 */
	private function parseFrontMatter (string $text) : array
	{
		$lines = \preg_split('~(\\r?\\n)+~', \trim($text));
		$frontMatter = [];

		foreach ($lines as $line)
		{
			$split = \explode(":", \trim($line), 2);

			// skip, as we did not find a colon
			if (2 !== \count($split))
			{
				continue;
			}

			$key = self::normalizeFrontMatterKey($split[0]);
			$key = $this->keyMapping[$key] ?? $key;
			$frontMatter[$key] = \trim($split[1]);
		}

		return $frontMatter;
	}


	/**
	 * Normalizes the given front matter key
	 */
	public static function normalizeFrontMatterKey (string $key) : string
	{
		return \mb_strtolower(\trim($key));
	}
}

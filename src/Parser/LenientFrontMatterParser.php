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

	public function __construct (string $separator = "---+")
	{
		$this->separator = $separator;

		if (false !== \strpos($separator, "~"))
		{
			throw new InvalidSeparatorException(\sprintf(
				"The separator may not use the tilde '~' symbol, given: '%s'",
				$separator
			));
		}
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

			$frontMatter[\trim($split[0])] = \trim($split[1]);
		}

		return $frontMatter;
	}
}

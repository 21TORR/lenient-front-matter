<?php declare(strict_types=1);

namespace Torr\LenientFrontMatter\Data;

use Torr\LenientFrontMatter\Parser\LenientFrontMatterParser;

final class ParsedContent
{
	/** @var string */
	private $content;
	/** @var array */
	private $frontMatter;

	/**
	 */
	public function __construct (string $content, array $frontMatter)
	{
		$this->content = $content;
		$this->frontMatter = $frontMatter;
	}

	/**
	 */
	public function getContent () : string
	{
		return $this->content;
	}

	/**
	 */
	public function getFrontMatter () : array
	{
		return $this->frontMatter;
	}

	/**
	 * @return mixed
	 */
	public function getFrontMatterValue (string $key)
	{
		$key = LenientFrontMatterParser::normalizeFrontMatterKey($key);
		return $this->frontMatter[$key] ?? null;
	}
}

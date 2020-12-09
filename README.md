Lenient Front Matter
====================

Front matter parser, that doesn't use YAML for parsing, but is a really lenient plain text parser.

This is intended for use, where the customer is entering simple key-value entries. 


Installation
------------

Install it via composer:

```bash
composer install 21torr/lenient-front-matter
```


Usage
-----

Create a new parser and parse the content:

```php
use Torr\LenientFrontMatter\Parser\LenientFrontMatterParser;

$parser = new LenientFrontMatterParser();
$content = $parser->parse("...");

// returns the plain text content
$textContent = $content->getContent();

// returns the key-value map of front matter
$frontMatter = $content->getFrontMatter();

// returns a single front matter value
$singleFrontMatterValue = $content->getFrontMatterValue("test");
```

### Normalization

The keys of the front matter are normalized, so basically trimmed and transformed to lower case.
So if you work with them, you always need to use the normalized version.


### Key Mappings

You can transform keys when they are parsed, eg. you can add aliases for keys.

```php
use Torr\LenientFrontMatter\Parser\LenientFrontMatterParser;

// The separator must be a partial regex expression
$parser = new LenientFrontMatterParser([
    "meeting-number" => "meeting number",
]);

$result = $parser->parse(<<<'VALUE'
Meeting-Number: 123
---
Text
VALUE
);

assert("123" === $result->getFrontMatterValue("meeting number"));
```


### Separator

By default, the separator is a line of dashes (at least three: `---`).
You can change it by passing the new separator in the constructor:

```php
use Torr\LenientFrontMatter\Parser\LenientFrontMatterParser;

// The separator must be a partial regex expression
$parser = new LenientFrontMatterParser([], "___+");
```

> You can't use the `~` symbol in your separator regex, as it is used internally as regex delimiter.

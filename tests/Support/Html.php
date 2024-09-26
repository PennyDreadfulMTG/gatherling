<?php

declare(strict_types=1);

namespace Gatherling\Tests\Support;

use Gatherling\Exceptions\ElementNotFoundException;
use Symfony\Component\DomCrawler\Crawler;

class Html
{
    private Crawler $crawler;

    public function __construct(string|Crawler $input)
    {
        $this->crawler = $input instanceof Crawler ? $input : new Crawler($input);
    }

    public function __get(string $element): Html
    {
        $node = $this->crawler->filter($element);
        if ($node->count() === 0) {
            throw new ElementNotFoundException("Element '{$element}' not found.");
        }
        return new self($node);
    }

    public function text(): string
    {
        return $this->crawler->text();
    }

    public function attr(string $attribute): ?string
    {
        return $this->crawler->attr($attribute);
    }
}

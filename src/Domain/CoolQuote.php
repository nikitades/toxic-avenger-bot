<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\Domain;

class CoolQuote
{
    /**
     * @param array<string> $tags
     */
    public function __construct(
        public string $author,
        public string $quote,
        public array $tags,
    ) {
    }
}

<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\Infrastructure\Yandex\Lemmatizer;

use JMS\Serializer\Annotation\Type;

class LemmatizingResult
{
    /**
     * @param array<LemmatizingAnalysis> $analysis
     */
    public function __construct(
        #[Type('array<' . LemmatizingAnalysis::class . '>')]
        private array $analysis,

        private string $text,
    ) {
    }

    public function isEmpty(): bool
    {
        return [] === $this->analysis;
    }

    public function getFirstResultOrNull(): LemmatizingAnalysis | null
    {
        return array_shift($this->analysis);
    }

    public function getFirstResultOrFallback(): string
    {
        return array_shift($this->analysis)?->getLex() ?? $this->text;
    }
}

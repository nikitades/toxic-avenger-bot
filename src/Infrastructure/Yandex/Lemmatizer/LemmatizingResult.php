<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\Infrastructure\Yandex\Lemmatizer;

class LemmatizingResult
{
    /** @var array<LemmatizingAnalysis> */
    private array $analysis;

    /**
     * @param array<LemmatizingAnalysis> $analysis
     */
    public function __construct(
        array $analysis,
        private string $text,
    ) {
        $this->analysis = $analysis;
    }

    public function isEmpty(): bool
    {
        return [] === $this->analysis;
    }

    public function getFirstResultOrNull(): LemmatizingAnalysis | null
    {
        return $this->analysis[0] ?? null;
    }

    public function getFirstResultOrFallback(): string
    {
        return array_shift($this->analysis)?->getLex() ?? $this->text;
    }

    public function getText(): string
    {
        return $this->text;
    }
}

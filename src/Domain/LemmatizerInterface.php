<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\Domain;

interface LemmatizerInterface
{
    /**
     * @return array<string> lemmas
     */
    public function lemmatizePhraseWithOnlyMeaningful(string $phrase): array;
}
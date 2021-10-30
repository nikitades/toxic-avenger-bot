<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\Domain;

interface LemmatizerInterface
{
    /**
     * @return array<string> lemmas
     */
    public function lemmatizePhraseWithOnlyMeaningful(string $phrase): array;

    /**
     * @param array<string> $sourceWords
     * @return array<string>
     */
    public function findObsceneLemmas(array $sourceWords): array;
}

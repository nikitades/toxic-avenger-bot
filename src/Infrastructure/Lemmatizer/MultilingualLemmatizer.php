<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\Infrastructure\Lemmatizer;

use LanguageDetection\Language;
use Nikitades\ToxicAvenger\Domain\LemmatizerInterface;

class MultilingualLemmatizer implements LemmatizerInterface
{
    /**
     * @param iterable<LanguageBoundLemmatizerInterface> $lemmatizers
     */
    public function __construct(
        private iterable $lemmatizers,
        private Language $languageDetector,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function findObsceneLemmas(array $sourceWords): array
    {
        $languages = iterator_to_array($this->languageDetector->detect(implode(', ', $sourceWords)));

        if ([] === $languages) {
            return [];
        }

        return $this->pickLemmatizer(array_keys($languages)[0])->findObsceneLemmas($sourceWords);
    }

    /**
     * TODO: handle multi-lingual phrases.
     *
     * {@inheritDoc}
     */
    public function lemmatizePhraseWithOnlyMeaningful(string $phrase): array
    {
        $languages = iterator_to_array($this->languageDetector->detect($phrase));

        if ([] === $languages) {
            return [];
        }

        return $this->pickLemmatizer(array_keys($languages)[0])->lemmatizePhraseWithOnlyMeaningful($phrase);
    }

    private function pickLemmatizer(string $language): LanguageBoundLemmatizerInterface
    {
        $lemmatizers = [];
        foreach ($this->lemmatizers as $lemmatizer) {
            $lemmatizers[] = $lemmatizer;
        }

        $lemmatizerMap = array_combine(
            array_map(
                fn (LanguageBoundLemmatizerInterface $lemmatizer): string => $lemmatizer->getLanguage(),
                $lemmatizers,
            ),
            $lemmatizers,
        );

        return $lemmatizerMap[$language] ?? $lemmatizerMap['ru']; //fallback lemmatizer
    }
}

<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\Infrastructure\Yandex\Lemmatizer;

use LogicException;
use Nikitades\ToxicAvenger\Infrastructure\Lemmatizer\LanguageBoundLemmatizerInterface;

class YandexLemmatizer implements LanguageBoundLemmatizerInterface
{
    public function __construct(
        private LemmatizerProcess $lemmatizerProcess,
    ) {
    }

    public function getLanguage(): string
    {
        return 'ru';
    }

    /**
     * {@inheritDoc}
     */
    public function findObsceneLemmas(array $sourceWords): array
    {
        $output = [];
        foreach ($sourceWords as $sourceWord) {
            $onePhraseResults = $this->lemmatizerProcess->lemmatizePhraseWithWeight($sourceWord);

            $successfulLemmatizations = array_filter(
                $onePhraseResults,
                fn (LemmatizingResult $result): bool => null !== $result->getFirstResultOrNull(),
            );

            $obsceneLemmas = array_filter(
                $successfulLemmatizations,
                fn (LemmatizingResult $result): bool => $result->getFirstResultOrNull()?->isObscene() ?? false,
            );

            if ([] === $obsceneLemmas) {
                return [];
            }

            $output[] = $obsceneLemmas[0]->getFirstResultOrFallback();
        }

        return $output;
    }

    /**
     * @return array<string>
     * @throws LogicException
     */
    public function lemmatizePhraseWithOnlyMeaningful(string $phrase): array
    {
        $allPhrases = $this->lemmatizerProcess->lemmatizePhraseWithWeight($phrase);

        $nonLemmatizablePhrases = array_filter(
            $allPhrases,
            fn (LemmatizingResult $result): bool => null === $result->getFirstResultOrNull(),
        );

        $lowercasedNonLemmatizablePhrases = array_map(
            fn (LemmatizingResult $result): LemmatizingResult => new LemmatizingResult([], mb_strtolower($result->getText())),
            $nonLemmatizablePhrases,
        );

        $lemmatizablePhrases = array_filter(
            $allPhrases,
            fn (LemmatizingResult $result): bool => null !== $result->getFirstResultOrNull(),
        );

        $meaningfulData = array_filter(
            $lemmatizablePhrases,
            fn (LemmatizingResult $result): bool => LemmatizingAnalysis::checkIfMeaningful($result->getFirstResultOrNull() ?? throw new LogicException('This is supposed to be meaningful')),
        );

        return array_map(
            fn (LemmatizingResult $result): string => $result->getFirstResultOrFallback(),
            array_merge($lowercasedNonLemmatizablePhrases, $meaningfulData),
        );
    }
}

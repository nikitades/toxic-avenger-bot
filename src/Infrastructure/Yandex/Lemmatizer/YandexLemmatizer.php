<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\Infrastructure\Yandex\Lemmatizer;

use LogicException;
use Nikitades\ToxicAvenger\Infrastructure\Lemmatizer\LanguageBoundLemmatizerInterface;

class YandexLemmatizer implements LanguageBoundLemmatizerInterface
{
    public function __construct(
        private LemmatizerProcess $lemmatizerFactory,
    ) {
    }

    public function getLanguage(): string
    {
        return 'ru';
    }

    /**
     * @return array<string>
     * @throws LogicException
     */
    public function lemmatizePhraseWithOnlyMeaningful(string $phrase): array
    {
        $allPhrases = $this->lemmatizerFactory->lemmatizePhraseWithWeight($phrase);

        $nonLemmatizablePhrases = array_filter(
            $allPhrases,
            fn (LemmatizingResult $result): bool => null === $result->getFirstResultOrNull(),
        );

        $lowercasedNonLemmatizablePhrases = array_map(
            fn (LemmatizingResult $result): LemmatizingResult => new LemmatizingResult([], mb_strtolower($result->getText())),
            $nonLemmatizablePhrases
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

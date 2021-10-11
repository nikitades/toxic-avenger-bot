<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\Infrastructure\Yandex\Lemmatizer;

use LogicException;
use Nikitades\ToxicAvenger\Domain\LemmatizerInterface;

class YandexLemmatizer implements LemmatizerInterface
{
    public function __construct(
        private LemmatizerSystemAwareFactory $lemmatizerFactory,
    ) {
    }

    /**
     * @return array<string>
     * @throws LogicException
     */
    public function lemmatizePhraseWithOnlyMeaningful(string $phrase): array
    {
        $allPhrases = $this->lemmatizerFactory->getInstance()->lemmatizePhraseWithWeight($phrase);

        $nonLemmatizablePhrases = array_filter(
            $allPhrases,
            fn (LemmatizingResult $result): bool => null === $result->getFirstResultOrNull(),
        );

        $lemmatizablePhrases = array_diff(
            $allPhrases,
            $nonLemmatizablePhrases
        );

        $meaningfulData = array_filter(
            $lemmatizablePhrases,
            fn (LemmatizingResult $result): bool => LemmatizingAnalysis::checkIfMeaningful($result->getFirstResultOrNull() ?? throw new LogicException('This is supposed to be meaningful')),
        );

        return array_map(
            fn (LemmatizingResult $result): string => $result->getFirstResultOrFallback(),
            array_merge($nonLemmatizablePhrases, $meaningfulData),
        );
    }
}

<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\Infrastructure\Lemmatizer;

use Nikitades\ToxicAvenger\Domain\LemmatizerInterface;

interface LanguageBoundLemmatizerInterface extends LemmatizerInterface
{
    public function getLanguage(): string;
}

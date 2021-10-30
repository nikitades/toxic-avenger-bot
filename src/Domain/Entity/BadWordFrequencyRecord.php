<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\Domain\Entity;

class BadWordFrequencyRecord
{
    public function __construct(
        public string $word,
        public int $usagesCount,
    ) {
    }
}

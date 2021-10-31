<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\Domain;

class ToxicityMeasure
{
    public function __construct(
        public int $usagesCount,
        public string $title,
    ) {
    }
}

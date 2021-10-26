<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\Domain;

use Safe\DateTimeImmutable;

class SystemClock
{
    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('now');
    }
}

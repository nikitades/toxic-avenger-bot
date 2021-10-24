<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\Domain;

use Symfony\Component\Uid\Uuid;

class UuidProvider
{
    public function provide(): Uuid
    {
        return Uuid::v4();
    }
}

<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\Domain;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class UuidProvider
{
    public function provide(): UuidInterface
    {
        return Uuid::uuid4();
    }
}

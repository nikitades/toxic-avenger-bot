<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\App;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class UuidProvider
{
    public function provide(): UuidInterface
    {
        return Uuid::uuid4();
    }
}

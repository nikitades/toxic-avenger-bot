<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\App;

use Ramsey\Uuid\Uuid;

class UuidProvider
{
    public function provide(): Uuid
    {
        return Uuid::uuid4();
    }
}

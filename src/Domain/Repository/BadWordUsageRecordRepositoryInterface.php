<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\Domain\Repository;

use Nikitades\ToxicAvenger\Domain\Entity\BadWordUsageRecord;

interface BadWordUsageRecordRepositoryInterface
{
    public function save(BadWordUsageRecord $record): void;
}

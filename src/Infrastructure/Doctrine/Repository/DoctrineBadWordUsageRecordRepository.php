<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\Infrastructure\Doctrine\Repository;

use Nikitades\ToxicAvenger\Domain\Entity\BadWordUsageRecord;
use Nikitades\ToxicAvenger\Domain\Repository\BadWordUsageRecordRepositoryInterface;

class DoctrineBadWordUsageRecordRepository implements BadWordUsageRecordRepositoryInterface
{
    public function save(BadWordUsageRecord $record): void
    {
        echo 'lala';
    }
}

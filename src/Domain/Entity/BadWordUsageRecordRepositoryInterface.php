<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\Domain\Entity;

interface BadWordUsageRecordRepositoryInterface
{
    public function save(BadWordUsageRecord $record): void;
}

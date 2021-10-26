<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\App;

use Nikitades\ToxicAvenger\Domain\Repository\BadWordLibraryRecordRepositoryInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class CommandDependencies
{
    public function __construct(
        public MessageBusInterface $messageBusInterface,
        public BadWordLibraryRecordRepositoryInterface $badWordLibraryRecordRepository,
    ) {
    }
}

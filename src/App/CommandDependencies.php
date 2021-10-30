<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\App;

use Nikitades\ToxicAvenger\Domain\BadWordsLibrary;
use Nikitades\ToxicAvenger\Domain\LemmatizerInterface;
use Nikitades\ToxicAvenger\Domain\ObsceneWordEscaper;
use Nikitades\ToxicAvenger\Domain\Repository\BadWordLibraryRecordRepositoryInterface;
use Nikitades\ToxicAvenger\Domain\Repository\BadWordUsageRecordRepositoryInterface;
use Nikitades\ToxicAvenger\Domain\ToxicityMeasurer;
use Symfony\Component\Messenger\MessageBusInterface;

class CommandDependencies
{
    public function __construct(
        public MessageBusInterface $messageBusInterface,
        public BadWordLibraryRecordRepositoryInterface $badWordLibraryRecordRepository,
        public BadWordUsageRecordRepositoryInterface $badWordUsageRecordRepository,
        public BadWordsLibrary $badWordsLibrary,
        public LemmatizerInterface $lemmatizer,
        public ToxicityMeasurer $toxicityMeasurer,
        public ObsceneWordEscaper $obsceneWordEscaper,
    ) {
    }
}

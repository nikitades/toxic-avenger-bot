<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\Domain\Command\NewMessage;

use Nikitades\ToxicAvenger\Domain\Repository\BadWordUsageRecordRepositoryInterface;
use Nikitades\ToxicAvenger\Domain\LemmatizerInterface;

class NewMessageCommandHandler
{
    public function __construct(
        private LemmatizerInterface $lemmatizer,
        private BadWordUsageRecordRepositoryInterface $badWordUsageRecordRepository
    ) {
    }

    public function __invoke(NewMessageCommand $command): void
    {
        $lemmas = $this->lemmatizer->lemmatizePhraseWithOnlyMeaningful($command->text);

        /**
         * 1. принять сообщение
         * 2. через лемматайзер получить анализ введенных слов
         * 3. выкинуть предлоги и частицы (https://yandex.ru/dev/mystem/doc/grammemes-values.html)
         * 4. добавить +1 использование каждой леммы пользователю.
         */
        $a = 1;
    }
}

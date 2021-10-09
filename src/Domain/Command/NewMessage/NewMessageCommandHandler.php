<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\Domain\Command\NewMessage;

use Nikitades\ToxicAvenger\Domain\Entity\BadWordUsageRecordRepositoryInterface;

class NewMessageCommandHandler
{
    public function __construct(
        private BadWordUsageRecordRepositoryInterface $badWordUsageRecordRepository
    ) {
    }

    public function __invoke(NewMessageCommand $command): void
    {
        /**
         * 1. принять сообщение
         * 2. через лемматайзер получить анализ введенных слов
         * 3. выкинуть предлоги и частицы (https://yandex.ru/dev/mystem/doc/grammemes-values.html)
         * 4. добавить +1 использование каждой леммы пользователю 
         */
    }
}

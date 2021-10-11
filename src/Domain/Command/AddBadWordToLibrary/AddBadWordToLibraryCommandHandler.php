<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\Domain\Command\AddBadWordToLibrary;

class AddBadWordToLibraryCommandHandler
{
    public function __invoke(AddBadWordToLibraryCommand $command): void
    {
        /**
         * 1. лемматизировать
         * 2. проверить на дубли
         * 3. добавить
         */
    }
}
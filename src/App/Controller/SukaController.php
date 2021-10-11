<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\App\Controller;

use DateTimeImmutable;
use Nikitades\ToxicAvenger\Domain\Command\NewMessage\NewMessageCommand;
use Nikitades\ToxicAvenger\Domain\Command\NewMessage\NewMessageCommandHandler;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SukaController
{
    public function __construct(
        private NewMessageCommandHandler $newMessageCommandHandler,
    ) {
    }

    #[Route(path: '/api/test', methods: ['GET'])]
    public function __invoke(): Response
    {
        $this->newMessageCommandHandler->__invoke(new NewMessageCommand(
            'Во поле берёзка стояла',
            1,
            2,
            new DateTimeImmutable('now'),
        ));

        return new Response('Ok');
    }
}

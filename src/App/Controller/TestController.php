<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TestController
{
    #[Route(path: '/api/test', methods: ['GET'])]
    public function __invoke(): Response
    {
        return new Response('Ok');
    }
}

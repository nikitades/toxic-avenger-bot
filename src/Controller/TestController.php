<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class TestController
{
    #[Route(path: '/api/test', methods: ['GET'])]
    public function __invoke(): Response
    {
        return new Response('Ok');
    }
}
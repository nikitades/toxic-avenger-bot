<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\Infrastructure\Yandex\Lemmatizer;

use JMS\Serializer\SerializerInterface;
use LogicException;

class LemmatizerSystemAwareFactory
{
    public function __construct(
        private SerializerInterface $serializer,
    ) {
    }

    private function osx(): LemmatizerProcess
    {
        return new LemmatizerProcess(
            executable: __DIR__ . '/mystem_osx',
            serializer: $this->serializer,
        );
    }

    private function linux(): LemmatizerProcess
    {
        return new LemmatizerProcess(
            executable: __DIR__ . '/mystem_linux',
            serializer: $this->serializer,
        );
    }

    public function getInstance(): LemmatizerProcess
    {
        return match (PHP_OS_FAMILY) {
            'Linux' => self::linux(),
            'Darwin' => self::osx(),
            default => throw new LogicException('Not implemented yet')
        };
    }
}

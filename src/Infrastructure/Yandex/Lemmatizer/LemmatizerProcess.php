<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\Infrastructure\Yandex\Lemmatizer;

use LogicException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Symfony\Component\Serializer\SerializerInterface;

class LemmatizerProcess
{
    public function __construct(
        private SerializerInterface $serializer,
    ) {
    }

    private static function osx(): string
    {
        return __DIR__ . '/mystem_osx';
    }

    private static function linux(): string
    {
        return __DIR__ . '/mystem_linux';
    }

    public function getExecutable(): string
    {
        return match (PHP_OS_FAMILY) {
            'Linux' => self::linux(),
            'Darwin' => self::osx(),
            default => throw new LogicException('Not implemented yet')
        };
    }

    /**
     * @return array<LemmatizingResult>
     */
    public function lemmatizePhraseWithWeight(string $phrase): array
    {
        $process = new Process([$this->getExecutable(), '-l', '-i', '--format', 'json', '--weight']);

        $process->setInput($phrase);
        $process->start();
        $process->wait();
        $process->stop(0.1, SIGINT);

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $this->serializer->deserialize($process->getOutput(), LemmatizingResult::class . '[]', 'json');
    }
}

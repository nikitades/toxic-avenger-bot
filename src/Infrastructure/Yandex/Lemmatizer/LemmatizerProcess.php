<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\Infrastructure\Yandex\Lemmatizer;

use JMS\Serializer\SerializerInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class LemmatizerProcess
{
    public function __construct(
        private string $executable,
        private SerializerInterface $serializer,
    ) {
    }

    /**
     * @return array<LemmatizingResult>
     */
    public function lemmatizePhraseWithWeight(string $phrase): array
    {
        $process = new Process([$this->executable, '-l', '-i', '--format', 'json', '--weight']);
        $process->setInput($phrase);
        $process->start();
        $process->wait();
        $process->stop(0.1, SIGINT);

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $a = $process->getOutput();

        return $this->serializer->deserialize($process->getOutput(), 'array<' . LemmatizingResult::class . '>', 'json');
    }
}

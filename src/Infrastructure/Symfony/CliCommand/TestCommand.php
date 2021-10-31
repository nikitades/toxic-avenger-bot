<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\Infrastructure\Symfony\CliCommand;

use Nikitades\ToxicAvenger\App\BusAwareTelegram;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Uid\Uuid;

class TestCommand extends Command
{
    public function __construct(
        private BusAwareTelegram $telegram,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('bot:test')
            ->setDescription('Test');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $namespace = Uuid::fromString('915dfade-14ff-4fe4-9c3d-dd223c1ae931');

        $a = 'lalalala';
        $b = 'lalalale';
        $c = 'bebebebe';

        for ($i = 0; $i < 5; ++$i) {
            $io->text((string) Uuid::v5($namespace, $a));
        }

        for ($i = 0; $i < 5; ++$i) {
            $io->text((string) Uuid::v5($namespace, $b));
        }

        for ($i = 0; $i < 5; ++$i) {
            $io->text((string) Uuid::v5($namespace, $c));
        }

        return Command::SUCCESS;
    }
}

<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\Infrastructure\Symfony\CliCommand;

use Nikitades\ToxicAvenger\App\BusAwareTelegram;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

class SetWebhookCommand extends Command
{
    public function __construct(
        private BusAwareTelegram $telegram,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('bot:webhook:set')
            ->setDescription('Set webhook')
            ->addArgument(
                'domain',
                InputArgument::REQUIRED,
                'Your bot\'s site with API',
            );
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        try {
            if (is_array($webhook = $input->getArgument('domain'))) {
                $webhook = array_shift($webhook);
            }
            $webhook = (string) $webhook;
            $webhook = sprintf('%s/api/webhook', $webhook);
            $this->telegram->setWebhook($webhook);
            $io->info(sprintf('Webhook successfully set to: %s', $webhook));
        } catch (Throwable $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}

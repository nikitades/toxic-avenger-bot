<?php

namespace App\Command;

use Closure;
use Longman\TelegramBot\Telegram;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class SetWebhookCommand extends Command
{
    protected static $defaultName = 'webhook:set';

    private string $botUserName;
    private string $botApiKey;

    public function __construct(ParameterBagInterface $params)
    {
        $this->botApiKey = $params->get('bot.api_key');
        $this->botUserName = $params->get('bot.user_name');
        parent::__construct(null);
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Set the webhook to receive updates')
            ->addArgument('url', InputArgument::REQUIRED, 'Webhook URL');;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $url = $input->getArgument('url');
        if (!is_string($url)) {
            $io->warning("Wrong url given");
            return 1;
        }

        $telegram = new Telegram($this->botApiKey, $this->botUserName);
        $result = $telegram->setWebhook((string) $url);
        if ($result->isOk()) {
            $io->success("Webhook {$url} set successfully");
            return 0;
        }
        $io->error("Something was wrong");
        $io->write($result->getResult());


        return 0;
    }
}

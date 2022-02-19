<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\App\Telegram\Command;

use DateTimeInterface;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;
use Nikitades\ToxicAvenger\App\Telegram\BusAwareSystemCommand;
use Nikitades\ToxicAvenger\Domain\Command\NewMessage\NewMessageCommand;
use DateTimeImmutable;
use Nikitades\ToxicAvenger\Domain\Entity\BadWordFrequencyRecord;
use Nikitades\ToxicAvenger\Domain\Entity\BadWordLibraryRecord;
use Nikitades\ToxicAvenger\Domain\ToxicityMeasure;

class GenericMessageCommand extends BusAwareSystemCommand
{
    public function execute(): ServerResponse
    {
        $this->commandDependencies->messageBusInterface->dispatch(
            new NewMessageCommand(
                text: $this->getMessage()->getText() ?? '',
                userId: $this->getMessage()->getFrom()->getId(),
                userName: $this->getMessage()->getFrom()->getUsername() ?? '<unknown>',
                chatId: $this->getMessage()->getChat()->getId(),
                messageId: $this->getMessage()->getMessageId(),
                sentAt: new DateTimeImmutable(date(DateTimeInterface::ATOM, $this->getMessage()->getDate())),
            ),
        );

        $lemmas = $this->commandDependencies->lemmatizer->lemmatizePhraseWithOnlyMeaningful(
            phrase: $this->getMessage()->getText(true) ?? '',
        );

        $badWordsFromLibrary = $this->commandDependencies->badWordsLibrary->getForChat(
            telegramChatId: $this->getMessage()->getChat()->getId(),
            lemmas: $lemmas,
        );

        $badWordsFromLibrary = array_filter(
            $badWordsFromLibrary,
            fn (BadWordLibraryRecord $bwlr): bool => $bwlr->active,
        );

        $usedBadWords = $this->commandDependencies->badWordUsageRecordRepository->getBadWordsUsageFrequencyForList(
            userId: $this->getMessage()->getFrom()->getId(),
            chatId: $this->getMessage()->getChat()->getId(),
            bwlr: $badWordsFromLibrary,
        );

        $acquiredDegrees = array_combine(
            array_map(fn (BadWordFrequencyRecord $bwfr): string => $bwfr->word, $usedBadWords),
            array_map(fn (BadWordFrequencyRecord $bwfr): ToxicityMeasure | null => $this->commandDependencies->toxicityMeasurer->measureToxicityLevel($bwfr->usagesCount), $usedBadWords),
        );

        /** @var array<string,ToxicityMeasure> $acquiredDegrees */
        $acquiredDegrees = array_filter($acquiredDegrees);

        if ([] !== $acquiredDegrees) {
            $output = [];
            foreach ($acquiredDegrees as $word => $toxicityMeasure) {
                $output[] = sprintf(
                    '%s for *%s* usages of *%s*',
                    $toxicityMeasure->title,
                    $toxicityMeasure->usagesCount,
                    $this->commandDependencies->obsceneWordEscaper->escape($word),
                );
            }

            return Request::sendMessage([
                'chat_id' => $this->getMessage()->getChat()->getId(),
                'text' => 'User *@' . $this->getMessage()->getFrom()->getUsername() . "* is: \n" . implode(",\n", $output),
                'parse_mode' => 'markdown',
            ]);
        }

        return Request::emptyResponse();
    }

}

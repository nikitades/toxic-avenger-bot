<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\Infrastructure\Doctrine\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211102180746 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bad_word_library_record ALTER telegram_chat_id TYPE TEXT');
        $this->addSql('ALTER TABLE bad_word_library_record ALTER telegram_chat_id DROP DEFAULT');
        $this->addSql('ALTER TABLE bad_word_library_record ALTER telegram_message_id TYPE TEXT');
        $this->addSql('ALTER TABLE bad_word_library_record ALTER telegram_message_id DROP DEFAULT');
        $this->addSql('ALTER TABLE bad_word_usage_record ALTER telegram_message_id TYPE TEXT');
        $this->addSql('ALTER TABLE bad_word_usage_record ALTER telegram_message_id DROP DEFAULT');
        $this->addSql('ALTER TABLE bad_word_usage_record ALTER telegram_chat_id TYPE TEXT');
        $this->addSql('ALTER TABLE bad_word_usage_record ALTER telegram_chat_id DROP DEFAULT');
        $this->addSql('ALTER TABLE bot_user ALTER telegram_id TYPE TEXT');
        $this->addSql('ALTER TABLE bot_user ALTER telegram_id DROP DEFAULT');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE bot_user ALTER telegram_id TYPE INT');
        $this->addSql('ALTER TABLE bot_user ALTER telegram_id DROP DEFAULT');
        $this->addSql('ALTER TABLE bad_word_usage_record ALTER telegram_message_id TYPE INT');
        $this->addSql('ALTER TABLE bad_word_usage_record ALTER telegram_message_id DROP DEFAULT');
        $this->addSql('ALTER TABLE bad_word_usage_record ALTER telegram_chat_id TYPE INT');
        $this->addSql('ALTER TABLE bad_word_usage_record ALTER telegram_chat_id DROP DEFAULT');
        $this->addSql('ALTER TABLE bad_word_library_record ALTER telegram_chat_id TYPE INT');
        $this->addSql('ALTER TABLE bad_word_library_record ALTER telegram_chat_id DROP DEFAULT');
        $this->addSql('ALTER TABLE bad_word_library_record ALTER telegram_message_id TYPE INT');
        $this->addSql('ALTER TABLE bad_word_library_record ALTER telegram_message_id DROP DEFAULT');
    }
}

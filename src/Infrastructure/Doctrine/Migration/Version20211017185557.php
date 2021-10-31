<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211017185557 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE bad_word_library_record (id UUID NOT NULL, telegram_chat_id INT DEFAULT NULL, text TEXT NOT NULL, active BOOLEAN NOT NULL, added_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5051A0C241DC10D33B8BA7C7 ON bad_word_library_record (telegram_chat_id, text)');
        $this->addSql('COMMENT ON COLUMN bad_word_library_record.id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE bad_word_usage_record (id UUID NOT NULL, user_id UUID DEFAULT NULL, telegram_message_id INT NOT NULL, telegram_chat_id INT NOT NULL, library_word_id UUID NOT NULL, sent_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_308DE102A76ED395 ON bad_word_usage_record (user_id)');
        $this->addSql('CREATE INDEX idx_user_library_word_chat ON bad_word_usage_record (user_id, library_word_id, telegram_chat_id)');
        $this->addSql('COMMENT ON COLUMN bad_word_usage_record.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN bad_word_usage_record.user_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN bad_word_usage_record.library_word_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE bot_user (id UUID NOT NULL, telegram_id INT NOT NULL, name TEXT NOT NULL, added_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C355A3BCC0B3066 ON bot_user (telegram_id)');
        $this->addSql('COMMENT ON COLUMN bot_user.id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE bad_word_usage_record ADD CONSTRAINT FK_308DE102A76ED395 FOREIGN KEY (user_id) REFERENCES bot_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE bad_word_usage_record DROP CONSTRAINT FK_308DE102A76ED395');
        $this->addSql('DROP TABLE bad_word_library_record');
        $this->addSql('DROP TABLE bad_word_usage_record');
        $this->addSql('DROP TABLE bot_user');
    }
}

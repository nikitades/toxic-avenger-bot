<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211009222548 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE bad_word_library_record (id TEXT NOT NULL, telegram_chat_id INT NOT NULL, text TEXT NOT NULL, active BOOLEAN NOT NULL, added_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5051A0C241DC10D33B8BA7C7 ON bad_word_library_record (telegram_chat_id, text)');
        $this->addSql('CREATE TABLE bad_word_usage_record (id TEXT NOT NULL, user_id TEXT DEFAULT NULL, telegram_message_id INT NOT NULL, telegram_chat_id INT NOT NULL, library_word_id TEXT NOT NULL, added_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_308DE102A76ED395 ON bad_word_usage_record (user_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_308DE102A76ED39541DC10D39996FBBD ON bad_word_usage_record (user_id, telegram_chat_id, library_word_id)');
        $this->addSql('CREATE TABLE "user" (id TEXT NOT NULL, telegram_id INT NOT NULL, name TEXT NOT NULL, added_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, bad_words_used INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649CC0B3066 ON "user" (telegram_id)');
        $this->addSql('ALTER TABLE bad_word_usage_record ADD CONSTRAINT FK_308DE102A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE bad_word_usage_record DROP CONSTRAINT FK_308DE102A76ED395');
        $this->addSql('DROP TABLE bad_word_library_record');
        $this->addSql('DROP TABLE bad_word_usage_record');
        $this->addSql('DROP TABLE "user"');
    }
}

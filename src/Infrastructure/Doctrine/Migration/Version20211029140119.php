<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\Infrastructure\Doctrine\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211029140119 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bad_word_library_record ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE bad_word_library_record DROP added_at');
        $this->addSql('COMMENT ON COLUMN bad_word_library_record.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE bad_word_usage_record ALTER sent_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE bad_word_usage_record ALTER sent_at DROP DEFAULT');
        $this->addSql('COMMENT ON COLUMN bad_word_usage_record.sent_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE bot_user ALTER added_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE bot_user ALTER added_at DROP DEFAULT');
        $this->addSql('COMMENT ON COLUMN bot_user.added_at IS \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE bot_user ALTER added_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE bot_user ALTER added_at DROP DEFAULT');
        $this->addSql('COMMENT ON COLUMN bot_user.added_at IS NULL');
        $this->addSql('ALTER TABLE bad_word_usage_record ALTER sent_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE bad_word_usage_record ALTER sent_at DROP DEFAULT');
        $this->addSql('COMMENT ON COLUMN bad_word_usage_record.sent_at IS NULL');
        $this->addSql('ALTER TABLE bad_word_library_record ADD added_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE bad_word_library_record DROP updated_at');
    }
}

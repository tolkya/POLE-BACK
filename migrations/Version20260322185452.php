<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260322185452 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE activity_type ADD description TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE activity_type ADD status VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE activity_type ADD parent_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE activity_type ADD CONSTRAINT FK_8F1A8CBB727ACA70 FOREIGN KEY (parent_id) REFERENCES activity_type (id)');
        $this->addSql('CREATE INDEX IDX_8F1A8CBB727ACA70 ON activity_type (parent_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE activity_type DROP CONSTRAINT FK_8F1A8CBB727ACA70');
        $this->addSql('DROP INDEX IDX_8F1A8CBB727ACA70');
        $this->addSql('ALTER TABLE activity_type DROP description');
        $this->addSql('ALTER TABLE activity_type DROP status');
        $this->addSql('ALTER TABLE activity_type DROP parent_id');
    }
}

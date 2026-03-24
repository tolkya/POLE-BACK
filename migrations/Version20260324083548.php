<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260324083548 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE activity_type DROP CONSTRAINT fk_8f1a8cbb727aca70');
        $this->addSql('DROP INDEX idx_8f1a8cbb727aca70');
        $this->addSql('ALTER TABLE activity_type DROP parent_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE activity_type ADD parent_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE activity_type ADD CONSTRAINT fk_8f1a8cbb727aca70 FOREIGN KEY (parent_id) REFERENCES activity_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_8f1a8cbb727aca70 ON activity_type (parent_id)');
    }
}

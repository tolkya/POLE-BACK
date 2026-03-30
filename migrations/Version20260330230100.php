<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260330230100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE club ADD description TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE club ADD theme_color VARCHAR(7) DEFAULT NULL');
        $this->addSql('ALTER TABLE club ADD logo_filename VARCHAR(500) DEFAULT NULL');
        $this->addSql('ALTER TABLE club ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE club ADD street VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE club ADD postal_code VARCHAR(10) DEFAULT NULL');
        $this->addSql('ALTER TABLE club ADD city VARCHAR(100) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE club DROP description');
        $this->addSql('ALTER TABLE club DROP theme_color');
        $this->addSql('ALTER TABLE club DROP logo_filename');
        $this->addSql('ALTER TABLE club DROP updated_at');
        $this->addSql('ALTER TABLE club DROP street');
        $this->addSql('ALTER TABLE club DROP postal_code');
        $this->addSql('ALTER TABLE club DROP city');
    }
}

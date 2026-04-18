<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260418000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Level : remplace value (enum) par name (varchar 100) + ajoute position (int)';
    }

    public function up(Schema $schema): void
    {
        // 1. Supprimer l'ancienne contrainte unique sur (activity_id, value)
        $this->addSql('ALTER TABLE level DROP CONSTRAINT IF EXISTS uniq_level_activity_value');

        // 2. Renommer la colonne value → name et changer le type (enum → varchar)
        $this->addSql('ALTER TABLE level RENAME COLUMN value TO name');
        $this->addSql('ALTER TABLE level ALTER COLUMN name TYPE VARCHAR(100)');

        // 3. Ajouter la colonne position
        $this->addSql('ALTER TABLE level ADD position INT NOT NULL DEFAULT 0');

        // 4. Initialiser position basée sur l'id (ordre de création)
        $this->addSql('
            UPDATE level l
            SET position = sub.row_num - 1
            FROM (
                SELECT id, ROW_NUMBER() OVER (PARTITION BY activity_id ORDER BY id ASC) AS row_num
                FROM level
            ) sub
            WHERE l.id = sub.id
        ');

        // 5. Créer la nouvelle contrainte unique sur (activity_id, name)
        $this->addSql('ALTER TABLE level ADD CONSTRAINT uniq_level_activity_name UNIQUE (activity_id, name)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE level DROP CONSTRAINT IF EXISTS uniq_level_activity_name');
        $this->addSql('ALTER TABLE level DROP COLUMN position');
        $this->addSql('ALTER TABLE level RENAME COLUMN name TO value');
        $this->addSql('ALTER TABLE level ADD CONSTRAINT uniq_level_activity_value UNIQUE (activity_id, value)');
    }
}
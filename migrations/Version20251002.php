<?php
/**
 * Auteur : Mehdi Graiet
 * Version : 1.0.0
 * Migration Doctrine pour créer la table "product"
 */

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251002120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Création de la table product avec UUIDv6, nom, sku, prix, dates';
    }

    public function up(Schema $schema): void
    {
        // Création de la table "product"
        $this->addSql('CREATE TABLE product (
            id UUID NOT NULL,
            name VARCHAR(255) NOT NULL,
            sku VARCHAR(17) NOT NULL,
            price DOUBLE PRECISION NOT NULL,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
            PRIMARY KEY(id)
        )');

        // Index unique sur le SKU
        $this->addSql('CREATE UNIQUE INDEX UNIQ_PRODUCT_SKU ON product (sku)');

        // Extension PostgreSQL pour gérer les UUID
        $this->addSql('CREATE EXTENSION IF NOT EXISTS "uuid-ossp"');
    }

    public function down(Schema $schema): void
    {
        // Suppression de la table en rollback
        $this->addSql('DROP TABLE product');
    }
}

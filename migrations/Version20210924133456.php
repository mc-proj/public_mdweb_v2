<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210924133456 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE mdwfactures (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, adresse_livraison_id INT NOT NULL, code_promo_id INT DEFAULT NULL, date_creation DATETIME NOT NULL, montant_total INT NOT NULL, montant_ht INT NOT NULL, montant_ttc INT NOT NULL, message VARCHAR(255) DEFAULT NULL, INDEX IDX_DF6CE6A7A76ED395 (user_id), INDEX IDX_DF6CE6A7BE2F0A35 (adresse_livraison_id), INDEX IDX_DF6CE6A7294102D4 (code_promo_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mdwfactures_produits (id INT AUTO_INCREMENT NOT NULL, produit_id INT NOT NULL, facture_id INT NOT NULL, quantite INT NOT NULL, INDEX IDX_FAE9D144F347EFB (produit_id), INDEX IDX_FAE9D1447F2DEE08 (facture_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE mdwfactures ADD CONSTRAINT FK_DF6CE6A7A76ED395 FOREIGN KEY (user_id) REFERENCES mdwusers (id)');
        $this->addSql('ALTER TABLE mdwfactures ADD CONSTRAINT FK_DF6CE6A7BE2F0A35 FOREIGN KEY (adresse_livraison_id) REFERENCES mdwadresses_livraison (id)');
        $this->addSql('ALTER TABLE mdwfactures ADD CONSTRAINT FK_DF6CE6A7294102D4 FOREIGN KEY (code_promo_id) REFERENCES mdwcodes_promos (id)');
        $this->addSql('ALTER TABLE mdwfactures_produits ADD CONSTRAINT FK_FAE9D144F347EFB FOREIGN KEY (produit_id) REFERENCES mdwproduits (id)');
        $this->addSql('ALTER TABLE mdwfactures_produits ADD CONSTRAINT FK_FAE9D1447F2DEE08 FOREIGN KEY (facture_id) REFERENCES mdwfactures (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mdwfactures_produits DROP FOREIGN KEY FK_FAE9D1447F2DEE08');
        $this->addSql('DROP TABLE mdwfactures');
        $this->addSql('DROP TABLE mdwfactures_produits');
    }
}

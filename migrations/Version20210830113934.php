<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210830113934 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE mdwadresses_livraison (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, adresse VARCHAR(255) NOT NULL, ville VARCHAR(45) NOT NULL, code_postal VARCHAR(45) NOT NULL, pays VARCHAR(45) NOT NULL, telephone VARCHAR(45) DEFAULT NULL, actif TINYINT(1) NOT NULL, derniere_modification DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mdwavis (id INT AUTO_INCREMENT NOT NULL, produit_id INT NOT NULL, user_id INT NOT NULL, note INT NOT NULL, commentaire VARCHAR(255) DEFAULT NULL, INDEX IDX_14FF724F347EFB (produit_id), INDEX IDX_14FF724A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mdwcaracteristiques (id INT AUTO_INCREMENT NOT NULL, produit_id INT NOT NULL, type_caracteristique_id INT NOT NULL, valeur VARCHAR(255) NOT NULL, INDEX IDX_A3E4FF4AF347EFB (produit_id), INDEX IDX_A3E4FF4A70659567 (type_caracteristique_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mdwcategories (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, image VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mdwcategories_mdwcategories (mdwcategories_source INT NOT NULL, mdwcategories_target INT NOT NULL, INDEX IDX_194FE8DA22ED439 (mdwcategories_source), INDEX IDX_194FE8DBBCB84B6 (mdwcategories_target), PRIMARY KEY(mdwcategories_source, mdwcategories_target)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mdwcodes_promos (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, type_promo VARCHAR(255) NOT NULL, valeur INT NOT NULL, minimum_achat INT NOT NULL, date_debut_validite DATE NOT NULL, date_fin_validite DATE NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mdwcodes_promos_users (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, code_promo_id INT NOT NULL, date_utilisation DATE NOT NULL, INDEX IDX_B104EE2CA76ED395 (user_id), INDEX IDX_B104EE2C294102D4 (code_promo_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mdwfactures (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, date_creation DATETIME NOT NULL, montant_total INT NOT NULL, montant_ht INT NOT NULL, montant_ttc INT NOT NULL, message VARCHAR(255) DEFAULT NULL, INDEX IDX_DF6CE6A7A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mdwfactures_produits (id INT AUTO_INCREMENT NOT NULL, produit_id INT NOT NULL, facture_id INT NOT NULL, quantite INT NOT NULL, INDEX IDX_FAE9D144F347EFB (produit_id), INDEX IDX_FAE9D1447F2DEE08 (facture_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mdwimages (id INT AUTO_INCREMENT NOT NULL, produit_id INT NOT NULL, image VARCHAR(255) NOT NULL, INDEX IDX_67962D70F347EFB (produit_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mdwpaniers (id INT AUTO_INCREMENT NOT NULL, adresse_livraison_id INT NOT NULL, commande_terminee TINYINT(1) NOT NULL, date_creation DATETIME NOT NULL, montant_ht INT NOT NULL, montant_ttc INT NOT NULL, message VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_B57CE0DFBE2F0A35 (adresse_livraison_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mdwpaniers_produits (id INT AUTO_INCREMENT NOT NULL, produit_id INT NOT NULL, panier_id INT NOT NULL, quantite INT NOT NULL, INDEX IDX_B94C09FCF347EFB (produit_id), INDEX IDX_B94C09FCF77D927C (panier_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mdwproduits (id INT AUTO_INCREMENT NOT NULL, taux_tva_id INT NOT NULL, reference VARCHAR(45) NOT NULL, nom VARCHAR(45) NOT NULL, est_visible TINYINT(1) NOT NULL, mis_en_avant TINYINT(1) NOT NULL, description_courte VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, date_debut_promo DATE DEFAULT NULL, date_fin_promo DATE DEFAULT NULL, tva_active TINYINT(1) NOT NULL, quantite_stock INT NOT NULL, limite_basse_stock INT NOT NULL, commandable_sans_stock TINYINT(1) NOT NULL, est_evaluable TINYINT(1) NOT NULL, tarif INT NOT NULL, tarif_promo INT NOT NULL, date_creation DATE NOT NULL, INDEX IDX_67066020F7FEBCCE (taux_tva_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mdwproduits_mdwcategories (mdwproduits_id INT NOT NULL, mdwcategories_id INT NOT NULL, INDEX IDX_998A0648D2924D25 (mdwproduits_id), INDEX IDX_998A06483636EFBB (mdwcategories_id), PRIMARY KEY(mdwproduits_id, mdwcategories_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mdwproduits_mdwproduits (mdwproduits_source INT NOT NULL, mdwproduits_target INT NOT NULL, INDEX IDX_444203344841942C (mdwproduits_source), INDEX IDX_4442033451A4C4A3 (mdwproduits_target), PRIMARY KEY(mdwproduits_source, mdwproduits_target)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mdwtaux_tva (id INT AUTO_INCREMENT NOT NULL, intitule VARCHAR(255) NOT NULL, taux INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mdwtypes_caracteristiques (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mdwusers (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, is_verified TINYINT(1) NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, adresse VARCHAR(255) NOT NULL, code_postal VARCHAR(45) NOT NULL, ville VARCHAR(45) NOT NULL, telephone VARCHAR(45) DEFAULT NULL, UNIQUE INDEX UNIQ_95B36D78E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE mdwavis ADD CONSTRAINT FK_14FF724F347EFB FOREIGN KEY (produit_id) REFERENCES mdwproduits (id)');
        $this->addSql('ALTER TABLE mdwavis ADD CONSTRAINT FK_14FF724A76ED395 FOREIGN KEY (user_id) REFERENCES mdwusers (id)');
        $this->addSql('ALTER TABLE mdwcaracteristiques ADD CONSTRAINT FK_A3E4FF4AF347EFB FOREIGN KEY (produit_id) REFERENCES mdwproduits (id)');
        $this->addSql('ALTER TABLE mdwcaracteristiques ADD CONSTRAINT FK_A3E4FF4A70659567 FOREIGN KEY (type_caracteristique_id) REFERENCES mdwtypes_caracteristiques (id)');
        $this->addSql('ALTER TABLE mdwcategories_mdwcategories ADD CONSTRAINT FK_194FE8DA22ED439 FOREIGN KEY (mdwcategories_source) REFERENCES mdwcategories (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE mdwcategories_mdwcategories ADD CONSTRAINT FK_194FE8DBBCB84B6 FOREIGN KEY (mdwcategories_target) REFERENCES mdwcategories (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE mdwcodes_promos_users ADD CONSTRAINT FK_B104EE2CA76ED395 FOREIGN KEY (user_id) REFERENCES mdwusers (id)');
        $this->addSql('ALTER TABLE mdwcodes_promos_users ADD CONSTRAINT FK_B104EE2C294102D4 FOREIGN KEY (code_promo_id) REFERENCES mdwcodes_promos (id)');
        $this->addSql('ALTER TABLE mdwfactures ADD CONSTRAINT FK_DF6CE6A7A76ED395 FOREIGN KEY (user_id) REFERENCES mdwusers (id)');
        $this->addSql('ALTER TABLE mdwfactures_produits ADD CONSTRAINT FK_FAE9D144F347EFB FOREIGN KEY (produit_id) REFERENCES mdwproduits (id)');
        $this->addSql('ALTER TABLE mdwfactures_produits ADD CONSTRAINT FK_FAE9D1447F2DEE08 FOREIGN KEY (facture_id) REFERENCES mdwfactures (id)');
        $this->addSql('ALTER TABLE mdwimages ADD CONSTRAINT FK_67962D70F347EFB FOREIGN KEY (produit_id) REFERENCES mdwproduits (id)');
        $this->addSql('ALTER TABLE mdwpaniers ADD CONSTRAINT FK_B57CE0DFBE2F0A35 FOREIGN KEY (adresse_livraison_id) REFERENCES mdwadresses_livraison (id)');
        $this->addSql('ALTER TABLE mdwpaniers_produits ADD CONSTRAINT FK_B94C09FCF347EFB FOREIGN KEY (produit_id) REFERENCES mdwproduits (id)');
        $this->addSql('ALTER TABLE mdwpaniers_produits ADD CONSTRAINT FK_B94C09FCF77D927C FOREIGN KEY (panier_id) REFERENCES mdwpaniers (id)');
        $this->addSql('ALTER TABLE mdwproduits ADD CONSTRAINT FK_67066020F7FEBCCE FOREIGN KEY (taux_tva_id) REFERENCES mdwtaux_tva (id)');
        $this->addSql('ALTER TABLE mdwproduits_mdwcategories ADD CONSTRAINT FK_998A0648D2924D25 FOREIGN KEY (mdwproduits_id) REFERENCES mdwproduits (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE mdwproduits_mdwcategories ADD CONSTRAINT FK_998A06483636EFBB FOREIGN KEY (mdwcategories_id) REFERENCES mdwcategories (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE mdwproduits_mdwproduits ADD CONSTRAINT FK_444203344841942C FOREIGN KEY (mdwproduits_source) REFERENCES mdwproduits (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE mdwproduits_mdwproduits ADD CONSTRAINT FK_4442033451A4C4A3 FOREIGN KEY (mdwproduits_target) REFERENCES mdwproduits (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mdwpaniers DROP FOREIGN KEY FK_B57CE0DFBE2F0A35');
        $this->addSql('ALTER TABLE mdwcategories_mdwcategories DROP FOREIGN KEY FK_194FE8DA22ED439');
        $this->addSql('ALTER TABLE mdwcategories_mdwcategories DROP FOREIGN KEY FK_194FE8DBBCB84B6');
        $this->addSql('ALTER TABLE mdwproduits_mdwcategories DROP FOREIGN KEY FK_998A06483636EFBB');
        $this->addSql('ALTER TABLE mdwcodes_promos_users DROP FOREIGN KEY FK_B104EE2C294102D4');
        $this->addSql('ALTER TABLE mdwfactures_produits DROP FOREIGN KEY FK_FAE9D1447F2DEE08');
        $this->addSql('ALTER TABLE mdwpaniers_produits DROP FOREIGN KEY FK_B94C09FCF77D927C');
        $this->addSql('ALTER TABLE mdwavis DROP FOREIGN KEY FK_14FF724F347EFB');
        $this->addSql('ALTER TABLE mdwcaracteristiques DROP FOREIGN KEY FK_A3E4FF4AF347EFB');
        $this->addSql('ALTER TABLE mdwfactures_produits DROP FOREIGN KEY FK_FAE9D144F347EFB');
        $this->addSql('ALTER TABLE mdwimages DROP FOREIGN KEY FK_67962D70F347EFB');
        $this->addSql('ALTER TABLE mdwpaniers_produits DROP FOREIGN KEY FK_B94C09FCF347EFB');
        $this->addSql('ALTER TABLE mdwproduits_mdwcategories DROP FOREIGN KEY FK_998A0648D2924D25');
        $this->addSql('ALTER TABLE mdwproduits_mdwproduits DROP FOREIGN KEY FK_444203344841942C');
        $this->addSql('ALTER TABLE mdwproduits_mdwproduits DROP FOREIGN KEY FK_4442033451A4C4A3');
        $this->addSql('ALTER TABLE mdwproduits DROP FOREIGN KEY FK_67066020F7FEBCCE');
        $this->addSql('ALTER TABLE mdwcaracteristiques DROP FOREIGN KEY FK_A3E4FF4A70659567');
        $this->addSql('ALTER TABLE mdwavis DROP FOREIGN KEY FK_14FF724A76ED395');
        $this->addSql('ALTER TABLE mdwcodes_promos_users DROP FOREIGN KEY FK_B104EE2CA76ED395');
        $this->addSql('ALTER TABLE mdwfactures DROP FOREIGN KEY FK_DF6CE6A7A76ED395');
        $this->addSql('DROP TABLE mdwadresses_livraison');
        $this->addSql('DROP TABLE mdwavis');
        $this->addSql('DROP TABLE mdwcaracteristiques');
        $this->addSql('DROP TABLE mdwcategories');
        $this->addSql('DROP TABLE mdwcategories_mdwcategories');
        $this->addSql('DROP TABLE mdwcodes_promos');
        $this->addSql('DROP TABLE mdwcodes_promos_users');
        $this->addSql('DROP TABLE mdwfactures');
        $this->addSql('DROP TABLE mdwfactures_produits');
        $this->addSql('DROP TABLE mdwimages');
        $this->addSql('DROP TABLE mdwpaniers');
        $this->addSql('DROP TABLE mdwpaniers_produits');
        $this->addSql('DROP TABLE mdwproduits');
        $this->addSql('DROP TABLE mdwproduits_mdwcategories');
        $this->addSql('DROP TABLE mdwproduits_mdwproduits');
        $this->addSql('DROP TABLE mdwtaux_tva');
        $this->addSql('DROP TABLE mdwtypes_caracteristiques');
        $this->addSql('DROP TABLE mdwusers');
    }
}

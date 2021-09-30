<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210930120232 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mdwpaniers ADD user_id INT NOT NULL, ADD code_promo_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE mdwpaniers ADD CONSTRAINT FK_B57CE0DFA76ED395 FOREIGN KEY (user_id) REFERENCES mdwusers (id)');
        $this->addSql('ALTER TABLE mdwpaniers ADD CONSTRAINT FK_B57CE0DF294102D4 FOREIGN KEY (code_promo_id) REFERENCES mdwcodes_promos (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B57CE0DFA76ED395 ON mdwpaniers (user_id)');
        $this->addSql('CREATE INDEX IDX_B57CE0DF294102D4 ON mdwpaniers (code_promo_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mdwpaniers DROP FOREIGN KEY FK_B57CE0DFA76ED395');
        $this->addSql('ALTER TABLE mdwpaniers DROP FOREIGN KEY FK_B57CE0DF294102D4');
        $this->addSql('DROP INDEX UNIQ_B57CE0DFA76ED395 ON mdwpaniers');
        $this->addSql('DROP INDEX IDX_B57CE0DF294102D4 ON mdwpaniers');
        $this->addSql('ALTER TABLE mdwpaniers DROP user_id, DROP code_promo_id');
    }
}

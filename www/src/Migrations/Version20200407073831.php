<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200407073831 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE subscription (id INT AUTO_INCREMENT NOT NULL, subscription_type_id INT NOT NULL, user_id INT NOT NULL, activated_at DATETIME DEFAULT NULL, expired_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, active SMALLINT DEFAULT 0, price DOUBLE PRECISION NOT NULL, price_currency VARCHAR(255) NOT NULL, currency_default DOUBLE PRECISION NOT NULL, payment_id_order VARCHAR(255) DEFAULT NULL, payment_method VARCHAR(255) DEFAULT NULL, INDEX IDX_AD005B69857C9F24 (subscription_type_id), INDEX IDX_AD005B699D86650F (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB ROW_FORMAT = DYNAMIC');
        $this->addSql('ALTER TABLE subscription ADD CONSTRAINT FK_AD005B69857C9F24 FOREIGN KEY (subscription_type_id) REFERENCES subscription_type (id)');
        $this->addSql('ALTER TABLE subscription ADD CONSTRAINT FK_AD005B699D86650F FOREIGN KEY (user_id) REFERENCES users (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE subscription');
    }
}

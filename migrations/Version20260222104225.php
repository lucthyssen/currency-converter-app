<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260222104225 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE allowed_ip (id INT AUTO_INCREMENT NOT NULL, ip_or_subnet VARCHAR(50) NOT NULL, description VARCHAR(100) DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE currency (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(3) NOT NULL, name VARCHAR(50) NOT NULL, UNIQUE INDEX UNIQ_6956883F77153098 (code), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE exchange_rate (id INT AUTO_INCREMENT NOT NULL, rate NUMERIC(15, 8) NOT NULL, date DATETIME NOT NULL, base_currency_id INT NOT NULL, target_currency_id INT NOT NULL, INDEX IDX_E9521FAB3101778E (base_currency_id), INDEX IDX_E9521FABBF1ECE7C (target_currency_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE exchange_rate ADD CONSTRAINT FK_E9521FAB3101778E FOREIGN KEY (base_currency_id) REFERENCES currency (id)');
        $this->addSql('ALTER TABLE exchange_rate ADD CONSTRAINT FK_E9521FABBF1ECE7C FOREIGN KEY (target_currency_id) REFERENCES currency (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE exchange_rate DROP FOREIGN KEY FK_E9521FAB3101778E');
        $this->addSql('ALTER TABLE exchange_rate DROP FOREIGN KEY FK_E9521FABBF1ECE7C');
        $this->addSql('DROP TABLE allowed_ip');
        $this->addSql('DROP TABLE currency');
        $this->addSql('DROP TABLE exchange_rate');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}

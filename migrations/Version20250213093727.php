<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250213093727 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE test_result (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, qcm_id INT NOT NULL, score INT NOT NULL, num_questions INT NOT NULL, INDEX IDX_84B3C63DA76ED395 (user_id), INDEX IDX_84B3C63DFF6241A6 (qcm_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE test_result ADD CONSTRAINT FK_84B3C63DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE test_result ADD CONSTRAINT FK_84B3C63DFF6241A6 FOREIGN KEY (qcm_id) REFERENCES quiz (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE test_result DROP FOREIGN KEY FK_84B3C63DA76ED395');
        $this->addSql('ALTER TABLE test_result DROP FOREIGN KEY FK_84B3C63DFF6241A6');
        $this->addSql('DROP TABLE test_result');
    }
}

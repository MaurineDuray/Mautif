<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230708130244 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE galery (id INT AUTO_INCREMENT NOT NULL, pattern_id INT NOT NULL, author_id INT NOT NULL, picture VARCHAR(255) NOT NULL, INDEX IDX_B5D5320F734A20F (pattern_id), INDEX IDX_B5D5320F675F31B (author_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE galery ADD CONSTRAINT FK_B5D5320F734A20F FOREIGN KEY (pattern_id) REFERENCES pattern (id)');
        $this->addSql('ALTER TABLE galery ADD CONSTRAINT FK_B5D5320F675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE galery DROP FOREIGN KEY FK_B5D5320F734A20F');
        $this->addSql('ALTER TABLE galery DROP FOREIGN KEY FK_B5D5320F675F31B');
        $this->addSql('DROP TABLE galery');
    }
}

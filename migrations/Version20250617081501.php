<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250617081501 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE access_log (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, access_point_id INT NOT NULL, granted_by_id INT DEFAULT NULL, granted TINYINT(1) NOT NULL, timestamp DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_EF7F3510A76ED395 (user_id), INDEX IDX_EF7F3510AD3D0F93 (access_point_id), INDEX IDX_EF7F35103151C11F (granted_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE access_point (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, active TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_5E308F775E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE authorization_rule (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, user_group_id INT DEFAULT NULL, access_point_id INT DEFAULT NULL, active TINYINT(1) NOT NULL, start_timestamp DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', end_timestamp DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', mon TINYINT(1) NOT NULL, tue TINYINT(1) NOT NULL, wed TINYINT(1) NOT NULL, thu TINYINT(1) NOT NULL, fri TINYINT(1) NOT NULL, sat TINYINT(1) NOT NULL, sun TINYINT(1) NOT NULL, temp TINYINT(1) NOT NULL, INDEX IDX_1EFE74ECA76ED395 (user_id), INDEX IDX_1EFE74EC1ED93D47 (user_group_id), INDEX IDX_1EFE74ECAD3D0F93 (access_point_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, birth_date DATE NOT NULL COMMENT '(DC2Type:date_immutable)', username VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, phone_number VARCHAR(15) NOT NULL, photo LONGBLOB DEFAULT NULL, administrator TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), UNIQUE INDEX UNIQ_8D93D649F85E0677 (username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE user_group (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_8F02BF9D5E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE user_group_user (user_group_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_3AE4BD51ED93D47 (user_group_id), INDEX IDX_3AE4BD5A76ED395 (user_id), PRIMARY KEY(user_group_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE access_log ADD CONSTRAINT FK_EF7F3510A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE access_log ADD CONSTRAINT FK_EF7F3510AD3D0F93 FOREIGN KEY (access_point_id) REFERENCES access_point (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE access_log ADD CONSTRAINT FK_EF7F35103151C11F FOREIGN KEY (granted_by_id) REFERENCES authorization_rule (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE authorization_rule ADD CONSTRAINT FK_1EFE74ECA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE authorization_rule ADD CONSTRAINT FK_1EFE74EC1ED93D47 FOREIGN KEY (user_group_id) REFERENCES user_group (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE authorization_rule ADD CONSTRAINT FK_1EFE74ECAD3D0F93 FOREIGN KEY (access_point_id) REFERENCES access_point (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_group_user ADD CONSTRAINT FK_3AE4BD51ED93D47 FOREIGN KEY (user_group_id) REFERENCES user_group (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_group_user ADD CONSTRAINT FK_3AE4BD5A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE access_log DROP FOREIGN KEY FK_EF7F3510A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE access_log DROP FOREIGN KEY FK_EF7F3510AD3D0F93
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE access_log DROP FOREIGN KEY FK_EF7F35103151C11F
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE authorization_rule DROP FOREIGN KEY FK_1EFE74ECA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE authorization_rule DROP FOREIGN KEY FK_1EFE74EC1ED93D47
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE authorization_rule DROP FOREIGN KEY FK_1EFE74ECAD3D0F93
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_group_user DROP FOREIGN KEY FK_3AE4BD51ED93D47
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_group_user DROP FOREIGN KEY FK_3AE4BD5A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE access_log
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE access_point
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE authorization_rule
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user_group
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user_group_user
        SQL);
    }
}

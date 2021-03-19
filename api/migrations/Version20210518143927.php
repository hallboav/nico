<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210518143927 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA db_automacao_sti');

        $this->addSql('CREATE SEQUENCE sq_contrato INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql(<<<'SQL'
CREATE TABLE db_automacao_sti.tb_contrato (
    pk_contrato INT NOT NULL,
    dh_criado_em TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
    dh_atualizado_em TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
    ds_nome VARCHAR(255) NOT NULL,
    nu_cnpj VARCHAR(14) NOT NULL,
    ds_email VARCHAR(255) NOT NULL,
    no_preposto VARCHAR(255) NOT NULL,
    PRIMARY KEY(pk_contrato)
)
SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP SEQUENCE sq_contrato CASCADE');
        $this->addSql('DROP TABLE tb_contrato');
    }
}

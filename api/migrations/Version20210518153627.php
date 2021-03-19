<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210518153627 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE sq_ordem_servico INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql(<<<'SQL'
CREATE TABLE db_automacao_sti.tb_ordem_servico (
    pk_ordem_servico INT NOT NULL,
    dh_criado_em TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
    dh_atualizado_em TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
    pk_contrato INT NOT NULL,
    ds_taiga_tag VARCHAR(255) NOT NULL,
    PRIMARY KEY(pk_ordem_servico)
)
SQL);
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D846CC8BE7B539F ON db_automacao_sti.tb_ordem_servico (ds_taiga_tag)');
        $this->addSql(<<<'SQL'
CREATE INDEX IDX_D846CC8B12022875 ON db_automacao_sti.tb_ordem_servico (pk_contrato)
SQL);
        $this->addSql(<<<'SQL'
ALTER TABLE db_automacao_sti.tb_ordem_servico
ADD CONSTRAINT FK_D846CC8B12022875
FOREIGN KEY (pk_contrato)
REFERENCES db_automacao_sti.tb_contrato (pk_contrato)
NOT DEFERRABLE INITIALLY IMMEDIATE
SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE sq_ordem_servico CASCADE');
        $this->addSql('DROP TABLE tb_ordem_servico');
    }
}

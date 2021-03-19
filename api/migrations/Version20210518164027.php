<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210518164027 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE sq_projeto INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql(<<<'SQL'
CREATE TABLE db_automacao_sti.tb_projeto (
    pk_projeto INT NOT NULL,
    dh_criado_em TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
    dh_atualizado_em TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
    pk_ordem_servico INT DEFAULT NULL,
    nu_taiga_id INT NOT NULL,
    no_projeto VARCHAR(500) NOT NULL,
    ds_projeto TEXT NOT NULL,
    PRIMARY KEY(pk_projeto))
SQL);
        $this->addSql(<<<'SQL'
ALTER TABLE db_automacao_sti.tb_projeto
ADD CONSTRAINT FK_6666480855B7759F
FOREIGN KEY (pk_ordem_servico)
REFERENCES db_automacao_sti.tb_ordem_servico (pk_ordem_servico)
NOT DEFERRABLE INITIALLY IMMEDIATE
SQL);
        $this->addSql('CREATE UNIQUE INDEX UNIQ_66664808CF066D19 ON db_automacao_sti.tb_projeto (nu_taiga_id)');
        $this->addSql('CREATE INDEX IDX_6666480855B7759F ON db_automacao_sti.tb_projeto (pk_ordem_servico)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE sq_projeto CASCADE');
        $this->addSql('DROP TABLE tb_projeto');
    }
}

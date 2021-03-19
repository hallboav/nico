<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210525141258 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE sq_sprint INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql(<<<'SQL'
CREATE TABLE db_automacao_sti.tb_sprint (
    pk_sprint INT NOT NULL,
    dh_criado_em TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
    dh_atualizado_em TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
    fk_projeto INT NOT NULL,
    nu_taiga_id INT NOT NULL,
    no_sprint VARCHAR(500) NOT NULL,
    dh_iniciada_em TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
    dh_finalizada_em TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
    st_ativa BOOLEAN NOT NULL,
    st_fechada BOOLEAN NOT NULL,
    vl_nspe DOUBLE PRECISION DEFAULT NULL,
    vl_nspp DOUBLE PRECISION DEFAULT NULL,
    vl_ip DOUBLE PRECISION DEFAULT NULL,
    vl_nibf INT DEFAULT NULL,
    vl_nibp INT DEFAULT NULL,
    vl_iq DOUBLE PRECISION DEFAULT NULL,
    PRIMARY KEY(pk_sprint)
)
SQL);
        $this->addSql(<<<'SQL'
ALTER TABLE db_automacao_sti.tb_sprint
ADD CONSTRAINT FK_D08C7B8D507AA0B8
FOREIGN KEY (fk_projeto)
REFERENCES db_automacao_sti.tb_projeto (pk_projeto)
NOT DEFERRABLE INITIALLY IMMEDIATE
SQL);
        $this->addSql('CREATE INDEX IDX_D08C7B8D507AA0B8 ON db_automacao_sti.tb_sprint (fk_projeto)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE sq_sprint CASCADE');
        $this->addSql('DROP TABLE db_automacao_sti.tb_sprint');
    }
}

<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(schema="db_automacao_sti", name="tb_projeto")
 *
 * @ApiResource(
 *     normalizationContext={"groups"={"projeto:read"}},
 *     denormalizationContext={"groups"={"projeto:write"}},
 *     collectionOperations={
 *         "get"
 *     },
 *     itemOperations={
 *         "get"
 *     }
 * )
 */
class Projeto
{
    /**
     * @Groups({"projeto:read"})
     *
     * @ORM\Id
     * @ORM\Column(name="pk_projeto", type="integer")
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="sq_projeto")
     */
    private ?int $id = null;

    /**
     * @Groups({"projeto:read"})
     *
     * @ORM\Column(name="dh_criado_em", type="datetime")
     */
    private ?\DateTimeInterface $criadoEm = null;

    /**
     * @Groups({"projeto:read"})
     *
     * @ORM\Column(name="dh_atualizado_em", type="datetime")
     */
    private ?\DateTimeInterface $atualizadoEm = null;

    /**
     * @Assert\NotBlank(allowNull=true)
     * @Assert\Valid
     *
     * @Groups({"projeto:read", "projeto:write"})
     *
     * @ORM\ManyToOne(targetEntity=OrdemServico::class)
     * @ORM\JoinColumn(name="pk_ordem_servico", referencedColumnName="pk_ordem_servico")
     */
    private ?OrdemServico $ordemServico = null;

    /**
     * @Assert\NotBlank
     * @Assert\Type("integer")
     *
     * @Groups({"projeto:read", "projeto:write"})
     *
     * @ORM\Column(name="nu_taiga_id", type="integer", unique=true)
     */
    private ?int $taigaId = null;

    /**
     * @Assert\NotBlank
     * @Assert\Length(max=500)
     *
     * @Groups({"projeto:read", "projeto:write"})
     *
     * @ORM\Column(name="no_projeto", type="string", length=500)
     */
    private ?string $nome = null;

    /**
     * @Groups({"projeto:read", "projeto:write"})
     *
     * @ORM\Column(name="ds_projeto", type="text")
     */
    private ?string $descricao = null;

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function onPrePersistOrUpdate(): void
    {
        $this->setAtualizadoEm(new \DateTimeImmutable('now', new \DateTimeZone('UTC')));

        if (null === $this->getCriadoEm()) {
            $this->setCriadoEm(new \DateTimeImmutable('now', new \DateTimeZone('UTC')));
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCriadoEm(): ?\DateTimeInterface
    {
        return $this->criadoEm;
    }

    public function setCriadoEm(\DateTimeInterface $criadoEm): self
    {
        $this->criadoEm = $criadoEm;

        return $this;
    }

    public function getAtualizadoEm(): ?\DateTimeInterface
    {
        return $this->atualizadoEm;
    }

    public function setAtualizadoEm(\DateTimeInterface $atualizadoEm): self
    {
        $this->atualizadoEm = $atualizadoEm;

        return $this;
    }

    public function getOrdemServico(): ?OrdemServico
    {
        return $this->ordemServico;
    }

    public function setOrdemServico(?OrdemServico $ordemServico): self
    {
        $this->ordemServico = $ordemServico;

        return $this;
    }

    public function getTaigaId(): ?int
    {
        return $this->taigaId;
    }

    public function setTaigaId(int $taigaId): self
    {
        $this->taigaId = $taigaId;

        return $this;
    }

    public function getNome(): ?string
    {
        return $this->nome;
    }

    public function setNome(string $nome): self
    {
        $this->nome = $nome;

        return $this;
    }

    public function getDescricao(): ?string
    {
        return $this->descricao;
    }

    public function setDescricao(string $descricao): self
    {
        $this->descricao = $descricao;

        return $this;
    }
}

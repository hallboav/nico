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
 * @ORM\Table(schema="db_automacao_sti", name="tb_ordem_servico")
 *
 * @ApiResource(
 *     normalizationContext={"groups"={"ordem_servico:read"}},
 *     denormalizationContext={"groups"={"ordem_servico:write"}},
 *     collectionOperations={
 *         "get"
 *     },
 *     itemOperations={
 *         "get"
 *     }
 * )
 */
class OrdemServico
{
    /**
     * @Groups({"ordem_servico:read"})
     *
     * @ORM\Id
     * @ORM\Column(name="pk_ordem_servico", type="integer")
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="sq_ordem_servico")
     */
    private ?int $id = null;

    /**
     * @Groups({"ordem_servico:read"})
     *
     * @ORM\Column(name="dh_criado_em", type="datetime")
     */
    private ?\DateTimeInterface $criadoEm = null;

    /**
     * @Groups({"ordem_servico:read"})
     *
     * @ORM\Column(name="dh_atualizado_em", type="datetime")
     */
    private ?\DateTimeInterface $atualizadoEm = null;

    /**
     * @Assert\NotBlank
     * @Assert\Valid
     *
     * @Groups({"ordem_servico:read", "ordem_servico:write"})
     *
     * @ORM\ManyToOne(targetEntity=Contrato::class)
     * @ORM\JoinColumn(name="pk_contrato", referencedColumnName="pk_contrato", nullable=false)
     */
    private ?Contrato $contrato = null;

    /**
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     *
     * @Groups({"ordem_servico:read", "ordem_servico:write"})
     *
     * @ORM\Column(name="ds_taiga_tag", type="string", length=255, unique=true)
     */
    private ?string $taigaTag = null;

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

    public function getContrato(): ?Contrato
    {
        return $this->contrato;
    }

    public function setContrato(?Contrato $contrato): self
    {
        $this->contrato = $contrato;

        return $this;
    }

    public function getTaigaTag(): ?string
    {
        return $this->taigaTag;
    }

    public function setTaigaTag(string $taigaTag): self
    {
        $this->taigaTag = $taigaTag;

        return $this;
    }
}

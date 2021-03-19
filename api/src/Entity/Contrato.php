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
 * @ORM\Table(schema="db_automacao_sti", name="tb_contrato")
 *
 * @ApiResource(
 *     normalizationContext={"groups"={"contrato:read"}},
 *     denormalizationContext={"groups"={"contrato:write"}},
 *     collectionOperations={
 *         "get"
 *     },
 *     itemOperations={
 *         "get"
 *     }
 * )
 */
class Contrato
{
    /**
     * @Groups({"contrato:read"})
     *
     * @ORM\Id
     * @ORM\Column(name="pk_contrato", type="integer")
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="sq_contrato")
     */
    private ?int $id = null;

    /**
     * @Groups({"contrato:read"})
     *
     * @ORM\Column(name="dh_criado_em", type="datetime")
     */
    private ?\DateTimeInterface $criadoEm = null;

    /**
     * @Groups({"contrato:read"})
     *
     * @ORM\Column(name="dh_atualizado_em", type="datetime")
     */
    private ?\DateTimeInterface $atualizadoEm = null;

    /**
     * @Assert\NotBlank
     * @Assert\Length(min=10, max=255)
     *
     * @Groups({"contrato:read", "contrato:write"})
     *
     * @ORM\Column(name="ds_nome", type="string", length=255)
     */
    private ?string $nome = null;

    /**
     * @Assert\NotBlank
     * @Assert\Length(14)
     *
     * @Groups({"contrato:read", "contrato:write"})
     *
     * @ORM\Column(name="nu_cnpj", type="string", length=14)
     */
    private ?string $cnpj = null;

    /**
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     * @Assert\Email
     *
     * @Groups({"contrato:read", "contrato:write"})
     *
     * @ORM\Column(name="ds_email", type="string", length=255)
     */
    private ?string $email = null;

    /**
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     *
     * @Groups({"contrato:read", "contrato:write"})
     *
     * @ORM\Column(name="no_preposto", type="string", length=255)
     */
    private ?string $nomePreposto = null;

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

    public function getNome(): ?string
    {
        return $this->nome;
    }

    public function setNome(string $nome): self
    {
        $this->nome = $nome;

        return $this;
    }

    public function getCnpj(): ?string
    {
        return $this->cnpj;
    }

    public function setCnpj(string $cnpj): self
    {
        $this->cnpj = $cnpj;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getNomePreposto(): ?string
    {
        return $this->nomePreposto;
    }

    public function setNomePreposto(string $nomePreposto): self
    {
        $this->nomePreposto = $nomePreposto;

        return $this;
    }
}

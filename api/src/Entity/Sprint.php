<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\EntityListeners({
 *     "App\EntityListener\SprintEntityListener",
 * })
 * @ORM\Table(schema="db_automacao_sti", name="tb_sprint")
 *
 * @ApiResource(
 *     normalizationContext={"groups"={"sprint:read"}},
 *     denormalizationContext={"groups"={"sprint:write"}},
 *     collectionOperations={
 *         "get"
 *     },
 *     itemOperations={
 *         "get"
 *     }
 * )
 * @ApiFilter(SearchFilter::class, properties={"projeto": "exact"})
 */
class Sprint
{
    /**
     * @Groups({"sprint:read"})
     *
     * @ORM\Id
     * @ORM\Column(name="pk_sprint", type="integer")
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="sq_sprint")
     */
    private ?int $id = null;

    /**
     * @Groups({"sprint:read"})
     *
     * @ORM\Column(name="dh_criado_em", type="datetime")
     */
    private ?\DateTimeInterface $criadoEm = null;

    /**
     * @Groups({"sprint:read"})
     *
     * @ORM\Column(name="dh_atualizado_em", type="datetime")
     */
    private ?\DateTimeInterface $atualizadoEm = null;

    /**
     * @Assert\NotBlank
     * @Assert\Valid
     *
     * @Groups({"sprint:read", "sprint:write"})
     *
     * @ORM\ManyToOne(targetEntity=Projeto::class)
     * @ORM\JoinColumn(name="fk_projeto", referencedColumnName="pk_projeto", nullable=false)
     */
    private ?Projeto $projeto = null;

    /**
     * @Assert\NotBlank
     * @Assert\Type("integer")
     *
     * @Groups({"sprint:read", "sprint:write"})
     *
     * @ORM\Column(name="nu_taiga_id", type="integer", unique=true)
     */
    private ?int $taigaId = null;

    /**
     * @Assert\NotBlank
     * @Assert\Length(max=500)
     *
     * @Groups({"sprint:read", "sprint:write"})
     *
     * @ORM\Column(name="no_sprint", type="string", length=500)
     */
    private ?string $nome = null;

    /**
     * @Assert\Type("bool")
     *
     * @ORM\Column(name="st_ativa", type="boolean")
     */
    private bool $isAtiva = true;

    /**
     * @Assert\NotBlank
     * @Assert\Type("\DateTimeInterface")
     *
     * @Groups({"sprint:read", "sprint:write"})
     *
     * @ORM\Column(name="dh_iniciada_em", type="datetime")
     */
    private ?\DateTimeInterface $iniciadaEm = null;

    /**
     * @Assert\NotBlank
     * @Assert\Type("\DateTimeInterface")
     *
     * @Groups({"sprint:read", "sprint:write"})
     *
     * @ORM\Column(name="dh_finalizada_em", type="datetime")
     */
    private ?\DateTimeInterface $finalizadaEm = null;

    /**
     * @Assert\Type("bool")
     *
     * @Groups({"sprint:read"})
     *
     * @ORM\Column(name="st_fechada", type="boolean")
     */
    private ?bool $isFechada = null;

    /**
     * @Assert\Type("float")
     *
     * @Groups({"sprint:read"})
     *
     * @ORM\Column(name="vl_nspe", type="float", nullable=true)
     */
    private ?float $nspe = null;

    /**
     * @Assert\Type("float")
     *
     * @Groups({"sprint:read"})
     *
     * @ORM\Column(name="vl_nspp", type="float", nullable=true)
     */
    private ?float $nspp = null;

    /**
     * @Assert\Type("float")
     *
     * @Groups({"sprint:read"})
     *
     * @ORM\Column(name="vl_ip", type="float", nullable=true)
     */
    private ?float $ip = null;

    /**
     * @Assert\Type("integer")
     *
     * @Groups({"sprint:read"})
     *
     * @ORM\Column(name="vl_nibf", type="integer", nullable=true)
     */
    private ?int $nibf = null;

    /**
     * @Assert\Type("integer")
     *
     * @Groups({"sprint:read"})
     *
     * @ORM\Column(name="vl_nibp", type="integer", nullable=true)
     */
    private ?int $nibp = null;

    /**
     * @Assert\Type("float")
     *
     * @Groups({"sprint:read"})
     *
     * @ORM\Column(name="vl_iq", type="float", nullable=true)
     */
    private ?float $iq = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function updateTimestamps(): void
    {
        $this->setAtualizadoEm(new \DateTimeImmutable('now', new \DateTimeZone('UTC')));

        if (null === $this->getCriadoEm()) {
            $this->setCriadoEm(new \DateTimeImmutable('now', new \DateTimeZone('UTC')));
        }
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

    public function getProjeto(): ?Projeto
    {
        return $this->projeto;
    }

    public function setProjeto(?Projeto $projeto): self
    {
        $this->projeto = $projeto;

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

    public function getIsAtiva(): bool
    {
        return $this->isAtiva;
    }

    public function setIsAtiva(bool $isAtiva): self
    {
        $this->isAtiva = $isAtiva;

        return $this;
    }

    public function getIniciadaEm(): ?\DateTimeInterface
    {
        return $this->iniciadaEm;
    }

    public function setIniciadaEm(\DateTimeInterface $iniciadaEm): self
    {
        $this->iniciadaEm = $iniciadaEm;

        return $this;
    }

    public function getFinalizadaEm(): ?\DateTimeInterface
    {
        return $this->finalizadaEm;
    }

    public function setFinalizadaEm(\DateTimeInterface $finalizadaEm): self
    {
        $this->finalizadaEm = $finalizadaEm;

        return $this;
    }

    public function getIsFechada(): ?bool
    {
        return $this->isFechada;
    }

    public function setIsFechada(bool $isFechada): self
    {
        $this->isFechada = $isFechada;

        return $this;
    }

    public function getNspe(): ?float
    {
        return $this->nspe;
    }

    public function setNspe(?float $nspe): self
    {
        $this->nspe = $nspe;

        return $this;
    }

    public function getNspp(): ?float
    {
        return $this->nspp;
    }

    public function setNspp(?float $nspp): self
    {
        $this->nspp = $nspp;

        return $this;
    }

    public function getIp(): ?float
    {
        return $this->ip;
    }

    public function setIp(?float $ip): self
    {
        $this->ip = $ip;

        return $this;
    }

    public function getNibf(): ?int
    {
        return $this->nibf;
    }

    public function setNibf(?int $nibf): self
    {
        $this->nibf = $nibf;

        return $this;
    }

    public function getNibp(): ?int
    {
        return $this->nibp;
    }

    public function setNibp(?int $nibp): self
    {
        $this->nibp = $nibp;

        return $this;
    }

    public function getIq(): ?float
    {
        return $this->iq;
    }

    public function setIq(?float $iq): self
    {
        $this->iq = $iq;

        return $this;
    }
}

<?php

declare(strict_types=1);

namespace App\EntityListener;

use App\Entity\Sprint;
use App\Taiga\V1\Collection\UserstoryCustomAttributeCollection;
use App\Taiga\V1\Model\MilestoneModel;
use App\Taiga\V1\Model\UserstoryCustomAttributeModel;
use App\Taiga\V1\TaigaInterface;
use Doctrine\ORM\Mapping as ORM;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SprintEntityListener
{
    public const SIM_REGEXP = '#sim#i';
    public const FINALIZADO_REGEXP = '#finalizado#i';
    public const BACKLOG_FALHOU_REGEXP = '#backlog.+falhou#i';

    public function __construct(private TaigaInterface $taiga, private ValidatorInterface $validator, private LoggerInterface $logger)
    {
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function onPrePersistOrUpdate(Sprint $sprint): void
    {
        $this->gerarIndices($sprint);
        $sprint->updateTimestamps();
    }

    private function gerarIndices(Sprint $sprint): void
    {
        if ($sprint->getIsFechada()) {
            // Era aberta e fechou
            $milestone = $this->taiga->getMilestoneById((int) $sprint->getTaigaId());
            $userstoryCustomAttributes = $this->taiga->getUserstoryCustomAttributeByProjectId((int) $sprint->getProjeto()?->getTaigaId());
            $this->calcularIndices($sprint, $milestone, $userstoryCustomAttributes);

            $this->logger->debug('Índices da sprint gerados.', [
                'taiga_id' => $sprint->getTaigaId(),
                'nome' => $sprint->getNome(),
            ]);

            return;
        }

        // Era fechada e abriu
        $this->resetarIndices($sprint);

        $this->logger->debug('Índices da sprint foram resetados.', [
            'taiga_id' => $sprint->getTaigaId(),
            'nome' => $sprint->getNome(),
        ]);
    }

    private function resetarIndices(Sprint $sprint): void
    {
        $sprint
            ->setNspp(null)
            ->setNspe(null)
            ->setIp(null)

            ->setNibf(null)
            ->setNibp(null)
            ->setIq(null)
            ;
    }

    /**
     * @param UserstoryCustomAttributeCollection<UserstoryCustomAttributeModel> $userstoryCustomAttributes
     */
    private function calcularIndices(Sprint $sprint, MilestoneModel $milestone, UserstoryCustomAttributeCollection $userstoryCustomAttributes): void
    {
        if (0.0 === $nspp = $milestone->getTotalPoints()) {
            $message = sprintf('Impossível calcular o IP da sprint "%s", pois NSPP é igual a zero.', $sprint->getNome());
            $this->logger->emergency($message);

            throw new \LogicException($message);
        }

        if (null === $backlogFalhouCustomAttribute = $userstoryCustomAttributes->findByRegExp(self::BACKLOG_FALHOU_REGEXP)) {
            $projeto = $sprint->getProjeto();
            $message = sprintf(
                'Impossível calcular o IQ da sprint "%s" (taiga_id=%d) do projeto "%s" (taiga_id=%d), pois o campo personalizado que indica se a história de usuário foi rejeitada na sprint anterior está ausente.',
                $sprint->getNome(),
                $sprint->getTaigaId(),
                $projeto?->getNome(),
                $projeto?->getTaigaId()
            );
            $this->logger->emergency($message);

            throw new \LogicException($message);
        }

        if (null === $finalizadoNaSprintCustomAttribute = $userstoryCustomAttributes->findByRegExp(self::FINALIZADO_REGEXP)) {
            $projeto = $sprint->getProjeto();
            $message = sprintf(
                'Impossível calcular o IP da sprint "%s" (taiga_id=%d) do projeto "%s" (taiga_id=%d), pois o campo personalizado que indica se a história de usuário foi finalizada na sprint está ausente.',
                $sprint->getNome(),
                $sprint->getTaigaId(),
                $projeto?->getNome(),
                $projeto?->getTaigaId()
            );
            $this->logger->emergency($message);

            throw new \LogicException($message);
        }

        $backlogRejeitadoAttributeId = $backlogFalhouCustomAttribute->getId();
        $finalizadoNaSprintAttributeId = $finalizadoNaSprintCustomAttribute->getId();

        $nspe = 0.0;
        $nibf = 0;

        foreach ($milestone->getUserStories() as $userStory) {
            $userstoryCustomAttributesValues = $this->taiga->getCustomAttributesValuesByUserStoryId($userStory->getId());
            if (null === $backlogRejeitadoAttributeValue = $userstoryCustomAttributesValues->findById($backlogRejeitadoAttributeId)) {
                $message = sprintf('Impossível calcular o IQ da sprint "%s", pois o campo personalizado que indica se a história de usuário "%s" foi rejeitada na sprint anterior não foi respondido.', $sprint->getNome(), $userStory->getSubject());
                $this->logger->emergency($message);

                throw new \LogicException($message);
            }

            if (null === $finalizadoNaSprintAttributeValue = $userstoryCustomAttributesValues->findById($finalizadoNaSprintAttributeId)) {
                $message = sprintf('Impossível calcular o IP da sprint "%s", pois o campo personalizado que indica se a história de usuário "%s" foi finalizada não foi respondido.', $sprint->getNome(), $userStory->getSubject());
                $this->logger->emergency($message);

                throw new \LogicException($message);
            }

            if (preg_match(self::SIM_REGEXP, $finalizadoNaSprintAttributeValue->getValue())) {
                $nspe += $userStory->getTotalPoints();
            }

            if (preg_match(self::SIM_REGEXP, $backlogRejeitadoAttributeValue->getValue())) {
                ++$nibf;
            }
        }

        $ip = $nspe / $nspp;
        $ip = min(1.0, $ip);

        $sprint
            ->setNspp($nspp)
            ->setNspe($nspe)
            ->setIp($ip)
            ;

        $nibp = count($milestone->getUserStories());
        $iq = 1 - ($nibf / $nibp);
        $iq = min(1.0, $iq);

        $sprint
            ->setNibf($nibf)
            ->setNibp($nibp)
            ->setIq($iq)
            ;

        if (0 < count($violations = $this->validator->validate($sprint))) {
            $violationsAsString = '';
            foreach ($violations as $violation) {
                $violationsAsString = sprintf('%s%s: %s%s', $violationsAsString, $violation->getPropertyPath(), $violation->getMessage(), PHP_EOL);
            }

            $this->logger->emergency($violationsAsString, ['violations' => $violationsAsString]);

            throw new \InvalidArgumentException($violationsAsString);
        }
    }
}

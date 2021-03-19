<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\OrdemServico;
use App\Entity\Projeto;
use App\Entity\Sprint;
use App\Taiga\V1\Exception\MaisDeUmaTagEncontradaException;
use App\Taiga\V1\Exception\NenhumaTagEncontradaException;
use App\Taiga\V1\TaigaInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @TODO Utilizar Symfony Messenger
 *
 * @Route("/taiga-webhook", methods={"POST"})
 */
class TaigaWebhookController
{
    public function __construct(
        private string $taigaWebhookSecretKey,
        private TaigaInterface $taiga,
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator,
        private LoggerInterface $logger
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $content = (string) $request->getContent();
        $signature = $request->headers->get('X-Taiga-Webhook-Signature') ?? '';

        if (!$this->isSignatureValid($signature, $content)) {
            $this->logger->emergency('Assinatura inválida.');

            throw new BadRequestHttpException('Assinatura inválida.');
        }

        $payload = json_decode($content, true);
        if (JSON_ERROR_NONE !== json_last_error()) {
            $this->logger->emergency('JSON inválido.');

            throw new BadRequestHttpException('JSON inválido.');
        }

        $this->logger->debug('Payload decodificado.', [
            'payload' => $payload,
        ]);

        if (!isset($payload['data'], $payload['type'], $payload['action'])) {
            $this->logger->debug('Campo "data" e/ou "type" e/ou "action" ausente.');

            throw new BadRequestHttpException(sprintf('O campo "data" e/ou "type" e/ou "action" ausente.'));
        }

        $data = $payload['data'];

        if (!in_array($payload['type'], ['milestone', 'userstory', 'task'])) {
            $this->logger->debug('Tipo não suportado.', ['type' => $payload['type']]);

            throw new BadRequestHttpException(sprintf('Tipo "%s" não suportado.', $payload['type']));
        }

        if (!in_array($payload['action'], ['test', 'create', 'change', 'delete'])) {
            $this->logger->debug('Ação não suportada.', ['action' => $payload['action']]);

            throw new BadRequestHttpException(sprintf('Ação "%s" não suportada.', $payload['action']));
        }

        if ('create' === $payload['action'] && 'task' === $payload['type']) {
            if (
                null !== $data['milestone']
                && (null !== $sprint = $this->entityManager->getRepository(Sprint::class)->findOneByTaigaId($data['milestone']['id']))
                && $sprint->getIsFechada()
            ) {
                $sprint->setIsFechada(false);
                $this->entityManager->flush();

                $this->logger->info('Status de fechada da sprint foi atualizado.', [
                    'taiga_id' => $sprint->getTaigaId(),
                    'is_fechada' => $sprint->getIsFechada(),
                    'motivo' => 'Tarefa foi criada no Taiga',
                ]);
            }
        } elseif ('change' === $payload['action']) {
            if (
                'milestone' === $payload['type']
                && (null !== $sprint = $this->entityManager->getRepository(Sprint::class)->findOneByTaigaId($data['id']))
            ) {
                $sprint
                    ->setNome($data['name'])
                    ->setIniciadaEm($this->getDateTimeWithTzFromDateString($data['estimated_start']))
                    ->setFinalizadaEm($this->getDateTimeWithTzFromDateString($data['estimated_finish']))
                    ;

                $this->entityManager->flush();

                $this->logger->info('Sprint modificada.', [
                    'taiga_id' => $sprint->getTaigaId(),
                    'nome' => $sprint->getNome(),
                    'motivo' => 'Sprint foi modificada no Taiga',
                ]);
            } elseif (('userstory' === $payload['type'] || 'task' === $payload['type']) && null !== $data['milestone']) {
                if (
                    (null !== $sprint = $this->entityManager->getRepository(Sprint::class)->findOneByTaigaId($data['milestone']['id']))
                    && $data['milestone']['closed'] !== $sprint->getIsFechada()
                ) {
                    $sprint->setIsFechada($data['milestone']['closed']);
                    $this->entityManager->flush();

                    $this->logger->info('Status de fechada da sprint foi atualizado.', [
                        'taiga_id' => $sprint->getTaigaId(),
                        'is_fechada' => $sprint->getIsFechada(),
                        'motivo' => 'História de usuário ou tarefa foi modificada no Taiga',
                    ]);
                } elseif ($data['milestone']['closed']) {
                    $projeto = $this->findOrCreateProject($data['project']['id']);
                    $sprint = $this->createSprint(
                        $data['milestone']['id'],
                        $data['milestone']['name'],
                        $data['milestone']['closed'],
                        $data['milestone']['estimated_start'],
                        $data['milestone']['estimated_finish'],
                        $projeto
                    );

                    $this->entityManager->persist($sprint);
                    $this->entityManager->flush();

                    $this->logger->info('Sprint criada.', [
                        'taiga_id' => $sprint->getTaigaId(),
                        'is_fechada' => $sprint->getIsFechada(),
                        'motivo' => 'História de usuário ou tarefa foi modificada no Taiga',
                    ]);
                }
            }
        } elseif ('delete' === $payload['action']) {
            if (
                'milestone' === $payload['type']
                && (null !== $sprint = $this->entityManager->getRepository(Sprint::class)->findOneByTaigaId($data['id']))
            ) {
                $sprint->setIsAtiva(false);
                $this->entityManager->flush();

                $this->logger->info('Sprint desativada.', [
                    'taiga_id' => $sprint->getTaigaId(),
                    'motivo' => 'Sprint foi excluída no Taiga',
                ]);
            } elseif (('userstory' === $payload['type'] || 'task' === $payload['type']) && null !== $data['milestone']) {
                if (
                    (null !== $sprint = $this->entityManager->getRepository(Sprint::class)->findOneByTaigaId($data['milestone']['id']))
                    && $data['milestone']['closed'] !== $sprint->getIsFechada()
                ) {
                    $sprint->setIsFechada($data['milestone']['closed']);
                    $this->entityManager->flush();

                    $this->logger->info('Status de fechada da sprint foi atualizado.', [
                        'taiga_id' => $sprint->getTaigaId(),
                        'is_fechada' => $sprint->getIsFechada(),
                        'motivo' => 'História de usuário ou tarefa foi excluída no Taiga',
                    ]);
                }
            }
        }

        return new Response('OK');
    }

    private function createSprint(int $id, string $name, bool $closed, string $estimatedStart, string $estimatedFinish, Projeto $projeto): Sprint
    {
        $sprint = new Sprint();
        $sprint
            ->setTaigaId($id)
            ->setNome($name)
            ->setIsFechada($closed)
            ->setIniciadaEm($this->getDateTimeWithTzFromDateString($estimatedStart))
            ->setFinalizadaEm($this->getDateTimeWithTzFromDateString($estimatedFinish))
            ->setProjeto($projeto)
            ;

        if (0 < count($violations = $this->validator->validate($sprint))) {
            $violationsAsString = $this->violationsToString($violations);
            $message = sprintf('A entidade "%s" não suporta os valores vindos da sprint do Taiga. '
                .'Compare os mapeamentos dessa entidade e os dados que vieram do Taiga.', Sprint::class);
            $this->logger->emergency($message, ['violations' => $violationsAsString]);

            throw new UnprocessableEntityHttpException($violationsAsString);
        }

        return $sprint;
    }

    private function findOrCreateProject(int $id): Projeto
    {
        $repository = $this->entityManager->getRepository(Projeto::class);
        if (null === $projeto = $repository->findOneByTaigaId($id)) {
            $projeto = $this->createProject($id);

            $this->entityManager->persist($projeto);
        }

        return $projeto;
    }

    private function createProject(int $id): Projeto
    {
        $this->logger->info('Buscando mais informações sobre o projeto no Taiga.', ['id' => $id]);

        $project = $this->taiga->getProjectById($id);
        $tagsParser = $project->createTagsParser();

        try {
            $ordemServicoTaigaTag = $tagsParser->getNomeOrdemServico();
        } catch (NenhumaTagEncontradaException $e) {
            $ordemServicoTaigaTag = null;
        } catch (MaisDeUmaTagEncontradaException $e) {
            $message = sprintf('Mais de uma tag de OS cadastrada no projeto: "%s".', implode(', ', $e->getFilteredTags()));
            $this->logger->emergency($message);

            throw new \RuntimeException($message);
        }

        $ordemServico = $this->entityManager->getRepository(OrdemServico::class)
            ->findOneByTaigaTag($ordemServicoTaigaTag);

        $projeto = new Projeto();
        $projeto
            ->setTaigaId($project->getId())
            ->setNome($project->getName())
            ->setDescricao($project->getDescription())
            ->setOrdemServico($ordemServico)
            ;

        if (0 < count($violations = $this->validator->validate($projeto))) {
            $violationsAsString = $this->violationsToString($violations);
            $message = sprintf('A entidade "%s" não suporta os valores vindos do projeto do Taiga. '
                .'Compare os mapeamentos dessa entidade e os dados que vieram do Taiga.', Projeto::class);
            $this->logger->emergency($message, ['violations' => $violationsAsString]);

            throw new UnprocessableEntityHttpException($violationsAsString);
        }

        return $projeto;
    }

    private function isSignatureValid(string $signature, string $content): bool
    {
        return hash_hmac('sha1', $content, $this->taigaWebhookSecretKey) === $signature;
    }

    /**
     * @param ConstraintViolationListInterface<ConstraintViolationInterface> $violations
     */
    private function violationsToString(ConstraintViolationListInterface $violations): string
    {
        $violationsAsString = '';
        foreach ($violations as $violation) {
            $violationsAsString = sprintf('%s%s: %s%s', $violationsAsString, $violation->getPropertyPath(), $violation->getMessage(), PHP_EOL);
        }

        return $violationsAsString;
    }

    private function getDateTimeWithTzFromDateString(string $date): \DateTimeInterface
    {
        // Como o Taiga não nos informa o timezone, temos que supor que seja America/Sao_Paulo
        $datetime = new \DateTime(sprintf('%sT00:00:00', $date), new \DateTimeZone('America/Sao_Paulo'));

        // Convertendo para UTC porque nós guardamos o valor da seguinte forma:
        // dh_finalizada_em TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL
        $datetime->setTimeZone(new \DateTimeZone('UTC'));

        return $datetime;
    }
}

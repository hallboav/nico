<?php

declare(strict_types=1);

namespace App\Tests\Funcional\Controller;

use App\Entity\Projeto;
use App\Entity\Sprint;
use App\Taiga\V1\Collection\CustomAttributeValueCollection;
use App\Taiga\V1\Collection\UserstoryCustomAttributeCollection;
use App\Taiga\V1\MockTaiga;
use App\Taiga\V1\Model\MilestoneModel;
use App\Taiga\V1\Model\ProjectModel;
use App\Taiga\V1\TaigaInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DomCrawler\Crawler;

class TaigaWebhookControllerTest extends KernelTestCase
{
    private $entityManager;
    private $browserClient;

    protected function setUp(): void
    {
        self::bootKernel();

        $container = static::$kernel->getContainer();
        $this->browserClient = $container->get('test.client');

        $doctrine = $container->get('doctrine');
        $this->entityManager = $doctrine->getManager();

        $doctrine->getConnection()->beginTransaction();
    }

    public function tearDown(): void
    {
        $connection = static::$kernel->getContainer()->get('doctrine')->getConnection();
        if ($connection->isTransactionActive()) {
            $connection->rollback();
        }

        parent::tearDown();

        $this->entityManager->close();
        $this->entityManager = null;
    }

    public function testTarefaCriadaNoTaigaComSprintFechadaExistente(): void
    {
        $this->createSprint(isClosed: true);

        $content = <<<'PAYLOAD'
{
    "action": "create",
    "type": "task",
    "data": {
        "milestone": {
            "id": 115,
            "closed": false
        }
    }
}
PAYLOAD;

        $crawler = $this->sendWebhookRequest($content);
        $this->assertEquals('OK', $crawler->text());

        $sprint = $this->findSprintByTaigaId(115);
        $this->assertFalse($sprint->getIsFechada());
        $this->assertNull($sprint->getIp());
    }

    public function testSprintExistenteFoiModificadaNoTaiga(): void
    {
        $this->createSprint();

        $content = <<<'PAYLOAD'
{
    "action": "change",
    "type": "milestone",
    "data": {
        "id": 115,
        "name": "Foobarbaz",
        "estimated_start": "2021-01-01",
        "estimated_finish": "2021-02-01"
    }
}
PAYLOAD;

        $crawler = $this->sendWebhookRequest($content);
        $this->assertEquals('OK', $crawler->text());

        $sprint = $this->findSprintByTaigaId(115);
        $this->assertEquals('Foobarbaz', $sprint->getNome());
        $this->assertEquals('2021-01-01T03:00:00+00:00', $sprint->getIniciadaEm()->format(\DateTime::RFC3339));
        $this->assertEquals('2021-02-01T03:00:00+00:00', $sprint->getFinalizadaEm()->format(\DateTime::RFC3339));
    }

    public function testTarefaOuHistoriaUsuarioPertencenteAUmaSprintFoiFechadaNoTaiga(): void
    {
        $this->createSprint();

        $content = <<<'PAYLOAD'
{
    "action": "change",
    "type": "task",
    "data": {
        "milestone": {
            "id": 115,
            "closed": true
        }
    }
}
PAYLOAD;

        $crawler = $this->sendWebhookRequest($content);
        $this->assertEquals('OK', $crawler->text());

        $sprint = $this->findSprintByTaigaId(115);
        $this->assertTrue($sprint->getIsFechada());
        $this->assertNotNull($sprint->getIp());
    }

    public function testTarefaOuHistoriaUsuarioPertencenteAUmaSprintFoiAbertaNoTaiga(): void
    {
        $this->createSprint(isClosed: true);

        $content = <<<'PAYLOAD'
{
    "action": "change",
    "type": "task",
    "data": {
        "milestone": {
            "id": 115,
            "closed": false
        }
    }
}
PAYLOAD;

        $crawler = $this->sendWebhookRequest($content);
        $this->assertEquals('OK', $crawler->text());

        $sprint = $this->findSprintByTaigaId(115);
        $this->assertFalse($sprint->getIsFechada());
        $this->assertNull($sprint->getIp());
    }

    public function testTarefaOuHistoriaUsuarioNaoPertencenteAUmaSprintFoiModificadaNoTaiga(): void
    {
        // 39 é o ID do projeto dos arquitetos nos Taiga
        $content = <<<'PAYLOAD'
{
    "action": "change",
    "type": "userstory",
    "data": {
        "project": {
            "id": 39
        },
        "milestone": {
            "id": 115,
            "name": "foo",
            "closed": true,
            "estimated_start": "2021-01-01",
            "estimated_finish": "2021-02-01"
        }
    }
}
PAYLOAD;

        $crawler = $this->sendWebhookRequest($content);
        $this->assertEquals('OK', $crawler->text());

        $sprint = $this->findSprintByTaigaId(115);
        $this->assertTrue($sprint->getIsFechada());
        $this->assertNotNull($sprint->getIp());
    }

    public function testDeletarMilestone(): void
    {
        $this->createSprint(isClosed: true);

        $content = <<<'PAYLOAD'
{
    "action": "delete",
    "type": "milestone",
    "data": {
        "id": 115,
        "closed": false
    }
}
PAYLOAD;

        $crawler = $this->sendWebhookRequest($content);
        $this->assertEquals('OK', $crawler->text());

        $sprint = $this->findSprintByTaigaId(115);
        $this->assertFalse($sprint->getIsAtiva());
    }

    public function testDeletarTarefaOuHistoriaDeUsuarioPertencenteAUmaSprintEIssoAlterarStatusDeFechada(): void
    {
        $this->createSprint();

        $content = <<<'PAYLOAD'
{
    "action": "delete",
    "type": "task",
    "data": {
        "milestone": {
            "id": 115,
            "closed": true
        }
    }
}
PAYLOAD;

        $crawler = $this->sendWebhookRequest($content);
        $this->assertEquals('OK', $crawler->text());

        $sprint = $this->findSprintByTaigaId(115);
        $this->assertTrue($sprint->getIsFechada());
        $this->assertNotNull($sprint->getIp());
    }

    public function testFecharSprintComIqNaoRespondido(): void
    {
        $taigaMock = new class() extends MockTaiga {
            public function getCustomAttributesValuesByUserStoryId(int $id): CustomAttributeValueCollection
            {
                return CustomAttributeValueCollection::fromArray([
                    'attributes_values' => [
                        '71' => 'NÃO',
                    ],
                ]);
            }
        };

        static::$kernel->getContainer()->set(TaigaInterface::class, $taigaMock);

        $this->createSprint();

        $content = <<<'PAYLOAD'
{
    "action": "change",
    "type": "task",
    "data": {
        "milestone": {
            "id": 115,
            "closed": true
        }
    }
}
PAYLOAD;

        $crawler = $this->sendWebhookRequest($content);
        $this->assertEquals(500, $this->browserClient->getResponse()->getStatusCode());
        $this->assertStringContainsString('Impossível calcular o IQ da sprint "foo", pois o campo personalizado que indica se a história de usuário "foo" foi rejeitada na sprint anterior não foi respondido.', $crawler->text());

        $sprint = $this->findSprintByTaigaId(115);
        $this->assertFalse($sprint->getIsFechada());
        $this->assertNull($sprint->getIp());
    }

    public function testFecharSprintComIpNaoRespondido(): void
    {
        $taigaMock = new class() extends MockTaiga {
            public function getCustomAttributesValuesByUserStoryId(int $id): CustomAttributeValueCollection
            {
                return CustomAttributeValueCollection::fromArray([
                    'attributes_values' => [
                        '70' => 'SIM',
                    ],
                ]);
            }
        };

        static::$kernel->getContainer()->set(TaigaInterface::class, $taigaMock);

        $this->createSprint();

        $content = <<<'PAYLOAD'
{
    "action": "change",
    "type": "task",
    "data": {
        "milestone": {
            "id": 115,
            "closed": true
        }
    }
}
PAYLOAD;

        $crawler = $this->sendWebhookRequest($content);
        $this->assertEquals(500, $this->browserClient->getResponse()->getStatusCode());
        $this->assertStringContainsString('Impossível calcular o IP da sprint "foo", pois o campo personalizado que indica se a história de usuário "foo" foi finalizada não foi respondido.', $crawler->text());

        $sprint = $this->findSprintByTaigaId(115);
        $this->assertFalse($sprint->getIsFechada());
        $this->assertNull($sprint->getIp());
    }

    public function testFecharSprintComCampoUsadoParaCalcularIqAusente(): void
    {
        $taigaMock = new class() extends MockTaiga {
            public function getUserstoryCustomAttributeByProjectId(int $id): UserstoryCustomAttributeCollection
            {
                return UserstoryCustomAttributeCollection::fromArray([
                    [
                        'id' => 71,
                        'name' => 'Finalizado na Sprint?',
                    ],
                ]);
            }
        };

        static::$kernel->getContainer()->set(TaigaInterface::class, $taigaMock);

        $this->createSprint();

        $content = <<<'PAYLOAD'
{
    "action": "change",
    "type": "task",
    "data": {
        "milestone": {
            "id": 115,
            "closed": true
        }
    }
}
PAYLOAD;

        $crawler = $this->sendWebhookRequest($content);
        $this->assertEquals(500, $this->browserClient->getResponse()->getStatusCode());
        $this->assertStringContainsString('Impossível calcular o IQ da sprint "foo" (taiga_id=115) do projeto "baz" (taiga_id=39), pois o campo personalizado que indica se a história de usuário foi rejeitada na sprint anterior está ausente.', $crawler->text());

        $sprint = $this->findSprintByTaigaId(115);
        $this->assertFalse($sprint->getIsFechada());
        $this->assertNull($sprint->getIp());
    }

    public function testFecharSprintComCampoUsadoParaCalcularIpAusente(): void
    {
        $taigaMock = new class() extends MockTaiga {
            public function getUserstoryCustomAttributeByProjectId(int $id): UserstoryCustomAttributeCollection
            {
                return UserstoryCustomAttributeCollection::fromArray([
                    [
                        'id' => 70,
                        'name' => 'Item de backlog falhou?',
                    ],
                ]);
            }
        };

        static::$kernel->getContainer()->set(TaigaInterface::class, $taigaMock);

        $this->createSprint();

        $content = <<<'PAYLOAD'
{
    "action": "change",
    "type": "task",
    "data": {
        "milestone": {
            "id": 115,
            "closed": true
        }
    }
}
PAYLOAD;

        $crawler = $this->sendWebhookRequest($content);
        $this->assertEquals(500, $this->browserClient->getResponse()->getStatusCode());
        $this->assertStringContainsString('Impossível calcular o IP da sprint "foo" (taiga_id=115) do projeto "baz" (taiga_id=39), pois o campo personalizado que indica se a história de usuário foi finalizada na sprint está ausente.', $crawler->text());

        $sprint = $this->findSprintByTaigaId(115);
        $this->assertFalse($sprint->getIsFechada());
        $this->assertNull($sprint->getIp());
    }

    public function testFecharSprintComZeroPontosFechados(): void
    {
        $taigaMock = new class() extends MockTaiga {
            public function getMilestoneById(int $id): MilestoneModel
            {
                return MilestoneModel::fromArray([
                    'id' => 115,
                    'total_points' => null,
                    'user_stories' => [
                        [
                            'id' => 1,
                            'subject' => 'foo',
                            'total_points' => null,
                        ],
                        [
                            'id' => 2,
                            'subject' => 'bar',
                            'total_points' => null,
                        ],
                    ],
                ]);
            }
        };

        static::$kernel->getContainer()->set(TaigaInterface::class, $taigaMock);

        $this->createSprint();

        $content = <<<'PAYLOAD'
{
    "action": "change",
    "type": "task",
    "data": {
        "milestone": {
            "id": 115,
            "closed": true
        }
    }
}
PAYLOAD;

        $crawler = $this->sendWebhookRequest($content);
        $this->assertEquals(500, $this->browserClient->getResponse()->getStatusCode());
        $this->assertStringContainsString('Impossível calcular o IP da sprint "foo", pois NSPP é igual a zero.', $crawler->text());

        $sprint = $this->findSprintByTaigaId(115);
        $this->assertFalse($sprint->getIsFechada());
        $this->assertNull($sprint->getIp());
    }

    public function testAssinaturaInvalida(): void
    {
        $content = 'foo';
        $server = [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_TAIGA_WEBHOOK_SIGNATURE' => 'foo',
        ];

        $crawler = $this->browserClient->request('POST', '/taiga-webhook', [], [], $server, $content);

        $this->assertEquals(400, $this->browserClient->getResponse()->getStatusCode());
        $this->assertStringContainsString('Assinatura inválida.', $crawler->text());
    }

    public function testJsonInvalido(): void
    {
        $crawler = $this->sendWebhookRequest('foo');

        $this->assertEquals(400, $this->browserClient->getResponse()->getStatusCode());
        $this->assertStringContainsString('JSON inválido.', $crawler->text());
    }

    public function testCampoDataAusente(): void
    {
        $crawler = $this->sendWebhookRequest('{"type":"foo","action":"bar"}');

        $this->assertEquals(400, $this->browserClient->getResponse()->getStatusCode());
        $this->assertStringContainsString('O campo "data" e/ou "type" e/ou "action" ausente.', $crawler->text());
    }

    public function testCampoTypeAusente(): void
    {
        $crawler = $this->sendWebhookRequest('{"data":"foo","action":"bar"}');

        $this->assertEquals(400, $this->browserClient->getResponse()->getStatusCode());
        $this->assertStringContainsString('O campo "data" e/ou "type" e/ou "action" ausente.', $crawler->text());
    }

    public function testCampoActionAusente(): void
    {
        $crawler = $this->sendWebhookRequest('{"data":"foo","type":"bar"}');

        $this->assertEquals(400, $this->browserClient->getResponse()->getStatusCode());
        $this->assertStringContainsString('O campo "data" e/ou "type" e/ou "action" ausente.', $crawler->text());
    }

    public function testTipoDesconhecido(): void
    {
        $crawler = $this->sendWebhookRequest('{"data":"foo","type":"bar","action":"foo"}');

        $this->assertEquals(400, $this->browserClient->getResponse()->getStatusCode());
        $this->assertStringContainsString('Tipo "bar" não suportado.', $crawler->text());
    }

    public function testActionDesconhecida(): void
    {
        $crawler = $this->sendWebhookRequest('{"data":"foo","type":"milestone","action":"foo"}');

        $this->assertEquals(400, $this->browserClient->getResponse()->getStatusCode());
        $this->assertStringContainsString('Ação "foo" não suportada.', $crawler->text());
    }

    public function testViolationsNoCreateSprint(): void
    {
        $content = <<<'PAYLOAD'
{
    "action": "change",
    "type": "userstory",
    "data": {
        "project": {
            "id": 39
        },
        "milestone": {
            "id": 115,
            "name": "Parthenolatry entomophagan Brunoniaceae gibbousness unauspicious applanation amenability wolveboon idiosyncratical assis tetracosane fastish submergement togetheriness prevalescent Mwa laudist Idoist caridoid mosasauroid nonzoological hipponosology planoorbicular yttrocrasitejungleside Yucatecan agrarian trabeculated zo splitbeak infelonious simian zoopsia orthoceracone tamasha julienite footstep arty alodiality alms unaccountable interbranch foresummer epistaxis fibrotic unpraise decontamination brilliantwisestagily conutrition planklike Nesogaean",
            "closed": true,
            "estimated_start": "2021-01-01",
            "estimated_finish": "2021-02-01"
        }
    }
}
PAYLOAD;

        $crawler = $this->sendWebhookRequest($content);
        $this->assertStringContainsString(<<<VIOLATIONS
nome: This value is too long. It should have 500 characters or less.
VIOLATIONS, $crawler->text());
    }

    public function testViolationsNoCreateProject(): void
    {
        $taigaMock = new class() extends MockTaiga {
            public function getProjectById(int $id): ProjectModel
            {
                return ProjectModel::fromArray([
                    'id' => 123,
                    'name' => 'transmogrify sacrocoxalgia cleanliness uncountenanced crownwork beehouse misopedist overfaith honeysuckle boatbuilder uncongratulate aponeurotome stratonic hemimorph nonbacterial report superciliously knowledgeable photochromic religiousness Fistulana disorchard bungerly Pythagorize commemoratively fancify antithenar disputator plasmoptysis stonecrop decigramme polygamous shipped graminivore importraiture tarlatan remissness intervenience nondependence stodgery mustnt cupbearer dytiscid olivil laspring Charles heterotactic acton',
                    'description' => 'Foo bar',
                    'tags' => ['os 01/2021'],
                ]);
            }
        };

        static::$kernel->getContainer()->set(TaigaInterface::class, $taigaMock);

        $content = <<<'PAYLOAD'
{
    "action": "change",
    "type": "userstory",
    "data": {
        "project": {
            "id": 123
        },
        "milestone": {
            "id": 115,
            "name": "foo",
            "closed": true,
            "estimated_start": "2021-01-01",
            "estimated_finish": "2021-02-01"
        }
    }
}
PAYLOAD;

        $crawler = $this->sendWebhookRequest($content);
        $this->assertStringContainsString(<<<VIOLATIONS
nome: This value is too long. It should have 500 characters or less.
VIOLATIONS, $crawler->text());
    }

    public function testCreateProjectNenhumaTagEncontradaException(): void
    {
        $taigaMock = new class() extends MockTaiga {
            public function getProjectById(int $id): ProjectModel
            {
                return ProjectModel::fromArray([
                    'id' => 39,
                    'name' => 'Foo',
                    'description' => 'Foo bar',
                    'tags' => [],
                ]);
            }
        };

        static::$kernel->getContainer()->set(TaigaInterface::class, $taigaMock);

        $content = <<<'PAYLOAD'
{
    "action": "change",
    "type": "userstory",
    "data": {
        "project": {
            "id": 39
        },
        "milestone": {
            "id": 115,
            "name": "foo",
            "closed": true,
            "estimated_start": "2021-01-01",
            "estimated_finish": "2021-02-01"
        }
    }
}
PAYLOAD;

        $this->sendWebhookRequest($content);
        $this->assertNull($this->entityManager->getRepository(Projeto::class)->findOneByTaigaId(39)->getOrdemServico());
    }

    public function testCreateProjectMaisDeUmaTagEncontradaException(): void
    {
        $taigaMock = new class() extends MockTaiga {
            public function getProjectById(int $id): ProjectModel
            {
                return ProjectModel::fromArray([
                    'id' => 39,
                    'name' => 'Foo',
                    'description' => 'Foo bar',
                    'tags' => ['os 01/2020', 'os 02/2020'],
                ]);
            }
        };

        static::$kernel->getContainer()->set(TaigaInterface::class, $taigaMock);

        $content = <<<'PAYLOAD'
{
    "action": "change",
    "type": "userstory",
    "data": {
        "project": {
            "id": 39
        },
        "milestone": {
            "id": 115,
            "name": "foo",
            "closed": true,
            "estimated_start": "2021-01-01",
            "estimated_finish": "2021-02-01"
        }
    }
}
PAYLOAD;

        $crawler = $this->sendWebhookRequest($content);
        $this->assertStringContainsString('Mais de uma tag de OS cadastrada no projeto: "os 01/2020, os 02/2020".', $crawler->text());
    }

    private function createSprint(
        int $projectId = 39,
        string $projectName = 'baz',
        string $projectDescription = 'bar',
        int $id = 115,
        string $name = 'foo',
        bool $isClosed = false,
        string $estimatedStart = '-3 days',
        string $estimatedFinish = '+3 days'
    ): void {
        $projeto = new Projeto();
        $projeto
            ->setTaigaId($projectId)
            ->setNome($projectName)
            ->setDescricao($projectDescription)
            ;

        $this->entityManager->persist($projeto);

        $sprint = new Sprint();
        $sprint
            ->setTaigaId($id)
            ->setNome($name)
            ->setIsFechada($isClosed)
            ->setIniciadaEm(new \DateTime($estimatedStart, new \DateTimeZone('UTC')))
            ->setFinalizadaEm(new \DateTime($estimatedFinish, new \DateTimeZone('UTC')))
            ->setProjeto($projeto)
            ;

        $this->entityManager->persist($sprint);
        $this->entityManager->flush();
    }

    private function findSprintByTaigaId(int $taigaId): ?Sprint
    {
        return $this->entityManager->getRepository(Sprint::class)->findOneByTaigaId($taigaId);
    }

    private function sendWebhookRequest(string $content): Crawler
    {
        $server = [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_TAIGA_WEBHOOK_SIGNATURE' => $this->sign($content),
        ];

        return $this->browserClient->request('POST', '/taiga-webhook', [], [], $server, $content);
    }

    private function sign(string $content): string
    {
        return hash_hmac('sha1', $content, 'foobar');
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Funcional\Entity;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Projeto;
use App\Entity\Sprint;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Component\VarDumper\Test\VarDumperTestTrait;

class SprintsTest extends ApiTestCase
{
    use RefreshDatabaseTrait;
    use VarDumperTestTrait;

    /**
     * @depends App\Tests\Funcional\LoginTest::testLogin
     */
    public function testGetCollection(string $token): void
    {
        $response = static::createClient()->request('GET', '/sprints', [
            'auth_bearer' => $token,
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        $this->assertMatchesResourceCollectionJsonSchema(Sprint::class);

        $projetoIri = $this->findIriBy(Projeto::class, ['taigaId' => 1]);

        $this->assertJsonContains([
            '@context' => '/contexts/Sprint',
            '@id' => '/sprints',
            '@type' => 'hydra:Collection',
            'hydra:totalItems' => 1,
            'hydra:member' => [
                [
                    'projeto' => $projetoIri,
                    'taiga_id' => 1,
                    'nome' => 'Ducimus soluta accusantium et sed adipisci.',
                    'is_fechada' => true,
                    'nspp' => 5.5,
                    'nspe' => 5.5,
                    'ip' => 1,
                    'nibf' => 1,
                    'nibp' => 2,
                    'iq' => 0.5,
                ],
            ],
        ]);

        $responseAsArray = $response->toArray();
        $members = $responseAsArray['hydra:member'];

        $this->assertArrayHasKey('criado_em', $members[0]);
        $iniciadaEm = \DateTime::createFromFormat(\DateTime::RFC3339, $members[0]['criado_em']);
        $this->assertNotEquals($iniciadaEm, false);
        $this->assertEquals($members[0]['criado_em'], $iniciadaEm->format(\DateTime::RFC3339));

        $this->assertArrayHasKey('atualizado_em', $members[0]);
        $finalizadaEm = \DateTime::createFromFormat(\DateTime::RFC3339, $members[0]['atualizado_em']);
        $this->assertNotEquals($finalizadaEm, false);
        $this->assertEquals($members[0]['atualizado_em'], $finalizadaEm->format(\DateTime::RFC3339));

        $this->assertArrayHasKey('iniciada_em', $members[0]);
        $iniciadaEm = \DateTime::createFromFormat(\DateTime::RFC3339, $members[0]['iniciada_em']);
        $this->assertNotEquals($iniciadaEm, false);
        $this->assertEquals($members[0]['iniciada_em'], $iniciadaEm->format(\DateTime::RFC3339));

        $this->assertArrayHasKey('finalizada_em', $members[0]);
        $finalizadaEm = \DateTime::createFromFormat(\DateTime::RFC3339, $members[0]['finalizada_em']);
        $this->assertNotEquals($finalizadaEm, false);
        $this->assertEquals($members[0]['finalizada_em'], $finalizadaEm->format(\DateTime::RFC3339));
    }

    /**
     * @depends App\Tests\Funcional\LoginTest::testLogin
     */
    public function testGetSingle(string $token): void
    {
        $client = static::createClient();

        $sprintId = static::$container->get('doctrine')
            ->getRepository(Sprint::class)
            ->findOneBy(['nome' => 'Ducimus soluta accusantium et sed adipisci.'])
            ->getId();

        $response = static::createClient()->request('GET', sprintf('/sprints/%d', $sprintId), [
            'auth_bearer' => $token,
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        $this->assertMatchesResourceItemJsonSchema(Sprint::class);
    }

    /**
     * @depends App\Tests\Funcional\LoginTest::testLogin
     */
    public function testFilterByProjeto(string $token): void
    {
        $client = static::createClient();

        $projetoId = static::$container->get('doctrine')
            ->getRepository(Projeto::class)
            ->findOneBy(['taigaId' => 1])
            ->getId();

        static::createClient()->request('GET', sprintf('/sprints?contrato=%d', $projetoId), [
            'auth_bearer' => $token,
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        $this->assertMatchesResourceCollectionJsonSchema(Sprint::class);

        $this->assertJsonContains([
            '@context' => '/contexts/Sprint',
            '@id' => '/sprints',
            '@type' => 'hydra:Collection',
            'hydra:totalItems' => 1,
            'hydra:view' => [
                '@id' => sprintf('/sprints?contrato=%d', $projetoId),
                '@type' => 'hydra:PartialCollectionView',
            ],
        ]);
    }
}

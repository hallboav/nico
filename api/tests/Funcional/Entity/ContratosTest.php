<?php

declare(strict_types=1);

namespace App\Tests\Funcional\Entity;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Contrato;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;

class ContratosTest extends ApiTestCase
{
    use RefreshDatabaseTrait;

    /**
     * @depends App\Tests\Funcional\LoginTest::testLogin
     */
    public function testGetCollection(string $token): void
    {
        $response = static::createClient()->request('GET', '/contratos', [
            'auth_bearer' => $token,
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        $this->assertMatchesResourceCollectionJsonSchema(Contrato::class);

        $this->assertJsonContains([
            '@context' => '/contexts/Contrato',
            '@id' => '/contratos',
            '@type' => 'hydra:Collection',
            'hydra:totalItems' => 1,
            'hydra:member' => [
                [
                    'nome' => 'Saepe at voluptas rerum expedita laboriosam sint.',
                    'cnpj' => '62043545000132',
                    'email' => 'nicolette.haag@windler.info',
                    'nome_preposto' => 'Mrs. Precious Hermann',
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
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Funcional\Entity;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Contrato;
use App\Entity\OrdemServico;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;

class OrdemServicosTest extends ApiTestCase
{
    use RefreshDatabaseTrait;

    /**
     * @depends App\Tests\Funcional\LoginTest::testLogin
     */
    public function testGetCollection(string $token): void
    {
        $response = static::createClient()->request('GET', '/ordem_servicos', [
            'auth_bearer' => $token,
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        $this->assertMatchesResourceCollectionJsonSchema(OrdemServico::class);

        $contratoIri = $this->findIriBy(Contrato::class, ['cnpj' => '62043545000132']);

        $this->assertJsonContains([
            '@context' => '/contexts/OrdemServico',
            '@id' => '/ordem_servicos',
            '@type' => 'hydra:Collection',
            'hydra:totalItems' => 1,
            'hydra:member' => [
                [
                    'contrato' => $contratoIri,
                    'taiga_tag' => 'et',
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

<?php

declare(strict_types=1);

namespace App\Tests\Funcional\Entity;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\OrdemServico;
use App\Entity\Projeto;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;

class ProjetosTest extends ApiTestCase
{
    use RefreshDatabaseTrait;

    /**
     * @depends App\Tests\Funcional\LoginTest::testLogin
     */
    public function testGetCollection(string $token): void
    {
        $response = static::createClient()->request('GET', '/projetos', [
            'auth_bearer' => $token,
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        $this->assertMatchesResourceCollectionJsonSchema(Projeto::class);

        $ordemServicoIri = $this->findIriBy(OrdemServico::class, ['taigaTag' => 'et']);

        $this->assertJsonContains([
            '@context' => '/contexts/Projeto',
            '@id' => '/projetos',
            '@type' => 'hydra:Collection',
            'hydra:totalItems' => 1,
            'hydra:member' => [
                [
                    'ordem_servico' => $ordemServicoIri,
                    'taiga_id' => 1,
                    'nome' => 'Perspiciatis saepe ipsa dolor totam.',
                    'descricao' => 'Quia voluptatum voluptatum officia dolor iure nostrum cum. Esse ad qui ut voluptatem illum. Veniam velit architecto et odio quas. Voluptas vel voluptas fuga dolores atque ipsum alias.',
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

<?php

declare(strict_types=1);

namespace App\Tests\Unitario\Taiga\V1\Endpoint;

use App\Taiga\V1\Collection\UserstoryCustomAttributeCollection;
use App\Taiga\V1\Endpoint\UserstoryCustomAttributesEndpoint;
use App\Taiga\V1\Model\UserModel;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;

class UserstoryCustomAttributesEndpointTest extends TestCase
{
    public function testGetByProjectId(): void
    {
        $userModelMock = $this->createMock(UserModel::class);
        $userModelMock->expects($this->once())->method('getAuthToken')->willReturn('mytokenmytokenmytokenmytokenmytokenmytoken');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('info')
            ->with(
                'Request para buscar userstory custom attributes do projeto enviado.',
                ['id' => 999, 'partial_token' => 'mytoke...ytoken']
            );

        $body = json_encode([
            ['id' => 123, 'name' => 'foo'],
            ['id' => 456, 'name' => 'bar'],
        ]);

        $httpClientMock = new MockHttpClient([new MockResponse($body)]);
        $projectsEndpoint = new UserstoryCustomAttributesEndpoint($httpClientMock, $userModelMock, $logger);
        $projectModel = $projectsEndpoint->getByProjectId(999);
        $this->assertInstanceOf(UserstoryCustomAttributeCollection::class, $projectModel);
    }

    public function testGetByProjectIdException(): void
    {
        $userModelMock = $this->createMock(UserModel::class);
        $userModelMock->expects($this->once())->method('getAuthToken')->willReturn('mytokenmytokenmytokenmytokenmytokenmytoken');

        $body = '{"msg":"foo"}';
        $info = [
            'http_code' => 500,
        ];

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('critical')
            ->with(
                'Falha ao buscar userstory custom attributes do projeto no Taiga.',
                [
                    'id' => 888,
                    'partial_token' => 'mytoke...ytoken',
                    'exception_message' => 'HTTP 500 returned for "https://taiga.cidadania.gov.br/api/v1/userstory-custom-attributes?project=888".',
                    'response_body' => $body,
                ]
            );

        $httpClientMock = new MockHttpClient([new MockResponse($body, $info)]);
        $projectsEndpoint = new UserstoryCustomAttributesEndpoint($httpClientMock, $userModelMock, $logger);

        $this->expectException(HttpExceptionInterface::class);
        $projectsEndpoint->getByProjectId(888);
    }
}

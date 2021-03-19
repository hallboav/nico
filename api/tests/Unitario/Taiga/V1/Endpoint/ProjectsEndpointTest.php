<?php

declare(strict_types=1);

namespace App\Tests\Unitario\Taiga\V1\Endpoint;

use App\Taiga\V1\Endpoint\ProjectsEndpoint;
use App\Taiga\V1\Model\ProjectModel;
use App\Taiga\V1\Model\UserModel;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;

class ProjectsEndpointTest extends TestCase
{
    public function testGetById(): void
    {
        $userModelMock = $this->createMock(UserModel::class);
        $userModelMock->expects($this->once())->method('getAuthToken')->willReturn('mytokenmytokenmytokenmytokenmytokenmytoken');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('info')
            ->with(
                'Request para buscar projeto enviado.',
                ['id' => 999, 'partial_token' => 'mytoke...ytoken']
            );

        $body = json_encode([
            'id' => 123,
            'name' => 'bar',
            'description' => 'foo',
            'tags' => [],
        ]);

        $httpClientMock = new MockHttpClient([new MockResponse($body)]);
        $projectsEndpoint = new ProjectsEndpoint($httpClientMock, $userModelMock, $logger);
        $projectModel = $projectsEndpoint->getById(999);
        $this->assertInstanceOf(ProjectModel::class, $projectModel);
    }

    public function testGetByIdException(): void
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
                'Falha ao buscar projeto no Taiga.',
                [
                    'id' => 888,
                    'partial_token' => 'mytoke...ytoken',
                    'exception_message' => 'HTTP 500 returned for "https://taiga.cidadania.gov.br/api/v1/projects/888".',
                    'response_body' => $body,
                ]
            );

        $httpClientMock = new MockHttpClient([new MockResponse($body, $info)]);
        $projectsEndpoint = new ProjectsEndpoint($httpClientMock, $userModelMock, $logger);

        $this->expectException(HttpExceptionInterface::class);
        $projectsEndpoint->getById(888);
    }
}

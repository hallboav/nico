<?php

declare(strict_types=1);

namespace App\Tests\Unitario\Taiga\V1\Endpoint;

use App\Taiga\V1\Endpoint\MilestonesEndpoint;
use App\Taiga\V1\Model\MilestoneModel;
use App\Taiga\V1\Model\UserModel;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;

class MilestonesEndpointTest extends TestCase
{
    public function testGetById(): void
    {
        $userModelMock = $this->createMock(UserModel::class);
        $userModelMock->expects($this->once())->method('getAuthToken')->willReturn('mytokenmytokenmytokenmytokenmytokenmytoken');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('info')
            ->with(
                'Request para buscar sprint enviado.',
                ['id' => 999, 'partial_token' => 'mytoke...ytoken']
            );

        $body = json_encode([
            'id' => 123,
            'total_points' => 30.0,
            'user_stories' => [
                [
                    'id' => 456,
                    'subject' => 'baz',
                    'total_points' => 30.0,
                ],
            ],
        ]);

        $httpClientMock = new MockHttpClient([new MockResponse($body)]);
        $milestonesEndpoint = new MilestonesEndpoint($httpClientMock, $userModelMock, $logger);
        $milestoneModel = $milestonesEndpoint->getById(999);
        $this->assertInstanceOf(MilestoneModel::class, $milestoneModel);
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
                'Falha ao buscar sprint no Taiga.',
                [
                    'id' => 888,
                    'partial_token' => 'mytoke...ytoken',
                    'exception_message' => 'HTTP 500 returned for "https://taiga.cidadania.gov.br/api/v1/milestones/888".',
                    'response_body' => $body,
                ]
            );

        $httpClientMock = new MockHttpClient([new MockResponse($body, $info)]);
        $milestonesEndpoint = new MilestonesEndpoint($httpClientMock, $userModelMock, $logger);

        $this->expectException(HttpExceptionInterface::class);
        $milestonesEndpoint->getById(888);
    }
}

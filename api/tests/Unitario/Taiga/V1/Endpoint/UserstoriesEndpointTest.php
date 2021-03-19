<?php

declare(strict_types=1);

namespace App\Tests\Unitario\Taiga\V1\Endpoint;

use App\Taiga\V1\Collection\CustomAttributeValueCollection;
use App\Taiga\V1\Endpoint\UserstoriesEndpoint;
use App\Taiga\V1\Model\UserModel;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;

class UserstoriesEndpointTest extends TestCase
{
    public function testGetCustomAttributesValuesByUserStoryId(): void
    {
        $userModelMock = $this->createMock(UserModel::class);
        $userModelMock->expects($this->once())->method('getAuthToken')->willReturn('mytokenmytokenmytokenmytokenmytokenmytoken');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('info')
            ->with(
                'Request para buscar custom attributes values enviado.',
                ['id' => 999, 'partial_token' => 'mytoke...ytoken']
            );

        $body = json_encode([
            'attributes_values' => [
                '123' => 'foo',
            ],
        ]);

        $httpClientMock = new MockHttpClient([new MockResponse($body)]);
        $userstoriesEndpoint = new UserstoriesEndpoint($httpClientMock, $userModelMock, $logger);
        $customAttributeValueCollection = $userstoriesEndpoint->getCustomAttributesValuesByUserStoryId(999);
        $this->assertInstanceOf(CustomAttributeValueCollection::class, $customAttributeValueCollection);
    }

    public function testGetCustomAttributesValuesByUserStoryIdException(): void
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
                'Falha ao buscar custom attributes values no Taiga.',
                [
                    'id' => 888,
                    'partial_token' => 'mytoke...ytoken',
                    'exception_message' => 'HTTP 500 returned for "https://taiga.cidadania.gov.br/api/v1/userstories/custom-attributes-values/888".',
                    'response_body' => $body,
                ]
            );

        $httpClientMock = new MockHttpClient([new MockResponse($body, $info)]);
        $userstoriesEndpoint = new UserstoriesEndpoint($httpClientMock, $userModelMock, $logger);

        $this->expectException(HttpExceptionInterface::class);
        $userstoriesEndpoint->getCustomAttributesValuesByUserStoryId(888);
    }
}

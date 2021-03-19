<?php

declare(strict_types=1);

namespace App\Tests\Unitario\Taiga\V1\Endpoint;

use App\Taiga\V1\Endpoint\AuthenticationEndpoint;
use App\Taiga\V1\Model\UserModel;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;

class AuthenticationEndpointTest extends TestCase
{
    public function testAuthenticate(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('info')
            ->with('Autenticação enviada.', ['username' => 'foo']);

        $body = json_encode([
            'auth_token' => 'baz',
        ]);

        $httpClientMock = new MockHttpClient([new MockResponse($body)]);
        $authenticationEndpoint = new AuthenticationEndpoint($httpClientMock, $logger);
        $userModel = $authenticationEndpoint->authenticate('foo', 'bar');
        $this->assertInstanceOf(UserModel::class, $userModel);
    }

    public function testAuthenticateException(): void
    {
        $body = '{"msg":"foo"}';
        $info = [
            'http_code' => 500,
        ];

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('critical')
            ->with(
                'Falha na autenticação com o Taiga.',
                [
                    'username' => 'foo',
                    'exception_message' => 'HTTP 500 returned for "https://taiga.cidadania.gov.br/api/v1/auth".',
                    'response_body' => $body,
                ]
            );

        $httpClientMock = new MockHttpClient([new MockResponse($body, $info)]);
        $authenticationEndpoint = new AuthenticationEndpoint($httpClientMock, $logger);

        $this->expectException(HttpExceptionInterface::class);
        $authenticationEndpoint->authenticate('foo', 'bar');
    }
}

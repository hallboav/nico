<?php

declare(strict_types=1);

namespace App\Taiga\V1\Endpoint;

use App\Taiga\V1\Model\UserModel;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AuthenticationEndpoint implements AuthenticationEndpointInterface
{
    public function __construct(private HttpClientInterface $httpClient, private LoggerInterface $logger)
    {
    }

    public function authenticate(string $username, string $password): UserModel
    {
        $response = $this->httpClient->request('POST', 'https://taiga.cidadania.gov.br/api/v1/auth', [
             'json' => [
                'type' => 'normal',
                'username' => $username,
                'password' => $password,
             ],
        ]);

        $this->logger->info('Autenticação enviada.', ['username' => $username]);

        try {
            /**
             * @var array{'auth_token': string}
             */
            $responseAsArray = $response->toArray();
        } catch (HttpExceptionInterface $e) {
            $this->logger->critical('Falha na autenticação com o Taiga.', [
                'username' => $username,
                'exception_message' => $e->getMessage(),
                'response_body' => ($response = $e->getResponse())->getContent(false),
            ]);

            throw $e;
        }

        return UserModel::fromArray($responseAsArray);
    }
}

<?php

declare(strict_types=1);

namespace App\Taiga\V1\Endpoint;

use App\Taiga\V1\Collection\UserstoryCustomAttributeCollection;
use App\Taiga\V1\Model\UserModel;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class UserstoryCustomAttributesEndpoint implements UserstoryCustomAttributesEndpointInterface
{
    public function __construct(private HttpClientInterface $httpClient, private UserModel $user, private LoggerInterface $logger)
    {
    }

    public function getByProjectId(int $id): UserstoryCustomAttributeCollection
    {
        $response = $this->httpClient->request('GET', sprintf('https://taiga.cidadania.gov.br/api/v1/userstory-custom-attributes?project=%d', $id), [
            'auth_bearer' => $authToken = $this->user->getAuthToken(),
        ]);

        $partialToken = sprintf('%s...%s', substr($authToken, 0, 6), substr($authToken, -6));
        $this->logger->info('Request para buscar userstory custom attributes do projeto enviado.', [
            'id' => $id,
            'partial_token' => $partialToken,
        ]);

        try {
            /**
             * @var array{'id': int,'name': string}[]
             */
            $responseAsArray = $response->toArray();
        } catch (HttpExceptionInterface $e) {
            $this->logger->critical('Falha ao buscar userstory custom attributes do projeto no Taiga.', [
                'id' => $id,
                'partial_token' => $partialToken,
                'exception_message' => $e->getMessage(),
                'response_body' => ($response = $e->getResponse())->getContent(false),
            ]);

            throw $e;
        }

        return UserstoryCustomAttributeCollection::fromArray($responseAsArray);
    }
}

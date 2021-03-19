<?php

declare(strict_types=1);

namespace App\Taiga\V1\Endpoint;

use App\Taiga\V1\Collection\CustomAttributeValueCollection;
use App\Taiga\V1\Model\UserModel;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class UserstoriesEndpoint implements UserstoriesEndpointInterface
{
    public function __construct(private HttpClientInterface $httpClient, private UserModel $user, private LoggerInterface $logger)
    {
    }

    public function getCustomAttributesValuesByUserStoryId(int $id): CustomAttributeValueCollection
    {
        $response = $this->httpClient->request('GET', sprintf('https://taiga.cidadania.gov.br/api/v1/userstories/custom-attributes-values/%d', $id), [
            'auth_bearer' => $authToken = $this->user->getAuthToken(),
        ]);

        $partialToken = sprintf('%s...%s', substr($authToken, 0, 6), substr($authToken, -6));
        $this->logger->info('Request para buscar custom attributes values enviado.', [
            'id' => $id,
            'partial_token' => $partialToken,
        ]);

        try {
            /**
             * @var array{'attributes_values': array<int|string, string>}
             */
            $responseAsArray = $response->toArray();
        } catch (HttpExceptionInterface $e) {
            $this->logger->critical('Falha ao buscar custom attributes values no Taiga.', [
                'id' => $id,
                'partial_token' => $partialToken,
                'exception_message' => $e->getMessage(),
                'response_body' => ($response = $e->getResponse())->getContent(false),
            ]);

            throw $e;
        }

        return CustomAttributeValueCollection::fromArray($responseAsArray);
    }
}

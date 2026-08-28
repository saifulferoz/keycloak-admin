<?php

declare(strict_types=1);

namespace Feroz\KeycloakAdmin\Resources;

use Feroz\KeycloakAdmin\Auth\TokenManagerInterface;
use Feroz\KeycloakAdmin\Http\HttpClientInterface;
use Feroz\KeycloakAdmin\Model\Client\ClientRepresentation;
use Feroz\KeycloakAdmin\Model\User\UserRepresentation;
use Feroz\KeycloakAdmin\Query\ClientQuery;

class ClientsResource extends AbstractResource
{
    public function __construct(
        HttpClientInterface $httpClient,
        TokenManagerInterface $tokenManager,
        private readonly string $realm
    ) {
        parent::__construct($httpClient, $tokenManager);
    }

    /**
     * @return list<ClientRepresentation>
     */
    public function list(?ClientQuery $query = null): array
    {
        $queryParams = $query !== null ? $query->toQueryParams() : [];
        $response = $this->sendGet(
            sprintf('/admin/realms/%s/clients', rawurlencode($this->realm)),
            $queryParams
        );

        $data = $response->getData();
        if (!is_array($data)) {
            return [];
        }

        return array_map(
            static fn(array $item) => ClientRepresentation::fromArray($item),
            $data
        );
    }

    public function findByClientId(string $clientId): ?ClientRepresentation
    {
        $clients = $this->list(ClientQuery::create()->withClientId($clientId));
        return $clients[0] ?? null;
    }

    public function get(string $clientUuid): ClientRepresentation
    {
        $response = $this->sendGet(sprintf('/admin/realms/%s/clients/%s', rawurlencode($this->realm), rawurlencode($clientUuid)));
        /** @var array<string, mixed> $data */
        $data = $response->getData();
        return ClientRepresentation::fromArray($data);
    }

    public function create(ClientRepresentation $client): string
    {
        $response = $this->sendPost(
            sprintf('/admin/realms/%s/clients', rawurlencode($this->realm)),
            $client->toArray()
        );

        $createdId = $response->getCreatedResourceId();
        if ($createdId !== null) {
            return $createdId;
        }

        if ($client->clientId !== null) {
            $found = $this->findByClientId($client->clientId);
            if ($found !== null && $found->id !== null) {
                return $found->id;
            }
        }

        return '';
    }

    public function update(string $clientUuid, ClientRepresentation $client): void
    {
        $this->sendPut(
            sprintf('/admin/realms/%s/clients/%s', rawurlencode($this->realm), rawurlencode($clientUuid)),
            $client->toArray()
        );
    }

    public function delete(string $clientUuid): void
    {
        $this->sendDelete(sprintf('/admin/realms/%s/clients/%s', rawurlencode($this->realm), rawurlencode($clientUuid)));
    }

    public function getClientSecret(string $clientUuid): ?string
    {
        $response = $this->sendGet(
            sprintf('/admin/realms/%s/clients/%s/client-secret', rawurlencode($this->realm), rawurlencode($clientUuid))
        );

        $data = $response->getData();
        if (is_array($data) && isset($data['value'])) {
            return (string) $data['value'];
        }

        return null;
    }

    public function regenerateClientSecret(string $clientUuid): string
    {
        $response = $this->sendPost(
            sprintf('/admin/realms/%s/clients/%s/client-secret', rawurlencode($this->realm), rawurlencode($clientUuid))
        );

        $data = $response->getData();
        if (is_array($data) && isset($data['value'])) {
            return (string) $data['value'];
        }

        return '';
    }

    public function getServiceAccountUser(string $clientUuid): UserRepresentation
    {
        $response = $this->sendGet(
            sprintf('/admin/realms/%s/clients/%s/service-account-user', rawurlencode($this->realm), rawurlencode($clientUuid))
        );

        /** @var array<string, mixed> $data */
        $data = $response->getData();
        return UserRepresentation::fromArray($data);
    }
}

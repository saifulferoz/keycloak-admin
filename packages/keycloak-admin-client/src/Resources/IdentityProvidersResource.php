<?php

declare(strict_types=1);

namespace Feroz\KeycloakAdmin\Resources;

use Feroz\KeycloakAdmin\Auth\TokenManagerInterface;
use Feroz\KeycloakAdmin\Http\HttpClientInterface;
use Feroz\KeycloakAdmin\Model\IdP\IdentityProviderRepresentation;

class IdentityProvidersResource extends AbstractResource
{
    public function __construct(
        HttpClientInterface $httpClient,
        TokenManagerInterface $tokenManager,
        private readonly string $realm
    ) {
        parent::__construct($httpClient, $tokenManager);
    }

    /**
     * @return list<IdentityProviderRepresentation>
     */
    public function all(): array
    {
        $response = $this->sendGet(sprintf('/admin/realms/%s/identity-provider/instances', rawurlencode($this->realm)));
        $data = $response->getData();

        if (!is_array($data)) {
            return [];
        }

        return array_map(
            static fn(array $item) => IdentityProviderRepresentation::fromArray($item),
            $data
        );
    }

    public function get(string $alias): IdentityProviderRepresentation
    {
        $response = $this->sendGet(
            sprintf('/admin/realms/%s/identity-provider/instances/%s', rawurlencode($this->realm), rawurlencode($alias))
        );

        /** @var array<string, mixed> $data */
        $data = $response->getData();
        return IdentityProviderRepresentation::fromArray($data);
    }

    public function create(IdentityProviderRepresentation $idp): void
    {
        $this->sendPost(
            sprintf('/admin/realms/%s/identity-provider/instances', rawurlencode($this->realm)),
            $idp->toArray()
        );
    }

    public function update(string $alias, IdentityProviderRepresentation $idp): void
    {
        $this->sendPut(
            sprintf('/admin/realms/%s/identity-provider/instances/%s', rawurlencode($this->realm), rawurlencode($alias)),
            $idp->toArray()
        );
    }

    public function delete(string $alias): void
    {
        $this->sendDelete(
            sprintf('/admin/realms/%s/identity-provider/instances/%s', rawurlencode($this->realm), rawurlencode($alias))
        );
    }
}

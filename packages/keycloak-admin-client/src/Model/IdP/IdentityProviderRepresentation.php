<?php

declare(strict_types=1);

namespace Feroz\KeycloakAdmin\Model\IdP;

final readonly class IdentityProviderRepresentation
{
    /**
     * @param array<string, string>|null $config
     */
    public function __construct(
        public ?string $alias = null,
        public ?string $displayName = null,
        public ?string $providerId = null,
        public ?bool $enabled = true,
        public ?bool $trustEmail = false,
        public ?bool $storeToken = false,
        public ?bool $addReadTokenRoleOnCreate = false,
        public ?bool $authenticateByDefault = false,
        public ?bool $linkOnly = false,
        public ?string $firstBrokerLoginFlowAlias = 'first broker login',
        public ?string $postBrokerLoginFlowAlias = null,
        public ?array $config = null
    ) {
    }

    public static function create(string $alias, string $providerId): self
    {
        return new self(
            alias: $alias,
            displayName: $alias,
            providerId: $providerId,
            enabled: true
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            alias: isset($data['alias']) ? (string) $data['alias'] : null,
            displayName: isset($data['displayName']) ? (string) $data['displayName'] : null,
            providerId: isset($data['providerId']) ? (string) $data['providerId'] : null,
            enabled: isset($data['enabled']) ? (bool) $data['enabled'] : true,
            trustEmail: isset($data['trustEmail']) ? (bool) $data['trustEmail'] : false,
            storeToken: isset($data['storeToken']) ? (bool) $data['storeToken'] : false,
            addReadTokenRoleOnCreate: isset($data['addReadTokenRoleOnCreate']) ? (bool) $data['addReadTokenRoleOnCreate'] : false,
            authenticateByDefault: isset($data['authenticateByDefault']) ? (bool) $data['authenticateByDefault'] : false,
            linkOnly: isset($data['linkOnly']) ? (bool) $data['linkOnly'] : false,
            firstBrokerLoginFlowAlias: isset($data['firstBrokerLoginFlowAlias']) ? (string) $data['firstBrokerLoginFlowAlias'] : 'first broker login',
            postBrokerLoginFlowAlias: isset($data['postBrokerLoginFlowAlias']) ? (string) $data['postBrokerLoginFlowAlias'] : null,
            config: isset($data['config']) && is_array($data['config']) ? $data['config'] : null
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [];
        if ($this->alias !== null) {
            $data['alias'] = $this->alias;
        }
        if ($this->displayName !== null) {
            $data['displayName'] = $this->displayName;
        }
        if ($this->providerId !== null) {
            $data['providerId'] = $this->providerId;
        }
        if ($this->enabled !== null) {
            $data['enabled'] = $this->enabled;
        }
        if ($this->trustEmail !== null) {
            $data['trustEmail'] = $this->trustEmail;
        }
        if ($this->storeToken !== null) {
            $data['storeToken'] = $this->storeToken;
        }
        if ($this->addReadTokenRoleOnCreate !== null) {
            $data['addReadTokenRoleOnCreate'] = $this->addReadTokenRoleOnCreate;
        }
        if ($this->authenticateByDefault !== null) {
            $data['authenticateByDefault'] = $this->authenticateByDefault;
        }
        if ($this->linkOnly !== null) {
            $data['linkOnly'] = $this->linkOnly;
        }
        if ($this->firstBrokerLoginFlowAlias !== null) {
            $data['firstBrokerLoginFlowAlias'] = $this->firstBrokerLoginFlowAlias;
        }
        if ($this->postBrokerLoginFlowAlias !== null) {
            $data['postBrokerLoginFlowAlias'] = $this->postBrokerLoginFlowAlias;
        }
        if ($this->config !== null) {
            $data['config'] = $this->config;
        }

        return $data;
    }
}

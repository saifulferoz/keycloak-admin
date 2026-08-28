<?php

declare(strict_types=1);

namespace Feroz\KeycloakAdmin\Model\Realm;

final readonly class RealmRepresentation
{
    /**
     * @param list<string>|null $supportedLocales
     * @param array<string, string>|null $smtpServer
     * @param array<string, string>|null $attributes
     */
    public function __construct(
        public ?string $id = null,
        public ?string $realm = null,
        public ?string $displayName = null,
        public ?string $displayNameHtml = null,
        public ?bool $enabled = true,
        public ?bool $registrationAllowed = false,
        public ?bool $registrationEmailAsUsername = false,
        public ?bool $rememberMe = false,
        public ?bool $verifyEmail = false,
        public ?bool $loginWithEmailAllowed = true,
        public ?bool $duplicateEmailsAllowed = false,
        public ?bool $resetPasswordAllowed = false,
        public ?bool $editUsernameAllowed = false,
        public ?int $accessTokenLifespan = 300,
        public ?int $ssoSessionIdleTimeout = 1800,
        public ?int $ssoSessionMaxLifespan = 36000,
        public ?string $sslRequired = 'external',
        public ?array $supportedLocales = null,
        public ?string $defaultLocale = null,
        public ?array $smtpServer = null,
        public ?array $attributes = null
    ) {
    }

    public static function create(string $realmName): self
    {
        return new self(
            id: $realmName,
            realm: $realmName,
            enabled: true
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (string) $data['id'] : null,
            realm: isset($data['realm']) ? (string) $data['realm'] : null,
            displayName: isset($data['displayName']) ? (string) $data['displayName'] : null,
            displayNameHtml: isset($data['displayNameHtml']) ? (string) $data['displayNameHtml'] : null,
            enabled: isset($data['enabled']) ? (bool) $data['enabled'] : true,
            registrationAllowed: isset($data['registrationAllowed']) ? (bool) $data['registrationAllowed'] : false,
            registrationEmailAsUsername: isset($data['registrationEmailAsUsername']) ? (bool) $data['registrationEmailAsUsername'] : false,
            rememberMe: isset($data['rememberMe']) ? (bool) $data['rememberMe'] : false,
            verifyEmail: isset($data['verifyEmail']) ? (bool) $data['verifyEmail'] : false,
            loginWithEmailAllowed: isset($data['loginWithEmailAllowed']) ? (bool) $data['loginWithEmailAllowed'] : true,
            duplicateEmailsAllowed: isset($data['duplicateEmailsAllowed']) ? (bool) $data['duplicateEmailsAllowed'] : false,
            resetPasswordAllowed: isset($data['resetPasswordAllowed']) ? (bool) $data['resetPasswordAllowed'] : false,
            editUsernameAllowed: isset($data['editUsernameAllowed']) ? (bool) $data['editUsernameAllowed'] : false,
            accessTokenLifespan: isset($data['accessTokenLifespan']) ? (int) $data['accessTokenLifespan'] : 300,
            ssoSessionIdleTimeout: isset($data['ssoSessionIdleTimeout']) ? (int) $data['ssoSessionIdleTimeout'] : 1800,
            ssoSessionMaxLifespan: isset($data['ssoSessionMaxLifespan']) ? (int) $data['ssoSessionMaxLifespan'] : 36000,
            sslRequired: isset($data['sslRequired']) ? (string) $data['sslRequired'] : 'external',
            supportedLocales: isset($data['supportedLocales']) && is_array($data['supportedLocales']) ? array_values($data['supportedLocales']) : null,
            defaultLocale: isset($data['defaultLocale']) ? (string) $data['defaultLocale'] : null,
            smtpServer: isset($data['smtpServer']) && is_array($data['smtpServer']) ? $data['smtpServer'] : null,
            attributes: isset($data['attributes']) && is_array($data['attributes']) ? $data['attributes'] : null
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [];
        if ($this->id !== null) {
            $data['id'] = $this->id;
        }
        if ($this->realm !== null) {
            $data['realm'] = $this->realm;
        }
        if ($this->displayName !== null) {
            $data['displayName'] = $this->displayName;
        }
        if ($this->displayNameHtml !== null) {
            $data['displayNameHtml'] = $this->displayNameHtml;
        }
        if ($this->enabled !== null) {
            $data['enabled'] = $this->enabled;
        }
        if ($this->registrationAllowed !== null) {
            $data['registrationAllowed'] = $this->registrationAllowed;
        }
        if ($this->registrationEmailAsUsername !== null) {
            $data['registrationEmailAsUsername'] = $this->registrationEmailAsUsername;
        }
        if ($this->rememberMe !== null) {
            $data['rememberMe'] = $this->rememberMe;
        }
        if ($this->verifyEmail !== null) {
            $data['verifyEmail'] = $this->verifyEmail;
        }
        if ($this->loginWithEmailAllowed !== null) {
            $data['loginWithEmailAllowed'] = $this->loginWithEmailAllowed;
        }
        if ($this->duplicateEmailsAllowed !== null) {
            $data['duplicateEmailsAllowed'] = $this->duplicateEmailsAllowed;
        }
        if ($this->resetPasswordAllowed !== null) {
            $data['resetPasswordAllowed'] = $this->resetPasswordAllowed;
        }
        if ($this->editUsernameAllowed !== null) {
            $data['editUsernameAllowed'] = $this->editUsernameAllowed;
        }
        if ($this->accessTokenLifespan !== null) {
            $data['accessTokenLifespan'] = $this->accessTokenLifespan;
        }
        if ($this->ssoSessionIdleTimeout !== null) {
            $data['ssoSessionIdleTimeout'] = $this->ssoSessionIdleTimeout;
        }
        if ($this->ssoSessionMaxLifespan !== null) {
            $data['ssoSessionMaxLifespan'] = $this->ssoSessionMaxLifespan;
        }
        if ($this->sslRequired !== null) {
            $data['sslRequired'] = $this->sslRequired;
        }
        if ($this->supportedLocales !== null) {
            $data['supportedLocales'] = $this->supportedLocales;
        }
        if ($this->defaultLocale !== null) {
            $data['defaultLocale'] = $this->defaultLocale;
        }
        if ($this->smtpServer !== null) {
            $data['smtpServer'] = $this->smtpServer;
        }
        if ($this->attributes !== null) {
            $data['attributes'] = $this->attributes;
        }

        return $data;
    }
}

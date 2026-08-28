<?php

declare(strict_types=1);

namespace Feroz\KeycloakAdmin;

use Feroz\KeycloakAdmin\Auth\Credentials\ClientCredentials;
use Feroz\KeycloakAdmin\Auth\Credentials\CredentialsInterface;
use Feroz\KeycloakAdmin\Auth\Credentials\PasswordCredentials;
use Feroz\KeycloakAdmin\Auth\Storage\PsrSimpleCacheTokenStorage;
use Feroz\KeycloakAdmin\Auth\Storage\TokenStorageInterface;
use Feroz\KeycloakAdmin\Auth\TokenManagerInterface;
use Feroz\KeycloakAdmin\Http\HttpClientInterface;
use Feroz\KeycloakAdmin\Http\Psr18HttpClient;
use InvalidArgumentException;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;

final class KeycloakClientBuilder
{
    private ?string $baseUrl = null;
    private string $authRealm = 'master';
    private ?string $defaultTargetRealm = null;
    private ?CredentialsInterface $credentials = null;
    private float $timeout = 15.0;
    private bool $verifySsl = true;

    private ?HttpClientInterface $httpClient = null;
    private ?ClientInterface $psr18Client = null;
    private ?RequestFactoryInterface $requestFactory = null;
    private ?StreamFactoryInterface $streamFactory = null;
    private ?TokenManagerInterface $tokenManager = null;
    private ?TokenStorageInterface $tokenStorage = null;
    private ?LoggerInterface $logger = null;

    public function withBaseUrl(string $baseUrl): self
    {
        $this->baseUrl = $baseUrl;
        return $this;
    }

    public function withAuthRealm(string $authRealm): self
    {
        $this->authRealm = $authRealm;
        return $this;
    }

    public function withDefaultTargetRealm(string $targetRealm): self
    {
        $this->defaultTargetRealm = $targetRealm;
        return $this;
    }

    public function withCredentials(CredentialsInterface $credentials): self
    {
        $this->credentials = $credentials;
        return $this;
    }

    public function withClientCredentials(string $clientId, ?string $clientSecret = null): self
    {
        $this->credentials = new ClientCredentials($clientId, $clientSecret);
        return $this;
    }

    public function withPasswordCredentials(
        string $clientId,
        string $username,
        string $password,
        ?string $clientSecret = null
    ): self {
        $this->credentials = new PasswordCredentials($clientId, $username, $password, $clientSecret);
        return $this;
    }

    public function withTimeout(float $timeout): self
    {
        $this->timeout = $timeout;
        return $this;
    }

    public function withVerifySsl(bool $verifySsl): self
    {
        $this->verifySsl = $verifySsl;
        return $this;
    }

    public function withHttpClient(HttpClientInterface $httpClient): self
    {
        $this->httpClient = $httpClient;
        return $this;
    }

    public function withPsr18Client(
        ClientInterface $client,
        ?RequestFactoryInterface $requestFactory = null,
        ?StreamFactoryInterface $streamFactory = null
    ): self {
        $this->psr18Client = $client;
        $this->requestFactory = $requestFactory;
        $this->streamFactory = $streamFactory;
        return $this;
    }

    public function withTokenManager(TokenManagerInterface $tokenManager): self
    {
        $this->tokenManager = $tokenManager;
        return $this;
    }

    public function withTokenStorage(TokenStorageInterface $tokenStorage): self
    {
        $this->tokenStorage = $tokenStorage;
        return $this;
    }

    public function withCache(CacheInterface $cache, string $prefix = 'keycloak_token_'): self
    {
        $this->tokenStorage = new PsrSimpleCacheTokenStorage($cache, $prefix);
        return $this;
    }

    public function withLogger(LoggerInterface $logger): self
    {
        $this->logger = $logger;
        return $this;
    }

    public function build(): KeycloakClientInterface
    {
        if ($this->baseUrl === null || trim($this->baseUrl) === '') {
            throw new InvalidArgumentException('Base URL must be configured for Keycloak client.');
        }

        if ($this->credentials === null) {
            throw new InvalidArgumentException('Credentials (Client or Password) must be configured for Keycloak client.');
        }

        $configuration = new Configuration(
            baseUrl: $this->baseUrl,
            authRealm: $this->authRealm,
            defaultTargetRealm: $this->defaultTargetRealm,
            credentials: $this->credentials,
            timeout: $this->timeout,
            verifySsl: $this->verifySsl
        );

        $httpClient = $this->httpClient;
        if ($httpClient === null) {
            $httpClient = new Psr18HttpClient(
                configuration: $configuration,
                client: $this->psr18Client,
                requestFactory: $this->requestFactory,
                streamFactory: $this->streamFactory,
                logger: $this->logger
            );
        }

        return new KeycloakClient(
            configuration: $configuration,
            httpClient: $httpClient,
            tokenManager: $this->tokenManager,
            tokenStorage: $this->tokenStorage,
            logger: $this->logger
        );
    }
}

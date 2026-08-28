<?php

declare(strict_types=1);

namespace Feroz\KeycloakAdminBundle\DependencyInjection;

use Feroz\KeycloakAdmin\Auth\Credentials\ClientCredentials;
use Feroz\KeycloakAdmin\Auth\Credentials\PasswordCredentials;
use Feroz\KeycloakAdmin\Auth\Storage\PsrSimpleCacheTokenStorage;
use Feroz\KeycloakAdmin\Auth\TokenManager;
use Feroz\KeycloakAdmin\Configuration as ClientConfig;
use Feroz\KeycloakAdmin\Http\Psr18HttpClient;
use Feroz\KeycloakAdmin\KeycloakClient;
use Feroz\KeycloakAdmin\KeycloakClientInterface;
use Feroz\KeycloakAdminBundle\DataCollector\KeycloakDataCollector;
use Feroz\KeycloakAdminBundle\DataCollector\TraceableHttpClient;
use Feroz\KeycloakAdminBundle\Factory\KeycloakClientRegistry;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

final class KeycloakAdminExtension extends Extension
{
    public function getAlias(): string
    {
        return 'keycloak_admin';
    }

    /**
     * @param array<string, mixed> $configs
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        /** @var array{
         *     default_client: string,
         *     http_client: ?string,
         *     cache: ?string,
         *     clients: array<string, array{
         *         base_url: string,
         *         realm: string,
         *         target_realm: ?string,
         *         auth: array{
         *             grant_type: string,
         *             client_id: string,
         *             client_secret: ?string,
         *             username: ?string,
         *             password: ?string
         *         },
         *         options: array{
         *             timeout: float,
         *             verify_ssl: bool
         *         }
         *     }>
         * } $config */
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');

        $clientReferences = [];
        $traceableClientRefs = [];

        foreach ($config['clients'] as $name => $clientConfig) {
            $clientId = sprintf('keycloak_admin.client.%s', $name);

            // 1. Credentials Definition
            $credentialsId = sprintf('keycloak_admin.credentials.%s', $name);
            if ($clientConfig['auth']['grant_type'] === 'password') {
                $credentialsDef = new Definition(PasswordCredentials::class, [
                    $clientConfig['auth']['client_id'],
                    $clientConfig['auth']['username'] ?? '',
                    $clientConfig['auth']['password'] ?? '',
                    $clientConfig['auth']['client_secret'] ?? null,
                ]);
            } else {
                $credentialsDef = new Definition(ClientCredentials::class, [
                    $clientConfig['auth']['client_id'],
                    $clientConfig['auth']['client_secret'] ?? null,
                ]);
            }
            $container->setDefinition($credentialsId, $credentialsDef);

            // 2. Configuration Definition
            $configDefId = sprintf('keycloak_admin.config.%s', $name);
            $configDef = new Definition(ClientConfig::class, [
                $clientConfig['base_url'],
                $clientConfig['realm'],
                $clientConfig['target_realm'],
                new Reference($credentialsId),
                $clientConfig['options']['timeout'],
                $clientConfig['options']['verify_ssl'],
            ]);
            $container->setDefinition($configDefId, $configDef);

            // 3. HTTP Client Definition
            $httpId = sprintf('keycloak_admin.http_client.%s', $name);
            if ($config['http_client'] !== null) {
                // If custom PSR-18 / Symfony HTTP client service provided
                $httpDef = new Definition(Psr18HttpClient::class, [
                    new Reference($configDefId),
                    new Reference($config['http_client']),
                ]);
            } else {
                $httpDef = new Definition(Psr18HttpClient::class, [
                    new Reference($configDefId),
                ]);
            }
            $container->setDefinition($httpId, $httpDef);

            // 4. Traceable HTTP Client (for WebProfiler)
            $traceableHttpId = sprintf('keycloak_admin.traceable_http_client.%s', $name);
            $traceableHttpDef = new Definition(TraceableHttpClient::class, [
                new Reference($httpId),
            ]);
            $container->setDefinition($traceableHttpId, $traceableHttpDef);
            $traceableClientRefs[] = new Reference($traceableHttpId);

            // 5. Token Storage Definition (optional cache)
            $storageRef = null;
            if ($config['cache'] !== null) {
                $storageId = sprintf('keycloak_admin.token_storage.%s', $name);
                $storageDef = new Definition(PsrSimpleCacheTokenStorage::class, [
                    new Reference($config['cache']),
                    sprintf('keycloak_token_%s_', $name),
                ]);
                $container->setDefinition($storageId, $storageDef);
                $storageRef = new Reference($storageId);
            }

            // 6. Token Manager Definition
            $tokenManagerId = sprintf('keycloak_admin.token_manager.%s', $name);
            $tokenManagerDef = new Definition(TokenManager::class, [
                new Reference($configDefId),
                new Reference($traceableHttpId),
                $storageRef,
            ]);
            $container->setDefinition($tokenManagerId, $tokenManagerDef);

            // 7. Keycloak Client Definition
            $clientDef = new Definition(KeycloakClient::class, [
                new Reference($configDefId),
                new Reference($traceableHttpId),
                new Reference($tokenManagerId),
                $storageRef,
            ]);
            $clientDef->setPublic(true);
            $container->setDefinition($clientId, $clientDef);

            $clientReferences[$name] = new Reference($clientId);
        }

        // 8. Register Registry Definition
        $registryDef = new Definition(KeycloakClientRegistry::class, [
            $clientReferences,
            $config['default_client'],
        ]);
        $registryDef->setPublic(true);
        $container->setDefinition(KeycloakClientRegistry::class, $registryDef);
        $container->setAlias('keycloak_admin.registry', KeycloakClientRegistry::class);

        // 9. Default client autowiring alias
        $defaultClientName = $config['default_client'];
        if (isset($config['clients'][$defaultClientName])) {
            $defaultClientId = sprintf('keycloak_admin.client.%s', $defaultClientName);
            $container->setAlias(KeycloakClientInterface::class, $defaultClientId);
            $container->setAlias(KeycloakClient::class, $defaultClientId);
        }

        // 10. Profiler Data Collector
        $collectorDef = new Definition(KeycloakDataCollector::class, [
            new Reference(KeycloakClientRegistry::class),
            $traceableClientRefs,
        ]);
        $collectorDef->addTag('data_collector', [
            'template' => '@KeycloakAdmin/Collector/keycloak.html.twig',
            'id' => KeycloakDataCollector::NAME,
            'priority' => 300,
        ]);
        $container->setDefinition(KeycloakDataCollector::class, $collectorDef);
    }
}

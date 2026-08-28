<?php

declare(strict_types=1);

namespace Feroz\KeycloakAdminBundle\Tests\DependencyInjection;

use Feroz\KeycloakAdmin\KeycloakClient;
use Feroz\KeycloakAdmin\KeycloakClientInterface;
use Feroz\KeycloakAdminBundle\DependencyInjection\KeycloakAdminExtension;
use Feroz\KeycloakAdminBundle\Factory\KeycloakClientRegistry;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

final class KeycloakAdminExtensionTest extends TestCase
{
    public function testExtensionLoadsAndRegistersServices(): void
    {
        $container = new ContainerBuilder(new ParameterBag(['kernel.debug' => false]));
        $extension = new KeycloakAdminExtension();

        $configs = [
            [
                'default_client' => 'main',
                'clients' => [
                    'main' => [
                        'base_url' => 'https://iam.example.com',
                        'realm' => 'master',
                        'target_realm' => 'myrealm',
                        'auth' => [
                            'client_id' => 'admin-cli',
                            'client_secret' => 'super-secret',
                        ],
                    ],
                    'tenant_b' => [
                        'base_url' => 'https://tenant-b.iam.example.com',
                        'auth' => [
                            'client_id' => 'tenant-client',
                            'client_secret' => 'tenant-secret',
                        ],
                    ],
                ],
            ],
        ];

        $extension->load($configs, $container);

        // Check clients registered
        $this->assertTrue($container->hasDefinition('keycloak_admin.client.main'));
        $this->assertTrue($container->hasDefinition('keycloak_admin.client.tenant_b'));

        // Check registry registered
        $this->assertTrue($container->hasDefinition(KeycloakClientRegistry::class));

        // Check alias for default client
        $this->assertTrue($container->hasAlias(KeycloakClientInterface::class));
        $this->assertSame('keycloak_admin.client.main', (string) $container->getAlias(KeycloakClientInterface::class));
    }
}

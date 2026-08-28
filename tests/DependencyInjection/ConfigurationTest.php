<?php

declare(strict_types=1);

namespace Feroz\KeycloakAdminBundle\Tests\DependencyInjection;

use Feroz\KeycloakAdminBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;

final class ConfigurationTest extends TestCase
{
    public function testDefaultConfiguration(): void
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), [
            [
                'clients' => [
                    'default' => [
                        'base_url' => 'https://iam.example.com',
                        'auth' => [
                            'client_id' => 'admin-cli',
                            'client_secret' => 'super-secret',
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertSame('default', $config['default_client']);
        $this->assertArrayHasKey('default', $config['clients']);
        $this->assertSame('https://iam.example.com', $config['clients']['default']['base_url']);
        $this->assertSame('master', $config['clients']['default']['realm']);
        $this->assertSame('client_credentials', $config['clients']['default']['auth']['grant_type']);
        $this->assertSame('admin-cli', $config['clients']['default']['auth']['client_id']);
        $this->assertSame('super-secret', $config['clients']['default']['auth']['client_secret']);
        $this->assertSame(15.0, $config['clients']['default']['options']['timeout']);
        $this->assertTrue($config['clients']['default']['options']['verify_ssl']);
    }
}

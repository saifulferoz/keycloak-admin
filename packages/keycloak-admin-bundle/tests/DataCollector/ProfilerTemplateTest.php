<?php

declare(strict_types=1);

namespace Feroz\KeycloakAdminBundle\Tests\DataCollector;

use Feroz\KeycloakAdminBundle\DataCollector\KeycloakDataCollector;
use Feroz\KeycloakAdminBundle\KeycloakAdminBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Bundle\WebProfilerBundle\WebProfilerBundle;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Kernel;

final class ProfilerTemplateTest extends TestCase
{
    public function testProfilerTemplateIsDiscoveredAndRendered(): void
    {
        $kernel = new class('test', true) extends Kernel {
            use MicroKernelTrait;

            public function registerBundles(): iterable
            {
                yield new FrameworkBundle();
                yield new TwigBundle();
                yield new WebProfilerBundle();
                yield new KeycloakAdminBundle();
            }

            public function getCacheDir(): string
            {
                return sys_get_temp_dir() . '/keycloak-profiler-test-' . spl_object_id($this);
            }

            public function getLogDir(): string
            {
                return $this->getCacheDir() . '/logs';
            }

            protected function configureContainer(ContainerConfigurator $container): void
            {
                $container->extension('framework', [
                    'secret' => 'test',
                    'test' => true,
                    'profiler' => ['enabled' => true],
                    'router' => ['utf8' => true],
                ]);
                $container->extension('twig', ['strict_variables' => true]);
                $container->extension('web_profiler', ['toolbar' => true]);
                $container->extension('keycloak_admin', [
                    'clients' => [
                        'default' => [
                            'base_url' => 'https://keycloak.example.test',
                            'auth' => ['client_id' => 'admin-cli'],
                        ],
                    ],
                ]);
            }
        };

        try {
            $kernel->boot();
            $container = $kernel->getContainer()->get('test.service_container');
            $twig = $container->get('twig');
            $collector = $container->get('profiler')->get(KeycloakDataCollector::NAME);
            $this->assertInstanceOf(KeycloakDataCollector::class, $collector);
            $template = $twig->load('@KeycloakAdmin/Collector/keycloak.html.twig');

            $this->assertStringContainsString('Keycloak Admin', $template->renderBlock('menu', ['collector' => $collector]));
            $this->assertStringContainsString('No Keycloak Admin API calls', $template->renderBlock('panel', ['collector' => $collector]));
        } finally {
            $kernel->shutdown();
            (new Filesystem())->remove($kernel->getCacheDir());
        }
    }
}

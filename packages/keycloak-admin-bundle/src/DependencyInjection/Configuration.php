<?php

declare(strict_types=1);

namespace Feroz\KeycloakAdminBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('keycloak_admin');
        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->scalarNode('default_client')
                    ->defaultValue('default')
                    ->info('The default client to use when multiple clients are configured.')
                ->end()
                ->scalarNode('http_client')
                    ->defaultNull()
                    ->info('Symfony HttpClient service ID or PSR-18 client service ID.')
                ->end()
                ->scalarNode('cache')
                    ->defaultNull()
                    ->info('PSR-16 or Symfony Cache pool service ID for token caching.')
                ->end()
                ->arrayNode('clients')
                    ->useAttributeAsKey('name')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('base_url')
                                ->isRequired()
                                ->cannotBeEmpty()
                                ->info('Base URL of the Keycloak server, e.g. https://iam.example.com')
                            ->end()
                            ->scalarNode('realm')
                                ->defaultValue('master')
                                ->info('The realm used for administration authentication.')
                            ->end()
                            ->scalarNode('target_realm')
                                ->defaultNull()
                                ->info('The default realm on which resources will be manipulated.')
                            ->end()
                            ->arrayNode('auth')
                                ->isRequired()
                                ->children()
                                    ->enumNode('grant_type')
                                        ->values(['client_credentials', 'password'])
                                        ->defaultValue('client_credentials')
                                    ->end()
                                    ->scalarNode('client_id')
                                        ->isRequired()
                                        ->cannotBeEmpty()
                                    ->end()
                                    ->scalarNode('client_secret')
                                        ->defaultNull()
                                    ->end()
                                    ->scalarNode('username')
                                        ->defaultNull()
                                    ->end()
                                    ->scalarNode('password')
                                        ->defaultNull()
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('options')
                                ->addDefaultsIfNotSet()
                                ->children()
                                    ->floatNode('timeout')
                                        ->defaultValue(15.0)
                                    ->end()
                                    ->booleanNode('verify_ssl')
                                        ->defaultTrue()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}

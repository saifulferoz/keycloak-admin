<?php

declare(strict_types=1);

namespace Feroz\KeycloakAdminBundle\DataCollector;

use Feroz\KeycloakAdminBundle\Factory\KeycloakClientRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Throwable;

final class KeycloakDataCollector extends DataCollector
{
    public const NAME = 'keycloak_admin';

    /**
     * @param list<TraceableHttpClient> $traceableClients
     */
    public function __construct(
        private readonly ?KeycloakClientRegistry $registry = null,
        private readonly array $traceableClients = []
    ) {
    }

    public function collect(Request $request, Response $response, ?Throwable $exception = null): void
    {
        $calls = [];
        $totalDuration = 0.0;

        foreach ($this->traceableClients as $client) {
            foreach ($client->getTraces() as $trace) {
                $calls[] = $trace;
                $totalDuration += $trace['duration'];
            }
        }

        $clientsInfo = [];
        if ($this->registry !== null) {
            foreach ($this->registry->getClients() as $name => $client) {
                $config = $client->getConfiguration();
                $clientsInfo[$name] = [
                    'base_url' => $config->getBaseUrl(),
                    'auth_realm' => $config->getAuthRealm(),
                    'target_realm' => $config->getDefaultTargetRealm(),
                    'timeout' => $config->getTimeout(),
                ];
            }
        }

        $this->data = [
            'call_count' => count($calls),
            'total_duration' => round($totalDuration, 2),
            'calls' => $calls,
            'clients' => $clientsInfo,
            'default_client' => $this->registry?->getDefaultClientName() ?? 'default',
        ];
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function reset(): void
    {
        $this->data = [];
        foreach ($this->traceableClients as $client) {
            $client->reset();
        }
    }

    public function getCallCount(): int
    {
        return $this->data['call_count'] ?? 0;
    }

    public function getTotalDuration(): float
    {
        return (float) ($this->data['total_duration'] ?? 0.0);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getCalls(): array
    {
        return $this->data['calls'] ?? [];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function getClients(): array
    {
        return $this->data['clients'] ?? [];
    }

    public function getDefaultClient(): string
    {
        return $this->data['default_client'] ?? 'default';
    }
}

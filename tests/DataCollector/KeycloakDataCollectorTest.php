<?php

declare(strict_types=1);

namespace Feroz\KeycloakAdminBundle\Tests\DataCollector;

use Feroz\KeycloakAdmin\Http\ApiResponse;
use Feroz\KeycloakAdmin\Http\HttpClientInterface;
use Feroz\KeycloakAdminBundle\DataCollector\KeycloakDataCollector;
use Feroz\KeycloakAdminBundle\DataCollector\TraceableHttpClient;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class KeycloakDataCollectorTest extends TestCase
{
    public function testCollectGathersTraces(): void
    {
        $mockHttp = $this->createMock(HttpClientInterface::class);
        $mockHttp->method('get')->willReturn(new ApiResponse(200, ['status' => 'ok']));

        $traceable = new TraceableHttpClient($mockHttp);
        $traceable->get('/admin/realms/master/users');

        $collector = new KeycloakDataCollector(null, [$traceable]);
        $collector->collect(new Request(), new Response());

        $this->assertSame(1, $collector->getCallCount());
        $this->assertGreaterThanOrEqual(0.0, $collector->getTotalDuration());
        $this->assertCount(1, $collector->getCalls());
        $this->assertSame('GET', $collector->getCalls()[0]['method']);
        $this->assertSame('/admin/realms/master/users', $collector->getCalls()[0]['path']);
        $this->assertSame(200, $collector->getCalls()[0]['status_code']);
    }
}

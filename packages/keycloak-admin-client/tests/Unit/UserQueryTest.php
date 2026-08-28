<?php

declare(strict_types=1);

namespace Feroz\KeycloakAdmin\Tests\Unit;

use Feroz\KeycloakAdmin\Query\UserQuery;
use PHPUnit\Framework\TestCase;

final class UserQueryTest extends TestCase
{
    public function testUserQueryParamsGeneration(): void
    {
        $query = UserQuery::create()
            ->withSearch('feroz')
            ->withEmailVerified(true)
            ->withEnabled(true)
            ->withPagination(page: 2, perPage: 25)
            ->withAttributeFilter('department', 'Engineering');

        $params = $query->toQueryParams();

        $this->assertSame('feroz', $params['search']);
        $this->assertSame('true', $params['emailVerified']);
        $this->assertSame('true', $params['enabled']);
        $this->assertSame('25', $params['first']);
        $this->assertSame('25', $params['max']);
        $this->assertSame('department:Engineering', $params['q']);
    }
}

<?php

declare(strict_types=1);

namespace Feroz\KeycloakAdmin\Tests\Unit;

use Feroz\KeycloakAdmin\Model\Client\ClientRepresentation;
use Feroz\KeycloakAdmin\Model\Enum\RequiredAction;
use Feroz\KeycloakAdmin\Model\Realm\RealmRepresentation;
use Feroz\KeycloakAdmin\Model\User\UserRepresentation;
use PHPUnit\Framework\TestCase;

final class ModelTest extends TestCase
{
    public function testUserRepresentationSerializationAndImmutability(): void
    {
        $user = UserRepresentation::create('feroz@example.com', 'saiful.feroz')
            ->withName('Saiful', 'Feroz')
            ->withEmail('feroz@example.com', true)
            ->withAttribute('department', 'Engineering')
            ->withRequiredActions([RequiredAction::UPDATE_PASSWORD])
            ->withPassword('Secr3tP@ss!', temporary: true);

        $array = $user->toArray();

        $this->assertSame('saiful.feroz', $array['username']);
        $this->assertSame('feroz@example.com', $array['email']);
        $this->assertSame('Saiful', $array['firstName']);
        $this->assertSame('Feroz', $array['lastName']);
        $this->assertTrue($array['emailVerified']);
        $this->assertSame(['Engineering'], $array['attributes']['department']);
        $this->assertSame(['UPDATE_PASSWORD'], $array['requiredActions']);
        $this->assertSame('password', $array['credentials'][0]['type']);
        $this->assertSame('Secr3tP@ss!', $array['credentials'][0]['value']);
        $this->assertTrue($array['credentials'][0]['temporary']);

        // Roundtrip hydration
        $hydrated = UserRepresentation::fromArray($array);
        $this->assertSame($user->username, $hydrated->username);
        $this->assertSame($user->email, $hydrated->email);
        $this->assertSame($user->firstName, $hydrated->firstName);
        $this->assertSame($user->lastName, $hydrated->lastName);
        $this->assertTrue($hydrated->emailVerified);
    }

    public function testClientRepresentation(): void
    {
        $client = ClientRepresentation::create('my-frontend')
            ->toArray();

        $this->assertSame('my-frontend', $client['clientId']);
        $this->assertTrue($client['enabled']);
    }

    public function testRealmRepresentation(): void
    {
        $realm = RealmRepresentation::create('customer-realm')->toArray();

        $this->assertSame('customer-realm', $realm['realm']);
        $this->assertTrue($realm['enabled']);
    }
}

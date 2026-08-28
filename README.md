# Keycloak Admin Symfony Bundle

A first-class Symfony Bundle for integrating Keycloak Admin REST API into Symfony applications.

## Features

- **Zero-Config Autowiring**: Automatically configures and injects `KeycloakClientInterface`.
- **Multi-Tenant & Multi-Realm Support**: Configure multiple Keycloak connections and access them via `KeycloakClientRegistry`.
- **Symfony WebProfiler Integration**: Dedicated Keycloak toolbar icon and panel tracking API calls, request/response data, latency, and status codes.
- **Console Command Suite**:
  - `bin/console keycloak:ping [client]`
  - `bin/console keycloak:user:list [realm]`
  - `bin/console keycloak:user:create [realm]`
  - `bin/console keycloak:user:delete <user-id> [realm]`
- **Framework Synergy**: Integrates with Symfony Cache for token persistence and Symfony HttpClient.

---

## Installation

```bash
composer require feroz/keycloak-admin-bundle
```

Register bundle in `config/bundles.php` (if not using Symfony Flex):

```php
return [
    // ...
    Feroz\KeycloakAdminBundle\KeycloakAdminBundle::class => ['all' => true],
];
```

---

## Configuration (`config/packages/keycloak_admin.yaml`)

```yaml
keycloak_admin:
  default_client: default
  cache: 'cache.app' # Optional: PSR-16 or Symfony cache pool for token caching
  
  clients:
    default:
      base_url: '%env(KEYCLOAK_BASE_URL)%'
      realm: 'master'
      target_realm: '%env(KEYCLOAK_TARGET_REALM)%'
      auth:
        grant_type: client_credentials # or password
        client_id: '%env(KEYCLOAK_CLIENT_ID)%'
        client_secret: '%env(KEYCLOAK_CLIENT_SECRET)%'
      options:
        timeout: 10.0
        verify_ssl: true

    # Optional second tenant
    partner_tenant:
      base_url: 'https://partner-iam.example.com'
      realm: 'master'
      target_realm: 'partner'
      auth:
        grant_type: client_credentials
        client_id: 'partner-admin'
        client_secret: '%env(PARTNER_KEYCLOAK_SECRET)%'
```

---

## Usage in Services & Controllers

```php
namespace App\Controller;

use Feroz\KeycloakAdmin\KeycloakClientInterface;
use Feroz\KeycloakAdmin\Model\User\UserRepresentation;
use Feroz\KeycloakAdminBundle\Factory\KeycloakClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class UserController extends AbstractController
{
    #[Route('/api/users', methods: ['POST'])]
    public function createUser(KeycloakClientInterface $keycloak): JsonResponse
    {
        $user = UserRepresentation::create('user@example.com', 'username')
            ->withName('First', 'Last');

        $userId = $keycloak->users()->create($user);

        return new JsonResponse(['id' => $userId], 201);
    }

    #[Route('/api/partner/users', methods: ['GET'])]
    public function listPartnerUsers(KeycloakClientRegistry $registry): JsonResponse
    {
        $partnerClient = $registry->getClient('partner_tenant');
        $users = $partnerClient->users()->list();

        return new JsonResponse(array_map(fn($u) => $u->toArray(), $users->getItems()));
    }
}
```

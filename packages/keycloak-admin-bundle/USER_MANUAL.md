# Keycloak Admin Symfony Bundle — User Manual & Use Cases

This manual details every aspect of working with Keycloak in Symfony using `saifulferoz/keycloak-admin-bundle`.

---

## Table of Contents

1. [Bundle Installation & Setup](#1-bundle-installation--setup)
2. [Configuration Reference (`keycloak_admin.yaml`)](#2-configuration-reference)
3. [Dependency Injection & Service Autowiring](#3-dependency-injection--service-autowiring)
4. [Multi-Tenant / Multi-Realm Architecture with `KeycloakClientRegistry`](#4-multi-tenant--multi-realm-architecture)
5. [Complete API Use Cases via Symfony Services](#5-complete-api-use-cases-via-symfony-services)
   - [User Management (Registration, Password Reset, Roles, Attributes)](#51-user-management)
   - [Group & Hierarchy Operations](#52-group--hierarchy-operations)
   - [Client & Service Account Management](#53-client--service-account-management)
   - [Realm Management & Cache Clearing](#54-realm-management--cache-clearing)
   - [Attack Detection & Brute Force Lockouts](#55-attack-detection--brute-force-lockouts)
   - [Active Sessions & Force Logout](#56-active-sessions--force-logout)
6. [WebProfiler Toolbar & Diagnostics](#6-webprofiler-toolbar--diagnostics)
7. [Console CLI Command Reference](#7-console-cli-command-reference)
8. [Exception Handling & Error Responses](#8-exception-handling--error-responses)

---

## 1. Bundle Installation & Setup

```bash
composer require saifulferoz/keycloak-admin-bundle
```

Register bundle in `config/bundles.php` (if not auto-registered by Symfony Flex):

```php
return [
    // ...
    Feroz\KeycloakAdminBundle\KeycloakAdminBundle::class => ['all' => true],
];
```

---

## 2. Configuration Reference (`config/packages/keycloak_admin.yaml`)

```yaml
keycloak_admin:
  default_client: default
  http_client: 'http_client' # Optional: reference to Symfony HttpClient service ID
  cache: 'cache.app'         # Optional: reference to Symfony Cache pool service ID
  
  clients:
    default:
      base_url: '%env(KEYCLOAK_BASE_URL)%'
      realm: '%env(default:master:KEYCLOAK_AUTH_REALM)%'
      target_realm: '%env(KEYCLOAK_TARGET_REALM)%'
      auth:
        grant_type: client_credentials # or password
        client_id: '%env(KEYCLOAK_CLIENT_ID)%'
        client_secret: '%env(KEYCLOAK_CLIENT_SECRET)%'
      options:
        timeout: 15.0
        verify_ssl: true

    # Optional secondary realm / tenant
    partner_tenant:
      base_url: '%env(PARTNER_KEYCLOAK_BASE_URL)%'
      realm: 'master'
      target_realm: 'partner-realm'
      auth:
        grant_type: client_credentials
        client_id: 'partner-admin-cli'
        client_secret: '%env(PARTNER_KEYCLOAK_SECRET)%'
```

---

## 3. Dependency Injection & Service Autowiring

In any Controller, Service, Command, or Event Subscriber, simply type-hint `KeycloakClientInterface`:

```php
namespace App\Service;

use Feroz\KeycloakAdmin\KeycloakClientInterface;
use Feroz\KeycloakAdmin\Model\User\UserRepresentation;

final class UserRegistrationService
{
    public function __construct(
        private readonly KeycloakClientInterface $keycloak
    ) {
    }

    public function registerUser(string $email, string $username, string $firstName, string $lastName, string $password): string
    {
        $user = UserRepresentation::create($email, $username)
            ->withName($firstName, $lastName)
            ->withPassword($password, temporary: false)
            ->withEmailVerified(true);

        return $this->keycloak->users()->create($user);
    }
}
```

---

## 4. Multi-Tenant Architecture with `KeycloakClientRegistry`

When managing multiple tenants or multiple realms dynamically:

```php
namespace App\Controller;

use Feroz\KeycloakAdminBundle\Factory\KeycloakClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class TenantController extends AbstractController
{
    #[Route('/api/tenants/{tenantKey}/users', methods: ['GET'])]
    public function listUsers(string $tenantKey, KeycloakClientRegistry $registry): JsonResponse
    {
        $tenantClient = $registry->getClient($tenantKey);
        $users = $tenantClient->users()->list();

        return new JsonResponse(
            array_map(fn($u) => $u->toArray(), $users->getItems())
        );
    }
}
```

---

## 5. Complete API Use Cases via Symfony Services

### 5.1 User Management
```php
use Feroz\KeycloakAdmin\Model\Enum\RequiredAction;
use Feroz\KeycloakAdmin\Model\User\UserRepresentation;
use Feroz\KeycloakAdmin\Query\UserQuery;

// 1. Create user with custom attributes
$user = UserRepresentation::create('jane@example.com', 'jane.doe')
    ->withName('Jane', 'Doe')
    ->withAttribute('department', 'Finance')
    ->withAttribute('manager', 'saiful.feroz')
    ->withPassword('TempPass123!', temporary: true)
    ->withRequiredActions([RequiredAction::UPDATE_PASSWORD]);

$userId = $keycloak->users()->create($user);

// 2. Query with search & filters
$query = UserQuery::create()
    ->withSearch('jane')
    ->withEnabled(true)
    ->withPagination(page: 1, perPage: 20);

$users = $keycloak->users()->list($query);

// 3. Reset Password
$keycloak->users()->resetPassword($userId, 'NewPassword123!', temporary: false);

// 4. Assign Realm Role
$adminRole = $keycloak->roles()->getRealmRole('admin');
$keycloak->users()->addRealmRoleMappings($userId, [$adminRole]);
```

### 5.2 Group & Hierarchy Operations
```php
use Feroz\KeycloakAdmin\Model\Group\GroupRepresentation;

// Create parent and child group
$parentId = $keycloak->groups()->create(GroupRepresentation::create('Finance'));
$childId = $keycloak->groups()->createChildGroup($parentId, GroupRepresentation::create('Accounting'));

// Add user to group
$keycloak->users()->joinGroup($userId, $childId);
```

### 5.3 Client & Service Account Management
```php
use Feroz\KeycloakAdmin\Model\Client\ClientRepresentation;

$client = new ClientRepresentation(
    clientId: 'payment-gateway',
    serviceAccountsEnabled: true,
    standardFlowEnabled: false
);
$clientUuid = $keycloak->clients()->create($client);

// Get service account user and assign role
$serviceAccountUser = $keycloak->clients()->getServiceAccountUser($clientUuid);
$keycloak->users()->addRealmRoleMappings($serviceAccountUser->id, [$adminRole]);
```

### 5.4 Attack Detection & Brute Force Lockouts
```php
// Check user brute force status
$status = $keycloak->attackDetection()->getUserStatus($userId);
if ($status->disabled) {
    // Unlock user
    $keycloak->attackDetection()->clearUserStatus($userId);
}
```

---

## 6. WebProfiler Toolbar & Diagnostics

The bundle registers a custom panel and toolbar badge in the Symfony WebProfiler:
- Displays **Total API Call count** and **Total Latency in ms**.
- Lists every Keycloak API request method, URL path, HTTP status code, and duration.
- Shows configured clients and target realms.

---

## 7. Console CLI Command Reference

### Ping Keycloak connectivity:
```bash
php bin/console keycloak:ping
php bin/console keycloak:ping partner_tenant
```

### List Realm Users:
```bash
php bin/console keycloak:user:list
php bin/console keycloak:user:list --search=feroz --limit=10
php bin/console keycloak:user:list my-custom-realm --client=partner_tenant
```

### Create User interactively or via flags:
```bash
php bin/console keycloak:user:create john.doe john@example.com --first-name=John --last-name=Doe --password=Secret123! --email-verified
```

### Delete User:
```bash
php bin/console keycloak:user:delete <user-uuid> --force
```

---

## 8. Exception Handling & Error Responses

```php
use Feroz\KeycloakAdmin\Exception\Resource\UserAlreadyExistsException;
use Feroz\KeycloakAdmin\Exception\Resource\UserNotFoundException;
use Feroz\KeycloakAdmin\Exception\Resource\ValidationException;

try {
    $keycloak->users()->create($user);
} catch (UserAlreadyExistsException $e) {
    // HTTP 409: Duplicate email/username
} catch (ValidationException $e) {
    // HTTP 400: Password policy or field validation error
} catch (UserNotFoundException $e) {
    // HTTP 404: User not found
}
```

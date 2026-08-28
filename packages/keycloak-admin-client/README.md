# Keycloak Admin PHP Client

Enterprise-grade, PSR-compliant, standalone PHP client for Keycloak Admin REST API.

[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D8.2-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)

## Features

- **PHP 8.2+ Typed & Immutable**: Readonly properties, backed enums, strict types.
- **Standards Compliant**: Built on PHP-FIG standards (PSR-7, PSR-17, PSR-18 HTTP client, PSR-6/16 cache, PSR-3 logging).
- **Proactive OAuth2 Token Lifecycle**: Automatic token negotiation, proactive renewal before expiration, and token storage caching.
- **Rich Domain Model & Fluent Query Builders**: Complete DTO coverage with helper methods, query builders with pagination, exact matching, and attribute filtering.
- **Domain Exceptions**: Maps HTTP status codes to explicit exceptions (`UserAlreadyExistsException`, `UserNotFoundException`, `RealmNotFoundException`, etc.).
- **Comprehensive Resource Coverage**:
  - **Realms**: CRUD, clear user/realm/keys cache.
  - **Users**: CRUD, search, password reset, send verification/action emails, session logout, realm & client role assignments, group memberships.
  - **Groups**: CRUD, hierarchy / child groups, members, role assignments.
  - **Roles**: Realm roles & client roles CRUD, composite roles, user mappings.
  - **Clients**: CRUD, service account users, client secret regeneration.
  - **Identity Providers**: Instances CRUD.
  - **Attack Detection & Sessions**: Brute-force status check/clearing, session invalidation.

---

## Installation

```bash
composer require feroz/keycloak-admin-client
```

---

## Quickstart

```php
use Feroz\KeycloakAdmin\KeycloakClient;
use Feroz\KeycloakAdmin\Model\User\UserRepresentation;
use Feroz\KeycloakAdmin\Query\UserQuery;

// 1. Instantiate client using the fluent builder
$client = KeycloakClient::builder()
    ->withBaseUrl('https://iam.example.com')
    ->withAuthRealm('master')
    ->withDefaultTargetRealm('mycompany')
    ->withClientCredentials('admin-cli', 'your-client-secret')
    ->build();

// 2. Manage Users
$users = $client->users(); // uses default target realm 'mycompany'

// Create a new user
$newUser = UserRepresentation::create('john.doe@example.com', 'johndoe')
    ->withName('John', 'Doe')
    ->withEmailVerified(true)
    ->withAttribute('department', 'Engineering')
    ->withPassword('TemporaryP@ssword123!', temporary: true);

$userId = $users->create($newUser);

// Query users with pagination and search
$query = UserQuery::create()
    ->withSearch('john')
    ->withEnabled(true)
    ->withPagination(page: 1, perPage: 20);

$results = $users->list($query);
foreach ($results as $user) {
    echo $user->username . ' (' . $user->email . ')' . PHP_EOL;
}

// Reset password
$users->resetPassword($userId, 'NewSecretPassword123!');

// 3. Manage Roles & Groups
$client->users()->addRealmRoleMappings($userId, [
    $client->roles()->getRealmRole('admin')
]);
```

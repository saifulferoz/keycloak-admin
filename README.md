# Keycloak Admin PHP & Symfony Monorepo

Enterprise-grade architecture for managing Keycloak with PHP and Symfony.

## Monorepo Packages

| Package | Directory | Description |
|---|---|---|
| **[`saifulferoz/keycloak-admin-client`](./packages/keycloak-admin-client)** | `packages/keycloak-admin-client` | Standalone, PSR-compliant Keycloak Admin REST API Client |
| **[`saifulferoz/keycloak-admin-bundle`](./packages/keycloak-admin-bundle)** | `packages/keycloak-admin-bundle` | First-class Symfony Bundle with DI, WebProfiler, and Console commands |

---

## Quick Testing

Run the test suites across all packages:

```bash
composer test
```

Start Keycloak locally:

```bash
docker compose up -d
```

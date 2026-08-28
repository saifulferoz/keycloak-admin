# Contributing Guidelines

Thank you for your interest in contributing to `keycloak-admin-bundle`!

## Getting Started

1. Fork the repository and create a feature branch (`git checkout -b feature/my-new-feature`).
2. Install dependencies:
   ```bash
   composer install
   ```
3. Run test suites:
   ```bash
   vendor/bin/phpunit
   ```

## Development Guidelines

- **PHP Standards**: We strictly adhere to PSR-12 / PER-CS and strict types.
- **Testing**: Add PHPUnit unit tests for any new features or bug fixes.
- **Commits**: Follow Conventional Commits (`feat:`, `fix:`, `docs:`, `chore:`).

## Submitting Pull Requests

1. Push your branch to GitHub.
2. Open a Pull Request against `main`.
3. Ensure CI checks pass.

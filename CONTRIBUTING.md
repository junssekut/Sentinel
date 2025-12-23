# Contributing to Sentinel

Thank you for your interest in contributing to Sentinel! This document provides guidelines for contributing to the project.

---

## Code of Conduct

- Be respectful and inclusive
- Focus on constructive feedback
- Help others learn and grow

---

## Getting Started

### Development Setup

```bash
# Clone and install
git clone https://github.com/your-org/sentinel.git
cd sentinel
composer install
npm install
cp .env.example .env
php artisan key:generate

# Setup database
php artisan migrate --seed

# Start development
npm run dev
php artisan serve
```

### Running Tests

```bash
# PHP tests
php artisan test

# With coverage
php artisan test --coverage
```

---

## Branching Strategy

| Branch | Purpose |
|--------|---------|
| `main` | Production-ready code |
| `develop` | Integration branch |
| `feature/*` | New features |
| `bugfix/*` | Bug fixes |
| `hotfix/*` | Urgent production fixes |

---

## Commit Messages

Follow [Conventional Commits](https://www.conventionalcommits.org/):

```
type(scope): description

[optional body]

[optional footer]
```

**Types:**
- `feat` — New feature
- `fix` — Bug fix
- `docs` — Documentation
- `style` — Formatting (no code change)
- `refactor` — Code restructuring
- `test` — Adding tests
- `chore` — Maintenance

**Examples:**
```
feat(tasks): add bulk task revocation
fix(auth): resolve session timeout issue
docs(api): update validation endpoint examples
```

---

## Pull Request Process

1. **Fork** the repository
2. **Create** a feature branch from `develop`
3. **Make** your changes with tests
4. **Ensure** all tests pass
5. **Update** documentation if needed
6. **Submit** PR against `develop`

### PR Checklist

- [ ] Code follows project style
- [ ] Tests added/updated
- [ ] Documentation updated
- [ ] Migrations are reversible
- [ ] No breaking changes (or documented)

---

## Coding Standards

### PHP (PSR-12)

```bash
# Check style
./vendor/bin/pint --test

# Fix style
./vendor/bin/pint
```

### Blade Templates

- Use components over includes when possible
- Keep logic in controllers, not views
- Use Tailwind utility classes

### JavaScript

- Use ES6+ syntax
- Keep Alpine.js for interactivity
- Avoid jQuery

---

## Directory Structure

```
app/
├── Http/Controllers/    # Request handlers
├── Models/              # Eloquent models
├── Policies/            # Authorization
├── Services/            # Business logic
└── Events/              # Domain events

resources/views/
├── components/          # Blade components
├── layouts/             # Page layouts
├── tasks/               # Task pages
├── users/               # User pages
└── gates/               # Gate pages

tests/
├── Feature/             # Integration tests
└── Unit/                # Unit tests
```

---

## Adding Features

### New Model

1. Create migration: `php artisan make:migration create_things_table`
2. Create model: `php artisan make:model Thing`
3. Create policy: `php artisan make:policy ThingPolicy --model=Thing`
4. Register policy in `AppServiceProvider`
5. Add tests

### New Controller

1. Create controller: `php artisan make:controller ThingController -r`
2. Add routes in `routes/web.php`
3. Create view templates
4. Add authorization checks
5. Add tests

---

## Reporting Issues

Use GitHub Issues with:

- **Title:** Clear, concise description
- **Environment:** PHP version, OS, browser
- **Steps:** How to reproduce
- **Expected:** What should happen
- **Actual:** What happens
- **Logs:** Relevant error messages

---

## Questions?

Open a Discussion on GitHub or contact the maintainers.

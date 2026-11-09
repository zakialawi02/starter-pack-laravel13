# StarterPack Laravel

A Laravel 13 starter template with authentication, role-based access control, user management, a Sanctum REST API and social login built in (Laravel 13 + Blade + Tailwind CSS with Preline UI).

> The repository is still named `starter-pack-laravel12` for continuity, but the code runs on **Laravel 13**.

## Table of Contents

-   [Features](#features)
-   [Requirements](#requirements)
-   [Installation](#installation)
-   [Configuration](#configuration)
-   [Usage](#usage)
-   [Project Structure](#project-structure)
-   [Development](#development)
-   [AI-Assisted Development (Laravel Boost)](#ai-assisted-development-laravel-boost)
-   [Testing](#testing)
-   [Deployment](#deployment)
-   [API Documentation](#api-documentation)
-   [Upgrading from the Laravel 12 Version](#upgrading-from-the-laravel-12-version)
-   [Security](#security)
-   [Contributing](#contributing)
-   [License](#license)
-   [Support and Donations](#support-and-donations)

## Features

-   **Laravel 13** with Blade views and Vite 7
-   **Authentication** based on Laravel Breeze: login with email _or_ username, registration, email verification, password reset, password confirmation, and login throttling
-   **Social login** with Laravel Socialite (Google, GitHub, Facebook)
-   **Role-based access control** with three roles (`superadmin`, `admin`, `user`) and a `role:` route middleware
-   **User management panel** for superadmins: server-side DataTables (Yajra), create/edit/delete, batch delete, CSV import with preview, and CSV export
-   **REST API** with Laravel Sanctum bearer tokens: auth endpoints, the current user's profile, and superadmin-only user management
-   **Frontend** with Tailwind CSS 4 and Preline UI, plus Alpine.js
-   **Disposable email blocking** on registration (`propaganistas/laravel-disposable-email`)
-   **Error tracking** with Sentry (optional)
-   **Testing** with Pest 4
-   **Code style** with Laravel Pint
-   **AI-assisted development** with Laravel Boost (guidelines, skills and an MCP server for coding agents)

## Requirements

-   PHP **8.3** or newer, with the `gd`, `fileinfo`, `mbstring`, `openssl`, `pdo` and `zip` extensions
-   Composer 2
-   Node.js **20.19+** or **22.12+** (required by Vite 7) and npm
-   MySQL, MariaDB, PostgreSQL or SQLite
-   Redis (optional)

## Installation

1. **Clone the repository**

    ```bash
    git clone https://github.com/zakialawi02/starter-pack-laravel12.git
    cd starter-pack-laravel12
    ```

2. **Install PHP dependencies**

    ```bash
    composer install
    ```

3. **Install Node.js dependencies**

    ```bash
    npm install
    ```

4. **Environment setup**

    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

5. **Database setup** (create the database named in `.env` first)

    ```bash
    php artisan migrate --seed
    ```

6. **Link public storage** (needed for uploaded profile photos)

    ```bash
    php artisan storage:link
    ```

7. **Build frontend assets**

    ```bash
    npm run build
    ```

## Configuration

### Environment Variables

The most important keys in `.env`:

```env
APP_NAME="StarterPack Laravel"
APP_ENV=local
APP_KEY=base64:your-app-key
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_TIMEZONE="Asia/Jakarta"

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=

MAIL_MAILER=log
```

`APP_URL` matters beyond local links: password-reset emails are always built from `APP_URL`, never from the request's `Host` header. Set it to the exact public URL in every environment.

### Social Login

Fill in the credentials for each provider you want to enable. The login and register pages only show a provider's button when both its client ID and its secret are set.

```env
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_CALLBACK_URL="${APP_URL}/auth/google/callback"

GITHUB_CLIENT_ID=
GITHUB_CLIENT_SECRET=
GITHUB_CALLBACK_URL="${APP_URL}/auth/github/callback"

FACEBOOK_CLIENT_ID=
FACEBOOK_CLIENT_SECRET=
FACEBOOK_CALLBACK_URL="${APP_URL}/auth/facebook/callback"
```

### Sentry (optional)

```env
SENTRY_LARAVEL_DSN=
SENTRY_SEND_DEFAULT_PII=true
SENTRY_TRACES_SAMPLE_RATE=1.0
```

`SENTRY_SEND_DEFAULT_PII=true` sends request data such as IP addresses and headers to Sentry. Set it to `false` if your privacy requirements don't allow that.

### Database Configuration

1. Create a new database for your project.
2. Update the database credentials in your `.env` file.
3. Run migrations and seeders:
    ```bash
    php artisan migrate:fresh --seed
    ```

## Usage

### Development Server

Run the web server, queue worker and Vite together:

```bash
composer dev
```

Or run them separately:

```bash
php artisan serve
```

```bash
npm run dev
```

The route reference for the web app is available in the browser at `/docs`.

### Default Credentials

The seeders create these accounts for **local development only**:

| Role       | Email                 | Username     | Password     |
| ---------- | --------------------- | ------------ | ------------ |
| superadmin | `superadmin@mail.com` | `superadmin` | `superadmin` |
| admin      | `admin@mail.com`      | `admin`      | `admin`      |
| user       | `user@mail.com`       | `user`       | `user`       |

`DatabaseSeeder` also creates 200 fake users whose password is `password`.

> **Warning:** never run the seeders on a production or shared database. If you did, change these passwords or delete the accounts right away.

### Roles

| Role         | Can access                                                                |
| ------------ | ------------------------------------------------------------------------- |
| `superadmin` | Everything, including user management (web panel and `/api/v1/users`)    |
| `admin`      | Dashboard and own profile (the `role:superadmin,admin` group is reserved for admin features) |
| `user`       | Dashboard and own profile                                                 |

The dashboard requires a verified email. The profile page does not.

## Project Structure

```
starter-pack-laravel12/
├── app/
│   ├── Actions/                 # Reusable action classes (e.g. UploadCoverImage)
│   ├── Enums/                   # UserRole enum
│   ├── Helpers/                 # Global helpers (breadcrumbs)
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/             # REST API controllers
│   │   │   │   └── Auth/
│   │   │   ├── Auth/            # Web auth controllers (Breeze-based)
│   │   │   └── Socialite/       # Social login redirect/callback
│   │   ├── Middleware/          # RoleCheck (`role:` alias)
│   │   ├── Requests/            # Form requests
│   │   └── Resources/           # API resources
│   ├── Models/
│   ├── Providers/
│   └── View/Components/
├── bootstrap/
├── config/
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
├── docs/                        # API docs (OpenAPI 2.0 JSON/YAML, Postman collection)
├── lang/                        # en and id translations
├── public/
├── resources/
│   ├── css/
│   ├── js/
│   └── views/
│       ├── auth/
│       ├── components/
│       ├── layouts/
│       └── pages/
├── routes/
│   ├── api/
│   │   └── auth.php             # /api/auth/*
│   ├── api.php                  # /api/v1/*
│   ├── auth.php                 # web auth routes
│   ├── console.php
│   └── web.php
├── storage/
├── tests/
│   ├── Feature/
│   └── Unit/
├── AGENTS.md                    # Points coding agents to CLAUDE.md
├── CLAUDE.md                    # Laravel Boost guidelines for coding agents
└── boost.json                   # Laravel Boost configuration
```

## Development

### Code Style

This project uses [Laravel Pint](https://laravel.com/docs/pint) with the default Laravel preset:

```bash
# Format changed files
vendor/bin/pint --dirty

# Check formatting without changing files
vendor/bin/pint --test
```

## AI-Assisted Development (Laravel Boost)

[Laravel Boost](https://laravel.com/docs/boost) is installed as a development dependency. It gives coding agents (Claude Code, Codex, Cursor, and others) project-specific context:

-   **`CLAUDE.md`**: Boost guidelines for this project (Laravel 13, Pest 4, Pint, and others).
-   **`AGENTS.md`**: a short pointer that sends every other agent to `CLAUDE.md`, so both files stay in sync.
-   **`.claude/skills/`**: Boost skills (Laravel best practices, testing, Tailwind, Socialite, and others).
-   **`.mcp.json`**: registers the Boost MCP server (`php artisan boost:mcp`), which exposes tools such as documentation search, database schema, and log readers.

Refresh the guidelines and skills after upgrading packages:

```bash
php artisan boost:update
```

> Boost only runs when `APP_ENV=local` or `APP_DEBUG=true`, and it is not installed with `composer install --no-dev`. While it runs, it registers an unauthenticated `POST /_boost/browser-logs` endpoint whose contents the MCP log tools pass to your coding agent. Don't expose a development server to untrusted networks.

## Testing

```bash
# Run all tests
php artisan test --compact

# Run one suite or one test
php artisan test --testsuite=Feature
php artisan test --compact --filter="non-superadmin token cannot list users"

# Run tests with coverage (requires the pcov or xdebug extension)
php artisan test --coverage
```

Tests use an in-memory SQLite database (see `phpunit.xml`). Run `npm run build` once before testing, because the page-rendering tests need the Vite manifest in `public/build`.

### Writing Tests

-   Place feature tests in `tests/Feature/` and unit tests in `tests/Unit/`.
-   Use the model factories (`database/factories`) instead of building models by hand.
-   Name files `{Subject}Test.php`.

## Deployment

### Production Setup

1. **Environment configuration**

    ```env
    APP_ENV=production
    APP_DEBUG=false
    APP_URL=https://your-domain.example
    SESSION_SECURE_COOKIE=true
    ```

2. **Install and optimize**

    ```bash
    composer install --optimize-autoloader --no-dev
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    npm ci && npm run build
    ```

3. **Database migration and storage link**

    ```bash
    php artisan migrate --force
    php artisan storage:link
    ```

Do **not** run the seeders in production (see [Default Credentials](#default-credentials)).

> **Known issue:** `resources/views/auth/login.blade.php` and `register.blade.php` read the OAuth credentials with `env()`. After `php artisan config:cache`, `env()` returns `null`, so the social-login buttons disappear in production. Until the views read `config('services.<provider>.client_id')` instead, skip `config:cache` if you rely on social login.

## API Documentation

The API reference is in `docs/` (OpenAPI 2.0 `laravel-restfull-api.json` / `.yaml` and a Postman collection) and online at [API DOCS](https://documenter.getpostman.com/view/25223819/2sAYkLoHLh).

### Authentication

The API uses Laravel Sanctum bearer tokens. Get a token from `POST /api/auth/login` or `POST /api/auth/register`, then send it with every request:

```http
Authorization: Bearer {your-token}
```

A new login revokes the user's previous tokens. Tokens don't expire by default; set `expiration` in `config/sanctum.php` if you need that.

### Endpoints

**Auth (`/api/auth`)**

-   `POST /api/auth/register`: register and receive a token
-   `POST /api/auth/login`: log in with `id_user` (email or username) and `password`; locks out for a short time after 5 failed attempts
-   `POST /api/auth/logout`: revoke the current token
-   `POST /api/auth/forgot-password`: always responds with the same generic message; the reset link is only sent by email
-   `POST /api/auth/reset-password`: set a new password with the emailed token
-   `POST /api/auth/email/verification-notification`: resend the verification email
-   `GET /api/auth/verify-email/{id}/{hash}`: verify an email address (signed URL)

**Current user (`/api/v1/user`, any authenticated token)**

-   `GET /api/v1/user`: get my profile
-   `PATCH /api/v1/user`: update my profile
-   `PATCH /api/v1/user/photo`: upload my profile photo (send as `POST` with `_method=PATCH` for multipart)
-   `DELETE /api/v1/user/destroy/me`: delete my account

**User management (`/api/v1/users`, verified superadmin only)**

-   `GET /api/v1/users`: list users
-   `POST /api/v1/users`: create a user
-   `GET /api/v1/users/{id}`: get a user
-   `PUT/PATCH /api/v1/users/{id}`: update a user
-   `DELETE /api/v1/users/{id}`: delete a user

Any other token gets `403 Forbidden` on these endpoints.

## Upgrading from the Laravel 12 Version

If you already have a checkout of the Laravel 12 version:

```bash
git pull
composer install
php artisan optimize:clear
php artisan migrate
npm install && npm run build
```

`php artisan optimize:clear` is required. Laravel Debugbar 4 moved to a new namespace, and Livewire is no longer pulled in, so a stale `bootstrap/cache/packages.php` or stale compiled views will throw "class not found" errors until they are cleared.

Other changes worth knowing about:

-   PHP 8.3 is now the minimum, and Pest moved to version 4.
-   Session data keeps PHP serialization (`config/session.php`, `'serialization' => 'php'`), so users stay logged in through the upgrade. Laravel 13's default is `json`; switching to it logs everyone out once.
-   The cache no longer unserializes PHP objects (`config/cache.php`, `'serializable_classes' => false`).
-   API breaking changes: `/api/v1/users` now requires a verified superadmin, and `POST /api/auth/forgot-password` no longer returns the reset token (`url_reset`) or a 404 for unknown emails.

See the official [Laravel 13 upgrade guide](https://laravel.com/docs/13.x/upgrade) for framework-level details.

## Security

Security measures built into this starter:

-   User management (web panel and API) is limited to verified superadmins.
-   Password-reset tokens are only delivered by email. Reset links are built from `APP_URL`.
-   Login throttling is keyed on the submitted login identifier and the client IP.
-   Post-login redirects (`?redirect=` and the social-login redirect) only follow same-origin paths.
-   Uploaded images are stored under a name derived from their detected content type, not the client's file name.
-   User-supplied names and emails are escaped in the user-management table.

If you discover a security vulnerability, please email hallo@zakialawi.my.id instead of using the issue tracker.

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Development Guidelines

-   Follow the Laravel conventions and run `vendor/bin/pint --dirty` before committing.
-   Write tests for new features and bug fixes.
-   Update the documentation (`README.md`, `WIKI.md`, `docs/`) when behavior changes.
-   Make sure `php artisan test` passes before opening a pull request.

## License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.

## Support and Donations

If you find this project useful and would like to support its further development, you can donate via https://ko-fi.com/zakialawi. Every contribution is greatly appreciated. Thank you!

-   **Documentation**: [Wiki](WIKI.md)
-   **Issues**: [GitHub Issues](https://github.com/zakialawi02/starter-pack-laravel12/issues)
-   **Discussions**: [GitHub Discussions](https://github.com/zakialawi02/starter-pack-laravel12/discussions)

## Acknowledgments

-   [Laravel Framework](https://laravel.com/)
-   [Tailwind CSS](https://tailwindcss.com/)
-   [Preline UI](https://preline.co/)
-   [Vite](https://vitejs.dev/)
-   All contributors who have helped make this project better

---

**Happy Coding! 🚀**

# Starter Pack Laravel 13

A Laravel 13 starter pack with pre-configured components and features to speed up web application development.

## Table of Contents

-   [Project Overview](#project-overview)
-   [Features](#features)
-   [Project Structure](#project-structure)
-   [Installation](#installation)
-   [Configuration](#configuration)
-   [Key Components](#key-components)
-   [Available Actions](#available-actions)
-   [Helpers](#helpers)
-   [Views and Layouts](#views-and-layouts)
-   [Routing](#routing)
-   [Authorization](#authorization)
-   [Testing](#testing)
-   [AI-Assisted Development](#ai-assisted-development)
-   [Development Guidelines](#development-guidelines)

## Project Overview

This starter pack is a foundation for web applications on Laravel 13. It ships with authentication, role-based access control, a superadmin user-management panel, a Sanctum REST API, and the components and conventions you need to get started quickly. For installation, deployment, and upgrade notes, see [README.md](./README.md).

## Features

-   Laravel 13 framework (PHP 8.3+)
-   Authentication based on Laravel Breeze, with login by email or username
-   Social login with Laravel Socialite (Google, GitHub, Facebook)
-   Role-based access control (`superadmin`, `admin`, `user`)
-   Superadmin user management: DataTables (Yajra), CSV import and export, batch delete
-   Admin panel and front-end layouts
-   Action classes for reusable business logic
-   Helper functions
-   File uploads to the public disk
-   Disposable email validation
-   Sentry error tracking
-   Sanctum API authentication
-   Testing with Pest 4
-   Laravel Boost for AI coding agents

## Project Structure

```
starter-pack-laravel12/
├── app/
│   ├── Actions/              # Reusable action classes
│   ├── Enums/                # UserRole enum
│   ├── Helpers/              # Global helper functions
│   ├── Http/
│   │   ├── Controllers/      # Web, API, Auth and Socialite controllers
│   │   ├── Middleware/       # RoleCheck (`role:` alias)
│   │   ├── Requests/         # Form request validation
│   │   └── Resources/        # API resources
│   ├── Models/               # Eloquent models
│   ├── Providers/            # Service providers
│   └── View/Components/      # Blade layout components
├── bootstrap/                # Application bootstrap (routing, middleware, exceptions)
├── config/                   # Configuration files
├── database/                 # Migrations, factories and seeders
├── docs/                     # API documentation (OpenAPI, Postman)
├── lang/                     # Translations (en, id)
├── public/                   # Publicly accessible files
├── resources/                # Views, styles and scripts
├── routes/                   # Route definitions
├── storage/                  # File storage
├── tests/                    # Automated tests
└── .env.example              # Environment configuration example
```

## Installation

1. Clone the repository:

    ```bash
    git clone https://github.com/zakialawi02/starter-pack-laravel12.git
    cd starter-pack-laravel12
    ```

2. Install PHP dependencies:

    ```bash
    composer install
    ```

3. Install Node dependencies:

    ```bash
    npm install
    ```

4. Copy and configure the environment file:

    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

5. Configure your database in the `.env` file.

6. Run the migrations and seeders (seeders are for local development only):

    ```bash
    php artisan migrate --seed
    ```

7. Link public storage for uploaded files:

    ```bash
    php artisan storage:link
    ```

8. Compile the assets:

    ```bash
    npm run dev
    # or for production
    npm run build
    ```

9. Start the development server (web server, queue worker and Vite together):
    ```bash
    composer dev
    ```

## Configuration

### Environment Variables

Key environment variables to configure in your `.env` file:

-   `APP_NAME`: application name
-   `APP_ENV`: environment (local, production, and so on)
-   `APP_KEY`: application encryption key
-   `APP_DEBUG`: debug mode (must be `false` in production)
-   `APP_URL`: public application URL, also used to build password-reset links
-   `APP_TIMEZONE`: application timezone
-   `DB_*`: database connection settings
-   `MAIL_*`: mail configuration
-   `QUEUE_CONNECTION`: queue driver
-   `SESSION_DRIVER`: session driver
-   `SESSION_SECURE_COOKIE`: set to `true` in production (HTTPS only)
-   `GOOGLE_*`, `GITHUB_*`, `FACEBOOK_*`: Socialite credentials
-   `SENTRY_LARAVEL_DSN`: Sentry error tracking

### Key Configuration Files

-   [bootstrap/app.php](./bootstrap/app.php): routing, middleware aliases and exception rendering
-   [config/app.php](./config/app.php): application configuration
-   [config/auth.php](./config/auth.php): authentication configuration
-   [config/sanctum.php](./config/sanctum.php): API token settings
-   [config/session.php](./config/session.php): session settings (keeps `'serialization' => 'php'` from the Laravel 12 version)
-   [config/cache.php](./config/cache.php): cache settings (`'serializable_classes' => false`)
-   [config/database.php](./config/database.php): database configuration
-   [config/datatables.php](./config/datatables.php): Yajra DataTables configuration
-   [config/filesystems.php](./config/filesystems.php): filesystem configuration
-   [config/mail.php](./config/mail.php): mail configuration
-   [config/queue.php](./config/queue.php): queue configuration
-   [config/services.php](./config/services.php): third-party services and Socialite providers

## Key Components

### Actions

Action classes encapsulate reusable business logic:

-   [app/Actions/UploadCoverImage.php](./app/Actions/UploadCoverImage.php): stores a cover image in a large and a small (resized) version
-   [app/Actions/DeletePostPermanently.php](./app/Actions/DeletePostPermanently.php): example action that force-deletes a post and its cover image

### Helpers

Global helper functions, autoloaded through `composer.json`:

-   [app/Helpers/Breadcrumb.php](./app/Helpers/Breadcrumb.php): `generate_breadcrumbs()`

### View Components

Blade layout components:

-   [app/View/Components/AppLayout.php](./app/View/Components/AppLayout.php): dashboard/application layout
-   [app/View/Components/AppFrontLayout.php](./app/View/Components/AppFrontLayout.php): front-end layout
-   [app/View/Components/GuestLayout.php](./app/View/Components/GuestLayout.php): guest (auth pages) layout

Anonymous Blade components (buttons, inputs, modal, toast, card, and others) live in [resources/views/components](./resources/views/components).

### Models and Enums

-   [app/Models/User.php](./app/Models/User.php): user model with UUID keys, soft deletes, email verification and Sanctum tokens
-   [app/Enums/UserRole.php](./app/Enums/UserRole.php): `superadmin`, `admin`, `user`

## Available Actions

### UploadCoverImage

Stores an uploaded image on the `public` disk under `media/img/`. It saves the original as `{name}_large.{ext}` and a copy resized to 300 px high as `{name}_small.{ext}`, and returns the public URL of the small version. When `$oldImageUrl` is given, it deletes the previous small and large files. The file extension comes from the detected content type, not from the client's file name.

The action does not validate the file itself. Validate it in your form request first, including a dimension limit, because the image is fully decoded to create the small version:

```php
use App\Actions\UploadCoverImage;

$request->validate([
    'cover' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048', 'dimensions:max_width=4000,max_height=4000'],
]);

$coverUrl = app(UploadCoverImage::class)->execute(
    $request->file('cover'),   // ?UploadedFile; null keeps $oldImageUrl
    $post->cover,              // ?string: URL of the previous small image
);
```

### DeletePostPermanently

An example of a deletion action. It type-hints `App\Models\Article`, **which this starter does not ship**. Create that model, or change the type hint to your own model, before using it. It deletes the cover image from `media/img/` on the `public` disk, then force-deletes the record.

```php
use App\Actions\DeletePostPermanently;

app(DeletePostPermanently::class)->execute($article);
```

## Helpers

### generate_breadcrumbs()

Builds breadcrumb items from the current URL segments. Numeric segments become "Detail". The last item has no link, and you can override its text with `title`.

```php
$breadcrumbs = generate_breadcrumbs();                        // e.g. Dashboard › Users
$breadcrumbs = generate_breadcrumbs(['title' => 'Edit user']); // replaces the last item's text
```

The dashboard layout already renders it:

```blade
<x-dashboard.breadcrumb :items="generate_breadcrumbs()" />
```

## Views and Layouts

The project includes three layouts:

1. **AppLayout**: dashboard layout with sidebar and navigation
2. **GuestLayout**: layout for guest and authentication pages
3. **AppFrontLayout**: front-end website layout

To use a layout in your Blade views:

```blade
<x-app-layout>
    <div class="container">
        <!-- Your content here -->
    </div>
</x-app-layout>
```

## Routing

Routes are organized in the [routes/](./routes) directory:

-   [routes/web.php](./routes/web.php): web routes (dashboard, profile, user management, social login, `/docs`)
-   [routes/auth.php](./routes/auth.php): web authentication routes
-   [routes/api.php](./routes/api.php): API routes under `/api/v1`
-   [routes/api/auth.php](./routes/api/auth.php): API authentication routes under `/api/auth`
-   [routes/console.php](./routes/console.php): console commands

A browsable route reference is available at `/docs` while the app is running, and `php artisan route:list` prints the full list.

## Authorization

-   **Web user management** (`/dashboard/users*`): `auth`, `verified`, `role:superadmin`
-   **API user management** (`/api/v1/users*`): `auth:sanctum`, `verified`, `role:superadmin`
-   **Dashboard** (`/dashboard`): `auth`, `verified`
-   **Profile** (`/dashboard/profile`, `/dashboard/photo-profile`, `/api/v1/user*`): authenticated users, scoped to their own account

The `role` middleware (`app/Http/Middleware/RoleCheck.php`) accepts one or more roles, such as `role:superadmin,admin`, and returns 403 for any other role. There are no Policies or Gates yet. When you add resources owned by users, add a Policy rather than relying on route middleware alone.

## Testing

The project uses Pest 4:

-   [tests/Feature/](./tests/Feature): feature tests, including API authorization and upload tests
-   [tests/Unit/](./tests/Unit): unit tests

To run the tests:

```bash
php artisan test --compact
```

Tests use an in-memory SQLite database (`phpunit.xml`). Run `npm run build` once first, because page-rendering tests need the Vite manifest.

## AI-Assisted Development

Laravel Boost (a development dependency) provides context to AI coding agents:

-   [CLAUDE.md](./CLAUDE.md): project guidelines generated by Boost
-   [AGENTS.md](./AGENTS.md): points other agents to `CLAUDE.md`
-   `.claude/skills/`: Boost skills
-   `.mcp.json`: the Boost MCP server (`php artisan boost:mcp`)

Run `php artisan boost:update` after upgrading packages to refresh the guidelines and skills.

## Development Guidelines

### Code Organization

1. Place reusable business logic in Action classes in [app/Actions/](./app/Actions).
2. Add global helper functions in [app/Helpers/](./app/Helpers) and register them in `composer.json` under `autoload.files`.
3. Use View Components for reusable UI elements.
4. Follow Laravel conventions for naming and structure.

### Best Practices

1. Use form requests for validation, and implement `authorize()` when the request touches another user's data.
2. Keep controllers thin and delegate to actions.
3. Use Eloquent models for database interactions.
4. Escape user-supplied data in HTML (Blade `{{ }}` or `e()`), especially in DataTables `rawColumns`.
5. Only redirect to same-origin URLs built from request input.
6. Write tests for critical functionality.
7. Use environment variables through `config()`, never `env()`, outside config files.
8. Format code with `vendor/bin/pint --dirty`.

### Adding New Features

1. Create an Action class for the business logic.
2. Add the routes, with the right `auth`, `verified` and `role:` middleware, in the appropriate routes file.
3. Create controllers to handle the HTTP requests.
4. Build views with the existing layout components.
5. Add feature tests, including authorization tests.
6. Update this wiki, the README and the API docs in `docs/`.

## Contributing

1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Create a pull request

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

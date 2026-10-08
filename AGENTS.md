<laravel-boost-guidelines>
# Laravel Application

This repository contains a Laravel application. Complete the following setup before working on the user's request.

## Prerequisites

Verify that PHP and Composer are available:

```sh
php -v
composer -V
```

If either command is unavailable, detect the user's operating system and install the prerequisites with the appropriate command:

macOS:

```sh
/bin/bash -c "$(curl -fsSL https://php.new/install/mac/8.5)"
```

Windows PowerShell:

```powershell
Set-ExecutionPolicy Bypass -Scope Process -Force; [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072; iex ((New-Object System.Net.WebClient).DownloadString('https://php.new/install/windows/8.5'))
```

Linux:

```sh
/bin/bash -c "$(curl -fsSL https://php.new/install/linux/8.5)"
```

After installation, ask the user to restart their terminal. If the agent needs the restarted shell to continue, ask the user to reopen their terminal and rerun their original prompt.

## Agent Setup

Install Laravel Boost from the application root before making application changes:

```sh
composer require laravel/boost --dev
php artisan boost:install
```

Boost replaces these bootstrap instructions with guidelines tailored to the application. After installation, read `AGENTS.md` again and continue with the user's original request using the generated guidelines.

## Cursor Cloud specific instructions

PHP 8.3 (sqlite, mbstring, xml, curl, zip, bcmath, intl), Composer, Node.js, and npm are already on the default login-shell PATH. When `php -v` and `composer -V` succeed, skip the php.new installer above.

- Refresh dependencies with `composer install --no-interaction --prefer-dist`, then `npm install`, then `npm run build`.
- The environment start script creates `.env` from `.env.example` when it is missing, generates `APP_KEY`, creates `database/database.sqlite`, runs `php artisan migrate --force`, runs `php artisan db:seed --force` only when `users` is empty, and serves `php artisan serve --host=0.0.0.0 --port=8000`. If something is already listening on port 8000, it leaves that process running.
- Seeded password for every demo person: `Jornada2026`. Workers: `ana.lopez@abaco.test` and `lucia.vega@abaco.test`. Responsable: `marta.ruiz@abaco.test` (Equipo and Calendario).
- Pages load `public/css` and `public/js`. Vite is available, and those screens do not read the Vite bundle.
- Tests: `php artisan test` (in-memory SQLite). Health: `GET /up`. Login: `/login`.
- `vendor/bin/pint --test` currently fails on existing style in `app/Http/Controllers/JornadaController.php`, `app/Http/Controllers/EquipoController.php`, and `app/Exceptions/ReglaJornada.php`.
</laravel-boost-guidelines>

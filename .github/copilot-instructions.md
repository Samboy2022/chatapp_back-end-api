
# Copilot Instructions

This document provides guidance for AI coding agents to effectively contribute to the ChatApp backend codebase.

## Project Overview

This is a Laravel 12 backend for a real-time chat application, similar to WhatsApp. It provides a RESTful API for a React Native mobile client and includes an admin panel for user and system management.

- **Backend:** Laravel 12, PHP 8.2+
- **Database:** MySQL (assumed, as is typical for Laravel)
- **Real-time:** Dual broadcasting with Laravel Reverb (default) and Pusher.
- **Authentication:** Laravel Sanctum for API token-based authentication.
- **Authorization:** `spatie/laravel-permission` for roles and permissions.

## Key Architectural Concepts

### Dual Broadcast System

The application is configured to use two different websocket broadcasting systems: Laravel Reverb and Pusher.

- **Laravel Reverb:** The primary, self-hosted solution. Configuration is in `config/reverb.php`. The server is started with `php artisan reverb:start`.
- **Pusher:** A cloud-based service, used as a fallback or for different environments. Configuration is in `config/broadcasting.php` and `.env`.

When working with real-time features, be aware of which driver is currently active. Events are defined in `app/Events` and broadcast on channels defined in `routes/channels.php`.

### API Structure

The API is versioned and separated into two main groups:

- **Public API (`routes/api.php`):** Endpoints for the mobile application. These are prefixed with `/api`. Controllers are in `app/Http/Controllers/Api`.
- **Admin API (`routes/web.php`):** Endpoints for the admin panel. These are protected by web middleware and authentication. Controllers are in `app/Http/Controllers/Admin`.

### Testing

The project has a significant number of test files.

- **PHPUnit Tests:** The standard Laravel testing framework is used. Tests are in the `tests/` directory. Run with `php artisan test`.
- **Manual Test Scripts:** There are numerous `test-*.php` and `comprehensive-*.php` scripts in the root directory. These are for manual, ad-hoc testing of specific features. Before running them, inspect the script to understand its purpose and potential side effects.

## Developer Workflow

1.  **Setup:**
    - Run `composer install`.
    - Copy `.env.example` to `.env` and configure your database and other services.
    - Run `php artisan key:generate`.
    - Run `php artisan migrate --seed` to set up the database.

2.  **Running the Application:**
    - `php artisan serve` to start the web server.
    - `php artisan reverb:start` to start the websocket server.

3.  **Debugging:**
    - Use the `debug-*.php` scripts in the root for quick debugging of specific functionalities.
    - Laravel Telescope is not installed by default, but can be a useful addition.

## Important Files and Directories

- `app/Http/Controllers/Api/`: Main controllers for the chat application's public API.
- `app/Http/Controllers/Admin/`: Controllers for the admin panel.
- `routes/api.php`: API routes for the mobile client.
- `routes/channels.php`: Defines the channels for broadcasting events.
- `config/broadcasting.php`: Configuration for Pusher and Reverb.
- `config/reverb.php`: Specific configuration for Laravel Reverb.
- `run-all-tests.php`: A script to execute a suite of tests.
- `start-websocket-server.bat`/`.sh`: Scripts to start the websocket server.

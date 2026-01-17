# Recollectie

A cozy personal productivity app for storing ideas and managing tasks. Built with Laravel and Vue 3.

## What is this?

Recollectie is a personal space with two modules:

- **Memory Box** - Store your thoughts, ideas, dreams, and random sparks of inspiration. Tag them with pretty colors and emojis, filter through them later, and never lose a brilliant idea again.
- **Checklist** - Manage your tasks with multiple lists, priorities, and a clean interface.

## Features

### Memory Box
- Write and store ideas
- Organize with colorful tags and emojis
- Filter ideas by tag

### Checklist
- Create multiple task lists
- Add tasks with descriptions and priorities
- Mark tasks as complete

### Personalization
- **Color themes** - Choose from pink, blue, green, or orange
- **Dark mode** - Light and dark theme support
- **User accounts** - Your data stays private and synced

### Account
- Secure authentication with password strength meter
- Profile management
- Account deletion

## Tech Stack

- **Laravel 11** - PHP framework with session authentication
- **Vue 3** - Interactive components
- **Vite** - Fast development and build tooling
- **SCSS** - Modular styling with CSS custom properties for theming
- **Blade** - Server-rendered templates
- **SQLite/MySQL** - Database with utf8mb4 for emoji support

## Getting Started

### Prerequisites

- PHP 8.2+
- Composer
- Node.js 22+
- SQLite or MySQL

### Installation

1. Clone the repo and install dependencies:
   ```bash
   composer install
   npm install
   ```

2. Set up your environment:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. Configure your database in `.env`:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=recollection
   DB_USERNAME=your_user
   DB_PASSWORD=your_password
   ```

4. Run migrations:
   ```bash
   php artisan migrate
   ```

5. Build assets:
   ```bash
   npm run build
   ```

6. Start the server:
   ```bash
   php artisan serve
   ```

7. Open http://localhost:8000

### Development

For development with hot-reload:

```bash
npm run dev
```

Then in another terminal:
```bash
php artisan serve
```

## Deployment

```bash
git pull origin master
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Language

The interface is in Dutch.

## License

Do whatever you want with it!

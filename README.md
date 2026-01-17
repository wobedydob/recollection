# ✨ Recollectie

A cute little memory box for all your wonderful ideas! Built with love using Laravel and Vue 3.

## 💭 What is this?

Recollectie is a cozy place to store your thoughts, ideas, dreams, and random sparks of inspiration. Tag them with pretty colors and emojis, filter through them later, and never lose a brilliant idea again!

## 🌸 Features

- **Write ideas** - Jot down whatever's on your mind
- **Colorful tags** - Organize with beautiful pastel-colored tags and emojis
- **Filter & find** - Quickly filter ideas by tag with smooth loading animations
- **User accounts** - Your ideas stay private and synced
- **Password security** - Visual strength meter and requirements checker
- **Cute design** - Pastel colors, smooth animations, and a sprinkle of magic

## 🏗️ Project Structure

```
recollection/
├── app/
│   ├── Http/Controllers/   # Controllers
│   │   ├── AuthController.php
│   │   ├── IdeaController.php
│   │   └── TagController.php
│   └── Models/             # Eloquent models
│       ├── User.php
│       ├── Idea.php
│       └── Tag.php
├── database/
│   └── migrations/         # Database migrations
├── resources/
│   ├── css/
│   │   └── app.css         # Pastel theme styles
│   ├── js/
│   │   ├── app.js          # Vue initialization
│   │   └── components/     # Vue components
│   │       ├── IdeasApp.vue
│   │       ├── PasswordInput.vue
│   │       └── PasswordStrength.vue
│   └── views/
│       ├── layouts/        # Blade layouts
│       │   ├── app.blade.php
│       │   └── guest.blade.php
│       ├── auth/           # Auth pages
│       │   ├── login.blade.php
│       │   └── register.blade.php
│       ├── ideas/
│       │   └── index.blade.php
│       └── profile.blade.php
├── routes/
│   ├── api.php             # API routes (for Vue components)
│   └── web.php             # Web routes
└── vite.config.js          # Vite + Vue configuration
```

## 🚀 Getting Started

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

7. Open http://localhost:8000 and start collecting memories! 🎉

### Development

For development with hot-reload:

```bash
npm run dev
```

Then in another terminal:
```bash
php artisan serve
```

## 🛠️ Tech Stack

- **Laravel 11** - PHP framework with session authentication
- **Vue 3** - Interactive components (ideas list, password strength)
- **Vite** - Fast development and build tooling
- **Blade** - Server-rendered templates
- **SQLite/MySQL** - Database with utf8mb4 for emoji support

## 🇳🇱 Language

The interface is in Dutch because it was made with love for someone special!

## 📝 License

Do whatever you want with it! Just spread some joy ✨

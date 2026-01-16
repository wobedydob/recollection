# ✨ Recollection

A cute little memory box for all your wonderful ideas! Built with PHP + Nuxt 3.

## 💭 What is this?

Recollection is a cozy place to store your thoughts, ideas, dreams, and random sparks of inspiration. Tag them with pretty colors and emojis, filter through them later, and never lose a brilliant idea again!

## 🌸 Features

- **Write ideas** - Jot down whatever's on your mind
- **Colorful tags** - Organize with beautiful pastel-colored tags and optional emojis
- **Filter & find** - Quickly filter ideas by tag
- **User accounts** - Your ideas stay private and synced
- **Cute design** - Pastel colors, smooth animations, and a sprinkle of magic

## 📁 Project Structure

```
recollection/
├── api/                  # PHP Backend API
│   ├── auth/             # Authentication endpoints
│   ├── ideas/            # Ideas CRUD
│   └── tags/             # Tags CRUD
├── frontend/             # Nuxt 3 Frontend (source)
│   ├── app/
│   └── package.json
├── public/               # Built frontend (served by Apache)
├── .htaccess             # Apache routing
├── .env                  # Environment config
├── database.sql          # Database schema
└── build.sh              # Build script
```

## 🚀 Getting Started

### Prerequisites

- PHP 7.4+ with PDO MySQL
- MySQL database
- Apache with mod_rewrite
- Node.js 22+ (for building frontend)

### Installation

1. Clone the repo and set up environment:
   ```bash
   cp .env.example .env
   ```

2. Edit `.env` with your database credentials:
   ```
   DB_HOST=localhost
   DB_USER=your_user
   DB_PASSWORD=your_password
   DB_NAME=recollection
   JWT_SECRET=your-secret-key-here
   ```

3. Create the database:
   ```bash
   mysql -u root -p < database.sql
   ```

4. Install frontend dependencies and build:
   ```bash
   cd frontend
   npm install
   cd ..
   ./build.sh
   ```

5. Set up Apache virtual host pointing to the project root.

6. Open your site and start collecting memories! 🎉

### Development

To work on the frontend with hot-reload:
```bash
cd frontend
npm run dev
```

The dev server runs on http://localhost:3000 and proxies API calls to your Apache.

## 🛠️ Tech Stack

- **PHP** - Backend API
- **Nuxt 3** - Vue frontend (SPA mode)
- **MySQL** - Database
- **JWT** - Authentication with httpOnly cookies

## 🇳🇱 Language

The interface is in Dutch because it was made with love for someone special!

## 📝 License

Do whatever you want with it! Just spread some joy ✨

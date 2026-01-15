# ✨ Recollection

A cute little memory box for all your wonderful ideas! Built with love using Nuxt 3.

## 💭 What is this?

Recollection is a cozy place to store your thoughts, ideas, dreams, and random sparks of inspiration. Tag them with pretty colors and emojis, filter through them later, and never lose a brilliant idea again!

## 🌸 Features

- **Write ideas** - Jot down whatever's on your mind
- **Colorful tags** - Organize with beautiful pastel-colored tags and optional emojis
- **Filter & find** - Quickly filter ideas by tag
- **User accounts** - Your ideas stay private and synced
- **Cute design** - Pastel colors, smooth animations, and a sprinkle of magic

## 🚀 Getting Started

### Prerequisites

- Node.js 22.12+
- MySQL database

### Installation

1. Clone the repo and install dependencies:
   ```bash
   npm install
   ```

2. Set up your environment variables:
   ```bash
   cp .env.example .env
   ```

   Then edit `.env` with your database credentials:
   ```
   DB_HOST=localhost
   DB_USER=your_user
   DB_PASSWORD=your_password
   DB_NAME=database_name
   JWT_SECRET=your-secret-key-here
   ```

3. Create the database:
   ```bash
   mysql -u root -p < database.sql
   ```

4. Start the dev server:
   ```bash
   npm run dev
   ```

5. Open http://localhost:3000 and start collecting memories! 🎉

## 🛠️ Tech Stack

- **Nuxt 3** - Vue framework with SSR
- **MySQL** - Database
- **JWT** - Authentication with httpOnly cookies
- **TypeScript** - Type safety

## 🇳🇱 Language

The interface is in Dutch because it was made with love for someone special!

## 📝 License

Do whatever you want with it! Just spread some joy ✨

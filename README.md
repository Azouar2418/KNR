# KNR

A manga / manhwa reading CMS built with **Laravel 10** (PHP 8.2+).

## Features

- Manga and chapter pages, search, random pick, bookmarks, reading history and view counts
- Authentication with Laravel Fortify (including two-factor) and social login (Socialite)
- Comments with reactions and reports
- Full admin dashboard: manga, chapters (bulk and chunked upload), genres, types, statuses, pages, ads, slider, users, roles and permissions
- SEO, mail, upload and theme settings; sitemap generation; backups; log viewer
- Theme system (`themes/default`, Tailwind + Vite) and plugin system (`plugins/<name>/plugin.php`)
- Arabic and English translations
- Web installer for first-time setup

## Requirements

- PHP 8.2 or newer (with `gd`, `mbstring`, `fileinfo`, `pdo_mysql`, `zip`)
- Composer
- Node.js 18+ (only to rebuild frontend assets)
- MySQL / MariaDB

## Setup

```bash
git clone https://github.com/Azouar2418/KNR.git
cd KNR

composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate
```

Set your database credentials and `APP_URL` in `.env`, then either open the site in the browser to run the web installer, or run `php artisan migrate`.

Make sure `storage/` and `bootstrap/cache/` are writable by the web server.

## Frontend

Compiled assets are committed in `public/build`. To rebuild them:

```bash
npm install
npm run build:default
```

## Production notes

- Keep `APP_DEBUG=false` and never commit `.env`.
- Point the web server document root at `public/`.
- `vendor/` is not tracked in git, so run `composer install` on every deploy.

## License

MIT (Laravel framework).

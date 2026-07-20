# UP1 — setup for testing on another PC

This does **not** change the original developer machine. The tester gets their own copy, their own `.env`, and their own SQLite database.

## What you need installed

- PHP 8.3+ (with sqlite, mbstring, openssl, pdo, tokenizer, xml, curl, fileinfo, gd)
- [Composer](https://getcomposer.org/)
- [Node.js 20+](https://nodejs.org/)
- Git

## Get the code

```bash
git clone https://github.com/1fancy/up1.git
cd up1
```

## One-command setup (Windows)

```powershell
powershell -ExecutionPolicy Bypass -File .\scripts\setup-local.ps1
```

## Or manual setup (Windows / Mac / Linux)

```bash
composer run setup:tester
```

That installs PHP/JS dependencies, creates `.env`, builds a fresh SQLite DB with demo data, and builds the frontend.

## Run the app

```bash
composer run dev
```

Open: [http://127.0.0.1:8000](http://127.0.0.1:8000)

## Demo accounts

| Role | URL | Login | Password |
|------|-----|-------|----------|
| Admin | `/adminos/login` | `up1` | `147235689` |
| Business | `/` | `mohamed@example.com` | `password` |
| Business | `/` | `fatima@example.com` | `password` |

## Reset demo data later

```bash
php artisan migrate:fresh --seed
```

## Optional: quick share without installing (developer PC only)

If the developer prefers a temporary public link instead of a clone:

```powershell
composer share
```

That uses a Cloudflare tunnel to the developer’s running app. It does not change `.env`. Stop with Ctrl+C.

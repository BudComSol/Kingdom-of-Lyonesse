# 🏰 Kingdom of Lyonesse — Medieval Fantasy MMORPG

![PHP Version](https://img.shields.io/badge/PHP-8.0%20--%208.3%2B-blue.svg)
![Database](https://img.shields.io/badge/Database-MySQL%20%2F%20MariaDB-orange.svg)
![Security](https://img.shields.io/badge/Security-BCrypt%20%2B%20Strict%20Sessions-green.svg)
![License](https://img.shields.io/badge/License-MIT-yellow.svg)
![Design](https://img.shields.io/badge/Design-Dark%20Fantasy%20RPG-gold.svg)

**Kingdom of Lyonesse** is a browser-based medieval fantasy massively multiplayer online role-playing game (MMORPG). Engineered with a modern, lightweight PHP architecture, it is designed for ultra-fast response times, high scalability, and seamless deployment across shared web hosting (cPanel), VPS/dedicated servers, or local environments.

---

## 📑 Table of Contents
1. [Technical Specifications](#-technical-specifications)
2. [Key Gameplay Features](#-key-gameplay-features)
3. [Project Directory & File Structure](#-project-directory--file-structure)
4. [Configuration Guide (`.env`)](#-configuration-guide-env)
5. [Installation & Deployment Guide](#-installation--deployment-guide)
   - [A. Shared Hosting (cPanel / LiteSpeed / Apache)](#a-shared-hosting-cpanel--litespeed--apache)
   - [B. VPS / Dedicated Server (Nginx / Apache)](#b-vps--dedicated-server-nginx--apache)
   - [C. Local Development (PHP Built-in Server / XAMPP / Laragon)](#c-local-development-php-built-in-server--xampp--laragon)
6. [Automated Scheduled Tasks (`game_cron`)](#-automated-scheduled-tasks-game_cron)
7. [Security & Optimization](#-security--optimization)
8. [Frequently Asked Questions & Administration](#-frequently-asked-questions--administration)
9. [License](#-license)

---

## ⚙️ Technical Specifications

| Requirement | Specification |
| :--- | :--- |
| **Backend Language** | PHP 8.0, 8.1, 8.2, 8.3+ (Fully typed, compatible with `short_open_tag = Off`) |
| **Database Engine** | MySQL 5.7+ / MariaDB 10.3+ via PDO (`utf8mb4` charset) |
| **Required PHP Extensions** | `pdo_mysql`, `mbstring`, `json`, `session`, `filter` |
| **Frontend Technologies** | Semantic HTML5, Vanilla CSS3 (Dark Fantasy Glassmorphism), Vanilla JavaScript |
| **Audio Engine** | Procedural Web Audio API Synthesizer (`audio.js`) for SFX and background music |
| **Authentication Engine** | `password_hash()` utilizing **BCrypt** with automatic legacy MD5 migration |
| **Compatible Web Servers** | Apache 2.4+, Nginx, LiteSpeed, OpenLiteSpeed, PHP Built-in Server |

---

## ⚔️ Key Gameplay Features

* **Dynamic Real-Time Combat (AJAX):** Turn-based Dark Forest encounters featuring floating combat text (`-120 💥 CRIT!`, `+45 💚`), smooth health bar transitions, tactile audio effects, and direct boss loot drops without full-page reloads. Dropped equipment retains its item's set rarity and slot.
* **Guild & Clan System:** Clan creation, Clan Hall, member hierarchy (Leader, Officer, Member), passive clan buffs (`+5%` to `+25%` earned Coins and Exp), and cooperative battles against the **Weekly Clan Raid Boss** (*Ancient Abyssal Leviathan*).
* **Inventory & Crafting Hub:** 8 equipment slots, 5 rarity tiers (Common, Rare, Epic, Legendary, Mythic), blacksmith forging (+1 to +10), rune socketing, 1-click batch item dismantling, and working **4-Piece / 8-Piece Set Resonance Bonuses** to Power and Health (plus Critical Strike at 8 pieces).
* **Daily Engagement & Progression:** 7-day progressive daily login streak calendar, real-time energy ticker countdowns, and a 10-tier Hero Achievement & Equipable Title system.
* **PvP Arena & Royal Tournament:** Asynchronous player challenges against opponents within 5 levels, settled from both players' combat stats, plus tournament points and weekly reward payouts.
* **Economy & Royal Treasury:** PayPal Checkout for Ruby packs, voucher promo code redemption, and currency exchange.
* **Animated Toast Notifications:** Floating notifications with sound effects and auto-dismissal.

---

## 📂 Project Directory & File Structure

```text
├── .env                      <- Database credentials and environment configuration
├── .env.example              <- Template configuration file
├── .htaccess                 <- Apache rewrite rules and security protection for config files
├── config.php                <- .env parser, PDO connection & session security
├── migrate.php               <- Command-line database schema migrations
├── database.sql (baza.sql)   <- Complete database dump in English (UTF-8)
│
├── core/
│   ├── func.php              <- Core game engine: head(), foot(), database helpers & UI bars
│   ├── level.php             <- Hero experience progression tables
│   └── us_level.php          <- Stat calculation and leveling formulas
│
├── api_combat.php            <- JSON API endpoint for dynamic combat and boss loot rolls
├── index.php                 <- Landing portal, quick guest registration, login & tutorial
├── profile.php               <- Hero character sheet, masteries, attributes, titles & clan info
├── item.php                  <- Equipment manager, stat inspector & Set Resonance indicators
├── bag.php                   <- Backpack inventory, item comparison & 1-click batch dismantle
├── lec.php                   <- Dark Forest hunting grounds & zone monster selector
├── arena.php                 <- Asynchronous player challenges, duel results & rewards
├── daily.php                 <- 7-Day progressive daily login rewards calendar
├── achievements.php          <- Hero achievement tracker & equipable title registry
├── clan.php                  <- Clan Castle, guild buffs, member roster & Weekly Raid Boss
├── shop.php                  <- Town market: armory, blacksmith forge, runes, masteries, trader
├── paywk.php                 <- Royal Treasury, PayPal Ruby purchases & promo voucher redemption
├── chat.php                  <- Global world chat and private clan chat
├── mail.php / contacts.php   <- Player-to-player messaging and friends list
├── save.php                  <- Account registration & linking (+10 Rubies & VIP bonus)
├── avatar.php                <- Interactive hero portrait selector
├── top.php / tour.php        <- Leaderboards, Hall of Fame & Royal Tournament
├── update.php                <- Administrative control panel for news broadcasts & metrics
│
├── templates/
│   ├── fight_lec.php         <- Dynamic combat interface for monster encounters
│   ├── index.php             <- Main Town Square hub after completing onboarding
│   ├── start.php / start2.php<- Guided prologue tutorial for new heroes
│   ├── shop1.php - shop5.php <- Individual merchant templates (Forge, Runes, Trainer, Market)
│   └── tavern/               <- Storyline quests and narrative tavern chapters
│
├── game_cron/                <- Automated background cron jobs
│   ├── tour.php              <- Weekly Royal Tournament reset and rewards payout
│   ├── sunduk.php / sunduk2.php <- Daily common and rare treasure chest respawns
│   └── clear_inactive.php    <- Session cleanup and stale cache purges
│
└── style/
    ├── style.css             <- Readable source for the Dark Fantasy RPG stylesheet
    ├── style.min.css         <- Minified stylesheet inlined by head()
    ├── toast.js              <- Client-side animated toast notification engine
    ├── audio.js              <- Web Audio API sound synthesizer engine
    └── favicon.ico           <- Official game favicon
```

---

## 🛠️ Configuration Guide (`.env`)

All game settings are managed through the `.env` file located in the root directory. **Do not hardcode database credentials into PHP files.**

### Example `.env` Configuration:

```ini
# =====================================================================
# Application Configuration
# =====================================================================
APP_NAME="Kingdom of Lyonesse"
APP_ENV=production
APP_DEBUG=false
APP_KEY=replace_with_a_unique_random_secret_key

# Use 'auto' for automatic URL detection, or specify your full domain (e.g., https://yourdomain.com)
BASE_URL=auto

# =====================================================================
# Database Configuration (MySQL / MariaDB)
# =====================================================================
DB_HOST=localhost
DB_PORT=3306
DB_NAME=your_database_name
DB_USER=your_database_user
DB_PASS=your_secure_password
DB_CHARSET=utf8mb4

# =====================================================================
# Security & Session Configuration
# =====================================================================
SESSION_LIFETIME=2592000
SESSION_SECURE=false
SESSION_SAMESITE=Lax
SESSION_NAME=LYONESSE_SESSID
```

> [!TIP]
> **Automatic Base URL (`BASE_URL=auto`):** Keeping this setting on `auto` enables the game to automatically identify whether it is running on `http://localhost:7000`, in a subfolder, or on a live production domain with HTTPS, eliminating the need for manual URL updates across environments.

### PayPal Checkout

1. Generate a unique application encryption key and set it as `APP_KEY` in `.env` (for example, `php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"`). Keep this key private and unchanged; it encrypts the PayPal Secret stored in the database.
2. Log in as a game administrator (`adm = 3`) and open **Admin Panel → PayPal & Ruby Pack Settings**.
3. Enter the PayPal REST API Client ID and Secret. Start in **Sandbox**, test with PayPal sandbox accounts, and switch to **Live** with live credentials when ready. Ruby pack amounts, prices (USD), and VIP days are editable on this page.
4. Configure a public HTTPS `BASE_URL` before accepting live payments. Payment approval and capture are handled server-side; Rubies and VIP time are granted only after PayPal confirms the captured amount and currency.

Back up `APP_KEY` securely with the database. If it is lost or changed, saved PayPal credentials can no longer be decrypted and must be entered again.

---

## 🚀 Installation & Deployment Guide

### A. Shared Hosting (cPanel / LiteSpeed / Apache)

1. **Upload Game Files:**
   - Compress the project files into a `.zip` archive.
   - Log into your cPanel $\rightarrow$ **File Manager** $\rightarrow$ Navigate to `public_html/` (or your subdomain directory).
   - Upload and extract the archive.

2. **Create MySQL Database:**
   - In cPanel, navigate to **MySQL Database Wizard** (or **MySQL Databases**).
   - Create a new database (e.g., `myuser_lyonesse`).
   - Create a database user with a secure password.
   - Grant the user **ALL PRIVILEGES** on the database.

3. **Import SQL Database Dump:**
   - Open **phpMyAdmin** from cPanel.
   - Select your newly created database on the left sidebar.
   - Go to the **Import** tab, choose `database.sql` (or `baza.sql`), and click **Import** / **Go**.

4. **Configure the `.env` File:**
   - In cPanel File Manager, ensure the setting **"Show Hidden Files (dotfiles)"** is checked.
   - Edit the `.env` file with your database credentials:
     ```ini
     DB_HOST=localhost
     DB_NAME=myuser_lyonesse
     DB_USER=myuser_dbuser
     DB_PASS=your_password_here
     BASE_URL=auto
     ```

5. **Verify PHP Version:**
   - In cPanel $\rightarrow$ **Select PHP Version** (or MultiPHP Manager), set PHP to **8.1 or 8.2**.
   - Verify that `pdo_mysql`, `mbstring`, and `json` extensions are enabled.

6. **Run database migrations:** From a terminal in the game directory, run `php migrate.php`.
   Run this after importing the database and before serving traffic, and again when deploying updates.
   The configured database user needs permission to create tables and alter `users`.

---

### B. VPS / Dedicated Server (Nginx / Apache)

1. **Deploy Files & Set Permissions:**
   ```bash
   cd /var/www/lyonesse
   chown -R www-data:www-data /var/www/lyonesse
   chmod -R 755 /var/www/lyonesse
   ```

2. **Import Database via Terminal:**
   ```bash
   mysql -u your_username -p your_database_name < /var/www/lyonesse/database.sql
   ```
   Configure `.env`, then run `php migrate.php` from the game directory before serving traffic.
   Repeat the migration command when deploying updates.

3. **Nginx VirtualHost Configuration Example:**
   ```nginx
   server {
       listen 80;
       server_name yourdomain.com www.yourdomain.com;
       root /var/www/lyonesse;
       index index.php index.html;

       # Block direct access to sensitive configuration files
       location ~ /\.(env|git|ht) {
           deny all;
       }

       location ~ \.(sql|log)$ {
           deny all;
       }

       location / {
           try_files $uri $uri/ /index.php?$args;
       }

       location ~ \.php$ {
           include snippets/fastcgi-php.conf;
           fastcgi_pass unix:/run/php/php8.2-fpm.sock;
           fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
           include fastcgi_params;
       }
   }
   ```

---

### C. Local Development (PHP Built-in Server / XAMPP / Laragon)

1. **Clone or Copy Files:**
   - Extract files to your local root directory (e.g., `C:/xampp/htdocs/setst.site`).
   - Start the built-in development server:
     ```bash
     php -S localhost:7000
     ```

2. **Setup Local Database in phpMyAdmin:**
   - Open `http://localhost/phpmyadmin`.
   - Create a database named `set` or `lyonesse`.
   - Import `database.sql`.

3. **Adjust `.env`:**
   ```ini
   DB_HOST=localhost
   DB_NAME=set
   DB_USER=root
   DB_PASS=
   BASE_URL=auto
   ```

4. **Run database migrations:** Run `php migrate.php` from the game directory after the import
   and configuration, and again when deploying updates.

5. **Launch Game in Browser:**
   - Visit `http://localhost:7000` or `http://localhost/setst.site`.

---

## ⏱️ Automated Scheduled Tasks (`game_cron`)

To ensure recurring world events, weekly tournaments, and daily chest respawns operate autonomously, configure the following cron jobs via cPanel or Linux `crontab -e`:

```bash
# Weekly Royal Tournament Reset (Every Monday at 00:00)
0 0 * * 1 php /absolute/path/to/game_cron/tour.php >/dev/null 2>&1

# Common Daily Chest Respawn (Every 6 Hours)
0 */6 * * * php /absolute/path/to/game_cron/sunduk.php >/dev/null 2>&1

# Rare Daily Chest Respawn (Every 12 Hours)
0 */12 * * * php /absolute/path/to/game_cron/sunduk2.php >/dev/null 2>&1
```

*(On cPanel, replace `/absolute/path/to/` with your home directory path, e.g., `/home/username/public_html/game_cron/tour.php`).*

---

## 🛡️ Security & Optimization

1. **Password Protection:** Passwords are encrypted using **BCrypt** ($2y$) via `password_hash()`. Any legacy MD5 hashes are automatically and transparently upgraded to modern BCrypt upon user login.
2. **Confidential File Shielding:** The [.htaccess](file:///c:/Users/Equipo/Downloads/Nueva%20carpeta%20(3)/setst/setst.site/.htaccess) configuration forbids public HTTP access to `.env`, `.env.example`, `.sql` dumps, and error logs.
3. **Hardened Session Management:** Session cookies are configured with `HttpOnly = true`, `SameSite = Lax`, and `use_strict_mode = 1` to prevent cross-site scripting (XSS) and session hijacking.
4. **Self-Healing Schema Migrations:** The database engine automatically creates missing tables (`clans`, `clan_members`, `clan_boss`, `achievements_user`, `arena_challenges`) and columns on initial database connection.
5. **Render-blocking Styles:** `head()` inlines `style/style.min.css`, generated from the readable `style/style.css`, so the first render does not wait for a separate stylesheet request. Keep CSS asset URLs root-relative so they work on every route. Google Fonts load asynchronously with `display=swap` and a no-JavaScript fallback. Inlining preserves the initial layout but includes the stylesheet in every HTML response instead of caching it separately.

---

## ❓ Frequently Asked Questions & Administration

#### 1. How do I resolve a "Database connection error"?
Verify that the database credentials in `.env` (`DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`) match your MySQL database settings and that the database user has been granted privileges on that database.

#### 2. How do I access the Game Administration Panel?
- Register a hero account in the game.
- Open phpMyAdmin, locate your hero in the `users` table, and change the `adm` column value to `3`.
- Log in to access the **Admin Panel** (`/update.php`) to create item sets, configure PayPal and Ruby packs, spawn NPC bots, broadcast news announcements, and manage bans.

#### 3. Why are CSS changes not appearing in the browser?
Press `Ctrl + F5` (or `Cmd + Shift + R` on macOS) to bypass browser caching. The stylesheet includes versioning parameters (`v=2.2.0`) to handle asset cache invalidation automatically.

---

## 📄 License

This project is open-source and distributed under the **[MIT License](file:///c:/Users/Equipo/Downloads/Nueva%20carpeta%20(3)/setst/setst.site/LICENSE)**.

```text
MIT License

Copyright (c) 2026 Kingdom of Lyonesse Contributors

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.
```

---

<p align="center">
  <b>Kingdom of Lyonesse MMORPG Engine</b> — Optimized for performance, gameplay depth, and modern web standards.
</p>

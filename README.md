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
8. [What I Have Done](#-what-i-have-done)
9. [Frequently Asked Questions & Administration](#-frequently-asked-questions--administration)
10. [License](#-license)

---

## ⚙️ Technical Specifications

| Requirement | Specification |
| :--- | :--- |
| **Backend Language** | PHP 8.0+ (compatible with `short_open_tag = Off`) |
| **Database Engine** | MySQL 5.7+ / MariaDB 10.3+ via PDO (`utf8mb4` charset) |
| **Required PHP Extensions** | `pdo_mysql`, `mbstring`, `json`, `session`, `filter` |
| **Frontend Technologies** | Semantic HTML5, Vanilla CSS3 (Dark Fantasy Glassmorphism), Vanilla JavaScript |
| **Audio Engine** | Procedurally synthesized background music and sound effects via the Web Audio API (`audio.js`) |
| **Authentication Engine** | `password_hash()` utilizing **BCrypt** with automatic legacy MD5 migration |
| **Compatible Web Servers** | Apache 2.4+, Nginx, LiteSpeed, OpenLiteSpeed, PHP Built-in Server |

---

## ⚔️ Key Gameplay Features

* **Dynamic Real-Time Combat (AJAX):** Turn-based Dark Forest encounters featuring floating combat text (`-120 💥 CRIT!`, `+45 💚`), smooth health bar transitions, tactile audio effects, and direct boss loot drops without full-page reloads. Dropped equipment retains its item's set rarity and slot.
  The header displays experience, available Arena battles, and Dark Forest energy; combat health remains visible in Forest and Tavern fights.
* **Expedition Map:** Discover five Dark Forest landmarks in order. Expeditions use Forest Energy and existing combat; victories permanently reveal each player's map and its stories.
* **Guild & Clan System:** Clan creation, Clan Hall, member hierarchy (Leader, Officer, Member), passive clan buffs (`+5%` to `+25%` earned Coins and Exp), and cooperative battles against the **Weekly Clan Raid Boss** (*Ancient Abyssal Leviathan*).
* **Inventory & Crafting Hub:** 8 equipment slots, 5 rarity tiers (Common, Rare, Epic, Legendary, Mythic), blacksmith forging (+1 to +10), rune socketing, 1-click batch item dismantling, and working **4-Piece / 8-Piece Set Resonance Bonuses** to Power and Health (plus Critical Strike at 8 pieces).
* **Daily Engagement & Progression:** 7-day progressive daily login streak calendar, real-time energy ticker countdowns, and a 10-tier Hero Achievement & Equipable Title system.
* **PvP Arena & Royal Tournament:** Asynchronous player challenges against opponents within 5 levels, settled from both players' combat stats, plus tournament points and weekly reward payouts.
* **Bordello:** Seek company for a one-hour Power bonus or purchase companions' secrets to reread. The bonus is captured when forest or Tavern fights start and when Arena combat stats are recorded.
* **Hero Wiki:** Heroes can create and edit shared guides, organize them by category, and link pages with `[[Page title]]`. Previous revisions remain available in page history; simultaneous edits are checked before saving.
* **Economy & Royal Treasury:** PayPal Checkout for Ruby packs, voucher promo code redemption, and currency exchange.
* **Animated Toast Notifications:** Floating notifications with sound effects and auto-dismissal.
* **Animated Landing Page:** A CSS-drawn dragon logo sits above the entry button, while a compact, winged dragon flies across the hero and periodically breathes fire. Administrators can turn the flying dragon on or off under **Admin Panel → Landing Page Dragon**; it is also hidden when reduced motion is preferred. The standalone logo can be viewed at `/logo-preview.html`.

---

## 📂 Project Directory & File Structure

```text
.
├── .env.example              <- Template for application, database, and session settings
├── .htaccess                 <- Apache security headers and protected-file rules
├── config.php                <- Environment parsing, PDO setup, sessions, and shared constants
├── migrate.php               <- Command-line database schema migration and setup
├── index.php                 <- Landing page, registration, login, and onboarding entry point
├── logo-preview.html         <- Standalone CSS logo preview
├── save.php                  <- Account registration and login-linking flows
├── profile.php               <- Hero profile, attributes, titles, and clan details
├── bag.php / item.php        <- Inventory, equipment, crafting, and item inspection
├── lec.php                   <- Dark Forest hunting grounds and monster selection
├── exploration.php           <- Expedition map and landmark progression
├── arena.php                 <- PvP challenges, arena combat, and tournament flows
├── bordello.php              <- Companionship, morale, and purchasable stories
├── wiki.php                  <- Collaborative Hero wiki and revision history
├── daily.php                 <- Daily login streak and reward calendar
├── achievements.php          <- Achievement and equipable-title system
├── clan.php                  <- Clan Castle, members, buffs, and clan raid
├── shop.php                  <- Armory, forge, runes, trainer, and trader
├── paywk.php                 <- Royal Treasury and PayPal voucher checkout
├── chat.php / mail.php       <- Global, clan, and private player messaging
├── contacts.php / ref.php    <- Friends and referral features
├── factions.php              <- Faction selection and hero portraits
├── top.php / tour.php        <- Leaderboards and Royal Tournament
├── update.php                <- Administrative control panel
├── api_combat.php            <- AJAX combat and loot JSON endpoint
├── create_item.php           <- Administrative item creation
├── ban_user.php              <- User moderation and ban management
├── bot_create.php            <- Administrative NPC bot creation
├── premium.php / settings.php<- Premium account and player settings
├── task.php / tavern.php     <- Recurring task and tavern quest flows
├── gold.php / skill.php      <- Currency exchange and hero skills
├── news.php / ip_user.php    <- News and player IP lookup
├── history_ban.php / privacy.php <- Ban history and privacy information
│
├── _sql/
│   └── database.sql          <- Database schema and seed data
│
├── core/
│   ├── func.php              <- Shared helpers, page head/foot, and UI rendering
│   ├── level.php             <- Hero experience progression
│   ├── us_level.php          <- Hero stat and leveling calculations
│   ├── arena_combat.php      <- Arena combat resolution
│   ├── exploration.php       <- Expedition domain logic
│   └── paypal.php            <- PayPal checkout and credential handling
│
├── templates/
│   ├── index.php             <- Main Town Square page
│   ├── fight_lec.php         <- Monster encounter interface
│   ├── start.php             <- New-player tutorial
│   ├── start2.php            <- Prologue continuation
│   ├── shop1.php - shop5.php <- Merchant-specific UI templates
│   ├── tavern/               <- Tavern screens and quest chapters
│   ├── statistic.php         <- Character statistics
│   ├── statistic2.php        <- Battle statistics
│   └── smiles.php            <- Chat and emoji display helpers
│
├── about/                    <- Rules, help, agreement, and tour pages
├── chat/                     <- Chat route entry point
├── clan/                     <- Clan route entry point
├── contacts/                 <- Contacts route entry point
├── news/                     <- News route entry point
├── online/                   <- Online-player route entry point
├── paywk/                    <- Payment route entry point
├── top/                      <- Top-player route entry point
├── game_cron/
│   ├── arena.php             <- Scheduled arena tournament processing
│   ├── sunduk2.php           <- Scheduled rare chest processing
│   └── task.php              <- Scheduled recurring task processing
│
├── tests/
│   ├── *_test.php            <- Standalone PHP regression and integration tests
│   └── database_seed_test.php <- Database seed verification
│
├── style/
│   ├── style.css             <- Readable Dark Fantasy RPG stylesheet
│   ├── style.min.css         <- Minified stylesheet
│   ├── exploration.css       <- Expedition-map layout and effects
│   ├── logo-preview.css      <- CSS dragon logo and standalone preview styles
│   ├── cookie-notice.css     <- Cookie notice styles
│   ├── audio.js              <- Web Audio API sound synthesizer
│   └── toast.js              <- Animated toast notification engine
│
├── images/                   <- Character, item, skill, faction, arena, emoji, and UI assets
└── robots.txt, sitemap.xml   <- Search-engine directives and site map
```

---

## 🛠️ Configuration Guide (`.env`)

Environment, database, and session settings are read from the `.env` file in the project root; some game and integration settings are managed in the database through the Admin Panel. **Do not hardcode database credentials into PHP files.**

### Example `.env` Configuration:

```ini
# =====================================================================
# Application Configuration
# =====================================================================
APP_NAME="Kingdom of Lyonesse"
APP_ENV=development
APP_DEBUG=true
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
SESSION_SECURE=true
SESSION_SAMESITE=Lax
SESSION_NAME=LYONESSE_SESSID
```

> [!TIP]
> **Automatic Base URL (`BASE_URL=auto`):** The application derives the scheme and host from the current request. It does not detect an installation subfolder; deploy at the web root or configure routing and asset paths for your subfolder deployment. For production HTTPS, ensure your web server or trusted proxy reports the correct scheme, or set `BASE_URL` explicitly.

For local development over plain HTTP, set `SESSION_SECURE=false`; use `true` for HTTPS deployments so browsers send the session cookie only over HTTPS.

### PayPal Checkout

1. Generate a unique application encryption key and set it as `APP_KEY` in `.env` (for example, `php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"`). Keep this key private and unchanged; it encrypts the PayPal Secret stored in the database.
2. Log in as a game administrator (`adm = 3`) and open **Admin Panel → PayPal & Ruby Pack Settings**.
3. Enter the PayPal REST API Client ID and Secret. Start in **Sandbox**, test with PayPal sandbox accounts, and switch to **Live** with live credentials when ready. Ruby pack amounts, prices (USD), and VIP days are editable on this page.
4. Configure a public HTTPS `BASE_URL` before accepting live payments. Payment approval and capture are handled server-side; Rubies and VIP time are granted only after PayPal confirms the captured amount and currency.

Back up `APP_KEY` securely with the database. If it is lost or changed, saved PayPal credentials can no longer be decrypted and must be entered again.

---

## 🚀 Installation & Deployment Guide

> The database dump does not include an administrator account. After registering a hero account, promote it to administrator in phpMyAdmin using the instructions in the FAQ below.

Fresh installs start player IDs at 1. Deleting rows from an existing `users` table does not reset its MySQL auto-increment counter. If this is a fresh deployment with no players **and no retained player-related data**, stop registrations and run `ALTER TABLE users AUTO_INCREMENT = 1;` before creating the first player. Do not reset the counter on an existing game: reused IDs could associate old data with new players. If a freshly created table still starts above 1, check the MySQL `auto_increment_increment` and `auto_increment_offset` settings.

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
   - Go to the **Import** tab, choose `_sql/database.sql`, and click **Import** / **Go**.

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
   - In cPanel $\rightarrow$ **Select PHP Version** (or MultiPHP Manager), select PHP **8.0 or newer**. The included `.htaccess` may contain a cPanel-generated PHP handler; update or remove that handler to match the version selected in cPanel.
   - Verify that `pdo_mysql`, `mbstring`, and `json` extensions are enabled.
   - Before production, remove or disable the legacy `php_flag display_errors on` directive in `.htaccess`; displaying PHP errors publicly can expose sensitive details, and the directive may not be supported by PHP-FPM.

6. **Finish setup:** The imported `database.sql` includes the current schema, so no migration command is needed for a fresh install. Register a hero account, then promote it to administrator in phpMyAdmin using the FAQ instructions below. When upgrading an existing installation, back up the database and run `php migrate.php` from a terminal in the game directory. The configured database user needs permission to create tables and alter `users` and `lec_fight`.

The included `.htaccess` sets frame, MIME-sniffing, referrer, and permissions policies and sends HSTS on HTTPS responses. Enable Apache `mod_headers` and allow `.htaccess` header directives, or enable equivalent LiteSpeed support. Server-level redirects or responses generated before `.htaccess` is processed need the same headers configured at the server or proxy. When TLS terminates at a proxy or CDN, configure HSTS at that HTTPS edge rather than trusting a client-supplied forwarding header. HSTS applies for one year to the current host; enable it only when HTTPS is reliable. Verify the headers on the public HTTPS response.

The game does not enable cross-origin reads by default: `Access-Control-Allow-Origin` is unnecessary for same-origin requests, and a wildcard could expose authenticated game data. Configure a specific trusted origin at the server only if a cross-origin integration is required.

The online list shows last activity and the elapsed duration of the most recently active login session, including idle time. NPCs created by the bot creator have no login session; their uptime is shown from creation instead. Sessions already logged in when this feature is deployed start tracking on their next request. The list continues to show recently active heroes for up to three hours, even after logout; their session duration is unavailable after logout. Refresh the list to see changes to other heroes' activity, logins, and logouts.

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
   mysql -u your_username -p your_database_name < /var/www/lyonesse/_sql/database.sql
   ```
   Configure `.env`; the imported `database.sql` already includes the current schema. No migration command is needed for a fresh install. When upgrading an existing installation, back up the database and run `php migrate.php` from the game directory.

3. **Nginx VirtualHost Configuration Example:**
   ```nginx
   server {
       listen 80;
       server_name yourdomain.com www.yourdomain.com;
       root /var/www/lyonesse;
       index index.php index.html;

       add_header X-Frame-Options "SAMEORIGIN" always;
       add_header X-Content-Type-Options "nosniff" always;
       add_header Referrer-Policy "strict-origin-when-cross-origin" always;
       add_header Permissions-Policy "camera=(), microphone=(), geolocation=()" always;

       # Block direct access to sensitive configuration files
       location ~ /\.(env|git|ht) {
           deny all;
       }

       location ~ \.(sql|log|bak|example)$ {
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

   Repeat these `add_header` directives in the HTTPS server block and add `add_header Strict-Transport-Security "max-age=31536000" always;` there only. Nginx does not inherit server-level `add_header` directives in locations that define their own, so repeat the required headers in those locations. If TLS terminates upstream, configure HSTS at the HTTPS edge instead. See the shared-hosting notes above before enabling HSTS or CORS.

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
   - Import `_sql/database.sql`.

3. **Adjust `.env`:**
   ```ini
   DB_HOST=localhost
   DB_NAME=set
   DB_USER=root
   DB_PASS=
   BASE_URL=auto
   ```

4. **Finish setup:** The imported `database.sql` includes the current schema, so no migration command is needed for a fresh install. When upgrading an existing installation, back up the database and run `php migrate.php` from the game directory.

5. **Launch Game in Browser:**
   - Visit `http://localhost:7000` or `http://localhost/setst.site`.

PHP's built-in development server does not read `.htaccess`; use a configured web server to verify response headers.

Heroes choose a faction during onboarding or can change it free from their profile. Factions grant the displayed percentage bonuses to Power, Defense, and Health (including equipped items) before set and morale bonuses. Existing heroes remain unaligned, without faction bonuses, until they choose. Finish forest and tavern fights before changing; previously sent arena challenges retain their original stats.

---

## ⏱️ Automated Scheduled Tasks (`game_cron`)

To ensure recurring world events operate autonomously, configure the included scripts via cPanel or Linux `crontab -e`. Adjust schedules to match the desired reset cadence:

```bash
# Reset arena and forest battle limits
0 */6 * * * php /absolute/path/to/game_cron/arena.php >/dev/null 2>&1

# Reset daily tasks, keys, common chest, and forest resources
0 0 * * * php /absolute/path/to/game_cron/task.php >/dev/null 2>&1

# Reset rare keys/chest and conclude weekly tournament rewards
0 0 * * 1 php /absolute/path/to/game_cron/sunduk2.php >/dev/null 2>&1
```

*(On cPanel, replace `/absolute/path/to/` with the game's absolute path, e.g., `/home/username/public_html/game_cron/arena.php`.)*

---

## 🛡️ Security & Optimization

1. **Password Protection:** Passwords are hashed using **BCrypt** ($2y$) via `password_hash()`. Supported legacy single- and double-MD5 password hashes are upgraded to BCrypt after a successful login.
2. **Confidential File Shielding:** The [`.htaccess`](.htaccess) configuration denies public HTTP access to `.env` files, `.sql` dumps, and files ending in `.log`, `.bak`, or `.example`. Configure equivalent protections when using Nginx.
3. **Session Management:** Session cookies use `HttpOnly`, `SameSite=Lax`, and PHP's strict session mode to reduce exposure to client-side scripts, cross-site requests, and session fixation.
4. **Schema Migrations:** Schema changes are applied by running `php migrate.php` from the game directory using the command line. They do not run automatically during a web request; back up the database before upgrading.
   The news read-position migration preserves a clear old notification as read; an existing unread flag is treated as one unread announcement because the old system did not record a count.
   If `users.last_news_read_id` is missing, News temporarily uses the legacy `news` flag instead of failing and logs an upgrade reminder. This mode cannot track new announcements individually. Back up the database and run `php migrate.php` to enable persistent unread counts; no schema changes are made by opening News.
5. **Render-blocking Styles:** `head()` inlines `style/style.min.css`, generated from the readable `style/style.css`, so the first render does not wait for a separate stylesheet request. Keep CSS asset URLs root-relative so they work on every route. Google Fonts load asynchronously with `display=swap` and a no-JavaScript fallback. Inlining preserves the initial layout but includes the stylesheet in every HTML response instead of caching it separately.

---

## ✅ What I Have Done

- **Expanded gameplay systems:** Added arena combat and gameplay bonuses, improved combat inventory drops and redirects, and implemented a persistent five-landmark forest exploration map with monster selection and existing battle progression ([#9](https://github.com/BudComSol/Kingdom-of-Lyonesse/pull/9), [#16](https://github.com/BudComSol/Kingdom-of-Lyonesse/pull/16), [#38](https://github.com/BudComSol/Kingdom-of-Lyonesse/pull/38), [#42](https://github.com/BudComSol/Kingdom-of-Lyonesse/pull/42), [#127](https://github.com/BudComSol/Kingdom-of-Lyonesse/pull/127)).
- **Added the Ruby economy:** Integrated PayPal Ruby pack purchases, Ruby exchange naming and balances, VIP benefits, and configurable purchase amounts ([#41](https://github.com/BudComSol/Kingdom-of-Lyonesse/pull/41), [#75](https://github.com/BudComSol/Kingdom-of-Lyonesse/pull/75), [#90](https://github.com/BudComSol/Kingdom-of-Lyonesse/pull/90)).
- **Improved account and player experience:** Added clearer login and password recovery feedback, login form validation, persistent login cleanup, moderation and profile improvements, and accurate player activity and NPC uptime displays ([#14](https://github.com/BudComSol/Kingdom-of-Lyonesse/pull/14), [#30](https://github.com/BudComSol/Kingdom-of-Lyonesse/pull/30), [#61](https://github.com/BudComSol/Kingdom-of-Lyonesse/pull/61), [#89](https://github.com/BudComSol/Kingdom-of-Lyonesse/pull/89), [#91](https://github.com/BudComSol/Kingdom-of-Lyonesse/pull/91), [#92](https://github.com/BudComSol/Kingdom-of-Lyonesse/pull/92)).
- **Modernized navigation and onboarding:** Reworked the sidebar and footer navigation, Town Hall and Ruby links, tutorial and logged-in page titles, helpful-links navigation, and the complete hero onboarding flow ([#62](https://github.com/BudComSol/Kingdom-of-Lyonesse/pull/62), [#64](https://github.com/BudComSol/Kingdom-of-Lyonesse/pull/64), [#88](https://github.com/BudComSol/Kingdom-of-Lyonesse/pull/88), [#93](https://github.com/BudComSol/Kingdom-of-Lyonesse/pull/93), [#117](https://github.com/BudComSol/Kingdom-of-Lyonesse/pull/117), [#126](https://github.com/BudComSol/Kingdom-of-Lyonesse/pull/126)).
- **Redesigned the landing page:** Updated the landing layout, login and Ruby calls to action, social metadata, accessibility, dragon artwork, fire effects, oval banners, titles, and a configurable administrator toggle ([#68](https://github.com/BudComSol/Kingdom-of-Lyonesse/pull/68), [#70](https://github.com/BudComSol/Kingdom-of-Lyonesse/pull/70), [#81](https://github.com/BudComSol/Kingdom-of-Lyonesse/pull/81), [#100](https://github.com/BudComSol/Kingdom-of-Lyonesse/pull/100), [#114](https://github.com/BudComSol/Kingdom-of-Lyonesse/pull/114), [#123](https://github.com/BudComSol/Kingdom-of-Lyonesse/pull/123)).
- **Added privacy and consent features:** Added a privacy page, cookie consent before game entry, visible policy links, and responsive cookie/privacy copy ([#39](https://github.com/BudComSol/Kingdom-of-Lyonesse/pull/39), [#95](https://github.com/BudComSol/Kingdom-of-Lyonesse/pull/95), [#97](https://github.com/BudComSol/Kingdom-of-Lyonesse/pull/97), [#125](https://github.com/BudComSol/Kingdom-of-Lyonesse/pull/125)).
- **Strengthened security and deployment:** Added HTTP security headers, moved migrations out of request bootstrap, protected current database installation, standardized fresh-install IDs and timezone behavior, and documented secure environment and session settings ([#57](https://github.com/BudComSol/Kingdom-of-Lyonesse/pull/57), [#60](https://github.com/BudComSol/Kingdom-of-Lyonesse/pull/60), [#65](https://github.com/BudComSol/Kingdom-of-Lyonesse/pull/65), [#71](https://github.com/BudComSol/Kingdom-of-Lyonesse/pull/71), [#96](https://github.com/BudComSol/Kingdom-of-Lyonesse/pull/96), [#129](https://github.com/BudComSol/Kingdom-of-Lyonesse/pull/129)).
- **Improved performance and SEO:** Removed render-blocking stylesheet requests, minified and inlined CSS, optimized font and homepage image loading, added structured game metadata and social previews, and fixed mobile viewport behavior ([#24](https://github.com/BudComSol/Kingdom-of-Lyonesse/pull/24), [#28](https://github.com/BudComSol/Kingdom-of-Lyonesse/pull/28), [#53](https://github.com/BudComSol/Kingdom-of-Lyonesse/pull/53), [#54](https://github.com/BudComSol/Kingdom-of-Lyonesse/pull/54), [#55](https://github.com/BudComSol/Kingdom-of-Lyonesse/pull/55), [#101](https://github.com/BudComSol/Kingdom-of-Lyonesse/pull/101), [#103](https://github.com/BudComSol/Kingdom-of-Lyonesse/pull/103)).
- **Modernized documentation and project structure:** Moved the database dump under `_sql/`, refreshed the README directory tree, and corrected setup, PHP, environment, session, migration, server-protection, caching, and license guidance ([#79](https://github.com/BudComSol/Kingdom-of-Lyonesse/pull/79), [#128](https://github.com/BudComSol/Kingdom-of-Lyonesse/pull/128), [#129](https://github.com/BudComSol/Kingdom-of-Lyonesse/pull/129)).
- **Added unread news notifications:** Added a footer badge with each player's unread announcement count, persistent news read tracking, read-on-open behavior, migration support for existing accounts, and tests covering notification and database migration behavior ([#159](https://github.com/BudComSol/Kingdom-of-Lyonesse/pull/159)).

---

## ❓ Frequently Asked Questions & Administration

#### 1. How do I resolve a "Database connection error"?
Verify that the database credentials in `.env` (`DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`) match your MySQL database settings and that the database user has been granted privileges on that database.

#### 2. How do I access the Game Administration Panel?
- Register a hero account in the game.
- Open phpMyAdmin, locate your hero in the `users` table, and change the `adm` column value to `3`.
- Log in to access the **Admin Panel** (`/update.php`) to create item sets, configure PayPal and Ruby packs, spawn NPC bots, broadcast news announcements, and manage bans.

#### 3. Why are CSS changes not appearing in the browser?
Press `Ctrl + F5` (or `Cmd + Shift + R` on macOS) to reload the page and bypass browser caching. The main stylesheet is inlined into each HTML response; JavaScript assets use versioned URLs for cache invalidation.

---

## 📄 License

This project is open-source and distributed under the [MIT License](LICENSE).

---

<p align="center">
  <b>Kingdom of Lyonesse MMORPG Engine</b> — Optimized for performance, gameplay depth, and modern web standards.
</p>

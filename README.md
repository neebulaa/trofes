<p align="center">
  <img src="./public/assets/logo/logo-transparent.png" alt="Trofes logo" width="160" />
</p>

<h1 align="center">Trofes</h1>

<p align="center">
  <strong>Smart food choices</strong> — recipe recommendations, ingredient detection, and health guides.
</p>

<p align='center'>Trofes is an application that helps users choose food intelligently and personally through recipe recommendations tailored to their preferences, allergies, and nutritional needs. It is complemented with easy-to-understand health articles to support healthy and sustainable meal planning.</p>

<p align="center">
  <a href="#tech-stack--services">Tech Stack</a> •
  <a href="#key-features">Features</a> •
  <a href="#installation-local">Install</a> •
  <a href="#getting-api-keys-step-by-step">API Keys</a>
</p>

<p align="center">
  <a href="https://laravel.com/" target="_blank" rel="noopener noreferrer">
    <img alt="Laravel" src="https://img.shields.io/badge/Laravel-12-red" />
  </a>
  <a href="https://www.php.net/" target="_blank" rel="noopener noreferrer">
    <img alt="PHP" src="https://img.shields.io/badge/PHP-8.2%2B-777bb4" />
  </a>
  <a href="https://react.dev/" target="_blank" rel="noopener noreferrer">
    <img alt="React" src="https://img.shields.io/badge/React-frontend-61DAFB" />
  </a>
  <a href="https://inertiajs.com/" target="_blank" rel="noopener noreferrer">
    <img alt="Inertia.js" src="https://img.shields.io/badge/Inertia.js-adapter-9553E9" />
  </a>
  <a href="https://www.mysql.com/" target="_blank" rel="noopener noreferrer">
    <img alt="MySQL" src="https://img.shields.io/badge/MySQL-database-4479A1" />
  </a>
</p>

---

## Tech Stack & Services

### Core
- **Laravel 12** (Backend)
- **Inertia.js** (Server-driven SPA adapter)
- **React** (Frontend UI)
- **Vite** (Frontend bundler / dev server)
- **MySQL** (Database)

### Integrations / Services
- **Trofes AI Recommendation & Ingredient Detection API**  
  Backing services for personalized recommendations and ingredient image detection:
  1. **AI Recommendation**: https://github.com/neebulaa/trofes-model-recommendation  
  2. **AI Ingredient Detector**: https://github.com/neebulaa/trofes-model-ingredient-detection  

- **Google OAuth 2.0** — Login with Google  
- **Cloudflare Turnstile** — Bot protection / verification  
- **YouTube Data API v3** — Recipe video integration  
- **Umami Analytics** — Web analytics (optional)  

---

## Key Features

### 01. Authentication & User Management
- Login (Credentials + Google OAuth)
- Register & Logout
- Forgot Password
- Email Verification
- Onboarding Setup
- Profile Management

### 02. Smart Recipe System
- All Recipes
- Recommendations based on likes & preferences
- Simple Search, Filters, and Sorting
- Advanced Search + Ingredient Image Detection
- Recipe Detail + YouTube Video API Integration
- Cooking Timer
- Like Recipe

### 03. Nutrition & Health Tools
- All Guides (Search & Sorting)
- Nutrients Calculator
- Food recommendations based on nutrition
- Healthy Guides & Articles
- Guide Detail

### 04. Dashboard & Admin Management
- Web Analytics (Umami Services)
- Allergies Data Management
- Dietary Preferences Data Management
- Guides Data Management
- User/Admin Role Settings
- Contact Us Message Management
- Activity Logging

---

## Requirements

- PHP **8.2+** (requires >= 8.2 and < 9.0)
- Composer
- Node.js + npm
- MySQL (or MariaDB)

Optional but recommended:
- SMTP credentials (for email verification / forgot password), e.g. Gmail SMTP + App Password
- Google account (for OAuth setup)
- Cloudflare account (for Turnstile)
- Google Cloud project (for YouTube Data API)

---

## Installation (Local)

### 1) Clone the repository
```bash
git clone https://github.com/neebulaa/trofes.git
cd trofes
```

### 2) Install backend dependencies
```bash
composer install
```

### 3) Install frontend dependencies
```bash
npm install
```

### 4) Create your environment file
```bash
cp .env.example .env
```

### 5) Generate app key
```bash
php artisan key:generate
```

### 6) Create the storage symlink
```bash
php artisan storage:link
```

### 7) Configure database (MySQL)

**Fix / correction:** if you're using MySQL, ensure `.env` uses `DB_CONNECTION=mysql` (not sqlite).

Example:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_db_name
DB_USERNAME=root
DB_PASSWORD=
```

Notes:
- Create `your_db_name` in MySQL first.
- If you use **MAMP**, your local MySQL password is commonly `root`.

### 8) Run migrations + seeders
```bash
php artisan migrate --seed
```

### 9) Start the frontend dev server (Vite)
```bash
npm run dev
```

### 10) Start Laravel (in a second terminal)
```bash
php artisan serve
```

### 11) Open the app
- http://127.0.0.1:8000

---

## Environment Variables (API Keys / Integrations)

Add or fill in these keys in your `.env`:

```env
# Cloudflare Turnstile verification
TURNSTILE_SITE_KEY=
TURNSTILE_SECRET_KEY=

# Google OAuth authentication
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI=http://127.0.0.1:8000/auth/google/callback

# YouTube Data API
YOUTUBE_API_KEY=

# SMTP (Gmail example) - used for email verification / forgot password
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="no-reply@trofes.site"
MAIL_FROM_NAME="${APP_NAME}"

# Umami Analytics
VITE_UMAMI_SHARE_URL=

```

---

## Getting API Keys (Step-by-step)

### A) Cloudflare Turnstile (TURNSTILE_SITE_KEY / TURNSTILE_SECRET_KEY)

1. Go to **Cloudflare Dashboard**: https://dash.cloudflare.com/
2. Open **Turnstile** (Application Security → Turnstile).
3. Under **Turnstile widgets**, click **Add widget**.
4. Fill in a **Widget name** (e.g. `Trofes Local`).
5. Configure **Hostname Management**:
   - Click **Add Hostnames**
   - Add the hostname(s) you will use, for example:
     - `127.0.0.1` (if you access your app via IP)
     - `localhost` (if you access your app via localhost)
     - your dev/staging domain (recommended for team/shared testing)
6. Choose a **Widget Mode** (Managed is usually recommended).
7. (Optional) Configure **Pre-clearance**:
   - **No** (default / recommended for most cases), unless your site is proxied through Cloudflare and you specifically want verified users to bypass some future security challenges.
8. Create/save the widget.
9. Copy the generated keys:
   - **Site key** → set as `TURNSTILE_SITE_KEY`
   - **Secret key** → set as `TURNSTILE_SECRET_KEY`
10. Paste them into your `.env` and restart your app.

```env
TURNSTILE_SITE_KEY=your_site_key
TURNSTILE_SECRET_KEY=your_secret_key
```

---

### B) Google OAuth (GOOGLE_CLIENT_ID / GOOGLE_CLIENT_SECRET / GOOGLE_REDIRECT_URI)

1. Go to **Google Cloud Console**: https://console.cloud.google.com/
2. Create a **new project** (or select an existing one).
3. Navigate to **APIs & Services → OAuth consent screen** to get into Google Auth Platform
4. Click **Get started** (if you are new to the app)
5. On **Project configuration**, complete the setup sections:
   - **1) App Information**: set your **App name** and **User support email**, then click **Next**
   - **2) Audience**: choose **External** (or **Internal** if you’re using a Google Workspace org), then click **Next**
   - **3) Contact Information**: fill in required contact email(s), then click **Next**
   - **4) Finish**: click **Create**
6. Create an OAuth client:
   - In the left sidebar, go to **Clients**
   - Click **Create client**
   - Choose application type **Web application**
   - For the name just make it yourself.
7. Add the redirect URI:
   - Under **Authorized redirect URIs**, add:
     - `http://127.0.0.1:8000/auth/google/callback`
     - `http://localhost:8000/auth/google/callback`
8. Create/save the client, then copy the credentials:
   - **Client ID** → `GOOGLE_CLIENT_ID`
   - **Client secret** → `GOOGLE_CLIENT_SECRET`
9. Set your `.env` like this:
```env
GOOGLE_CLIENT_ID=your_client_id
GOOGLE_CLIENT_SECRET=your_client_secret
GOOGLE_REDIRECT_URI=http://127.0.0.1:8000/auth/google/callback
```
10. Restart your app and test “Login with Google”.

Notes:
- If **External** is used, your app typically starts in **Testing** mode and you may need to add **test users** (Google accounts) before it will work for others.
- The UI changes over time, but the key parts are: configure app (Branding/Audience) → create **Client** → add **Redirect URI**.

---

### C) YouTube Data API (YOUTUBE_API_KEY)

1. Go to **Google Cloud Console**: https://console.cloud.google.com/
2. Select the same project as OAuth (or create a new one).
3. Enable the API:
   - Go to **APIs & Services → Library**
   - Search for **YouTube Data API v3**
   - Click **Enable**
4. Create an API key:
   - Go to **APIs & Services → Credentials**
   - Click **Create Credentials → API key**
   - Enter the API Key name yourself
5. (Recommended) Restrict the key:
   - In the API key settings, set **API restrictions** to *YouTube Data API v3*
   - Optionally restrict by HTTP referrers (for browser usage) or IPs (for server usage), depending on how your app calls the API.
6. Copy the generated key and set it on the `.env`:
```env
YOUTUBE_API_KEY=your_api_key
```

---

## SMTP Setup (Gmail) — Step-by-step

This app sends emails for features like **email verification** and **forgot password**.  
For local development you can use Gmail SMTP:

1. Use (or create) a Google account you want to send mail from.
2. Enable **2-Step Verification** on the Google account.
3. Create a **Gmail App Password**:
   - Google Account → Security → **App passwords** (or you can go to this link https://myaccount.google.com/apppasswords)
   - Create an app password for “Mail” (or “Other”)
4. Put the credentials into your `.env`:
   - `MAIL_USERNAME` = your Gmail address (e.g. `yourname@gmail.com`)
   - `MAIL_PASSWORD` = the generated **App Password** (not your normal password)
5. Ensure these values are set:
   - `MAIL_HOST=smtp.gmail.com`
   - `MAIL_PORT=587`
   - `MAIL_ENCRYPTION=tls`
6. Restart `php artisan serve` and test a flow that sends email (e.g. “Forgot Password”).

Troubleshooting:
- If Gmail blocks sign-in, double-check you are using an **App Password** and that 2FA is enabled.
- If emails are not arriving, check spam/junk and confirm `MAIL_FROM_ADDRESS`

---

## Umami Analytics (Dashboard Embed)

Trofes shows a simple analytics panel on the admin dashboard using an **embedded Umami share link** (via an `<iframe>`).

### How to set up Umami (Cloud or Self-hosted)

1. Create an Umami account / instance:
   - Umami Cloud: https://cloud.umami.is/
   - Or self-host Umami: https://umami.is/
2. Create a **Website** in Umami for your Trofes deployment. Enter the name and the domain, you can use 'localhost' for local development
3. Open the Umami website dashboard and create a **Share link**:
   - Find the **Share** option in the Umami UI for your website/dashboard (Typically in the top right Edit button)
   - Enable/add sharing (enter name and create) and copy the generated **share URL**
4. Put the share link into your `.env`:
```env
VITE_UMAMI_SHARE_URL=your_share_url
```
5. Restart `php artisan serve` and check if the analytics shows on the Home Dashboard

### Notes / Security

- The Umami **share link** is intended for embedding and typically does not require logging in.
- Treat the share URL as **semi-private**: Anyone with the link may be able to view analytics, depending on your Umami share settings.

---

## License

This project is licensed under the [MIT License](./LICENSE).

---

## Contact

If you have questions, feedback, or want to contribute, feel free to open an issue or reach out via the Contact Us feature (if enabled).
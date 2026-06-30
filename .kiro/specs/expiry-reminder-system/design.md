# Technical Design: Expiry Reminder System

## Overview

This document describes the technical architecture for the Expiry Reminder System — a Laravel 13 web application that tracks product expiry dates for minimarket employees and delivers Telegram notifications before products expire. The system uses server-rendered Blade views with TailwindCSS v4, Google OAuth (via Laravel Socialite) for user login, traditional email/password for admin login, queue workers for async notifications, and the Telegram Bot API for delivery.

The UX is designed for speed — employees can scan a barcode to instantly find a product and set its expiry date in under 10 seconds.

## Architecture

```
┌─────────────────────────────────────────────────────────────┐
│              Browser (Blade + TailwindCSS + JS Scanner)      │
└──────────────────────────────┬──────────────────────────────┘
                               │ HTTP
┌──────────────────────────────▼──────────────────────────────┐
│                     Laravel Application                       │
│  ┌────────────┐  ┌──────────────┐  ┌────────────────────┐  │
│  │ Controllers │  │  Middleware   │  │  Form Requests     │  │
│  └──────┬─────┘  └──────────────┘  └────────────────────┘  │
│         │                                                    │
│  ┌──────▼─────────────────────────────────────────────────┐ │
│  │                   Eloquent Models                        │ │
│  │  User · Category · Product · TrackedItem                 │ │
│  └──────┬─────────────────────────────────────────────────┘ │
│         │                                                    │
│  ┌──────▼───────┐  ┌────────────────┐  ┌────────────────┐  │
│  │   Database    │  │  Queue Worker   │  │   Scheduler    │  │
│  │   (SQLite)   │  │  (database)     │  │   (Daily)      │  │
│  └──────────────┘  └───────┬────────┘  └───────┬────────┘  │
│                             │                    │            │
│                    ┌────────▼────────────────────▼────────┐  │
│                    │      TelegramNotifierService          │  │
│                    └────────────────┬─────────────────────┘  │
└─────────────────────────────────────┼────────────────────────┘
                                      │ HTTPS
              ┌───────────────────────┼───────────────────────┐
              │                       │                       │
┌─────────────▼──────┐  ┌────────────▼────────────┐         │
│  Google OAuth API  │  │  Telegram Bot API        │         │
│  (User login)      │  │  (sendMessage)           │         │
└────────────────────┘  └─────────────────────────┘         │
```

## Authentication Strategy

| Role | Method | Package | Notes |
|------|--------|---------|-------|
| User (karyawan) | Google OAuth | Laravel Socialite | One-click login, no password needed. Account auto-created on first login. |
| Admin | Email + Password | Laravel built-in auth | Traditional login form at `/admin/login`. Admins are seeded or created manually. |

### User OAuth Flow
1. User clicks "Login with Google" on `/login`
2. Redirected to Google consent screen
3. Google redirects back to `/auth/google/callback`
4. System checks if `google_id` exists in `users` table
5. **If exists**: login the user
6. **If new**: create user with name/email from Google, role = 'user', login

### Admin Login Flow
1. Admin goes to `/admin/login`
2. Enters email + password
3. Standard Laravel authentication
4. Redirected to admin dashboard

## Data Models

### ERD

```
┌──────────────────┐     ┌───────────────┐     ┌──────────────────┐
│      users       │     │  categories   │     │    products       │
├──────────────────┤     ├───────────────┤     ├──────────────────┤
│ id               │     │ id            │     │ id               │
│ name             │     │ name          │     │ category_id (FK) │
│ email            │     │ created_at    │     │ name             │
│ password (null)  │     │ updated_at    │     │ barcode          │
│ google_id (null) │     └───────┬───────┘     │ image            │
│ avatar (null)    │             │             │ shelf_life_days  │
│ role (enum)      │             └────────────►│ created_at       │
│ is_active        │                           │ updated_at       │
│ telegram_user_id │                           └────────┬─────────┘
│ last_login_at    │                                    │
│ created_at       │                                    │
│ updated_at       │                                    │
└──────┬───────────┘                                    │
       │                                                │
       │          ┌────────────────────────────┐        │
       │          │      tracked_items         │        │
       │          ├────────────────────────────┤        │
       └─────────►│ id                         │◄───────┘
                  │ user_id (FK)               │
                  │ product_id (FK)            │
                  │ expiry_date                │
                  │ remind_at (date)           │
                  │ reminder_status (enum)     │
                  │ reminder_sent_at (ts)      │
                  │ created_at                 │
                  │ updated_at                 │
                  └────────────────────────────┘

┌──────────────────┐
│ product_requests │
├──────────────────┤
│ id               │
│ user_id (FK)     │
│ name             │
│ barcode          │
│ description      │
│ status (enum)    │
│ rejection_reason │
│ created_at       │
│ updated_at       │
└──────────────────┘
```

### Database Tables

#### `users` (modify existing migration `0001_01_01_000000`)

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint | PK, auto-increment | |
| name | string(255) | not null | |
| email | string(255) | unique, not null | |
| password | string | **nullable** | Only set for admin accounts |
| google_id | string(100) | nullable, unique | Google OAuth ID for user accounts |
| avatar | string(255) | nullable | Google profile photo URL |
| role | enum('admin','user') | default 'user' | |
| is_active | boolean | default true | Account deactivation flag |
| telegram_user_id | string(20) | nullable | Telegram numeric user ID |
| last_login_at | timestamp | nullable | Track last login for admin stats |
| email_verified_at | timestamp | nullable | |
| remember_token | string(100) | nullable | |
| timestamps | | | created_at, updated_at |

> **Auth split**: User login pakai Google OAuth (tanpa password). Admin login pakai email/password biasa. Field `password` nullable karena OAuth users tidak punya password.

#### `categories` (new migration)

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint | PK, auto-increment | |
| name | string(100) | unique, not null | Category name (e.g., Minuman, Makanan Ringan) |
| timestamps | | | created_at, updated_at |

#### `products` (modify existing migration `2026_06_23_072540`)

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint | PK, auto-increment | |
| category_id | bigint | FK → categories.id, set null on delete | Product category |
| name | string(255) | not null | Product name |
| barcode | string(50) | unique, not null | Product barcode (for scanning) |
| image | string(255) | nullable | Path to product photo (storage/app/public/products) |
| shelf_life_days | integer | nullable, min: 1 | Default shelf life in days |
| timestamps | | | created_at, updated_at |

#### `tracked_items` (rename existing `products_expired` migration `2026_06_23_072623`)

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint | PK, auto-increment | |
| user_id | bigint | FK → users.id, cascade delete | |
| product_id | bigint | FK → products.id, cascade delete | |
| expiry_date | date | not null | When the product expires |
| remind_at | date | nullable | When the reminder notification should be sent |
| reminder_status | enum('pending','sent','failed') | default 'pending' | Status of the reminder |
| reminder_sent_at | timestamp | nullable | When notification was delivered |
| timestamps | | | created_at, updated_at |
| | | unique(user_id, product_id, expiry_date) | Prevent duplicates |

> **Migration note**: The existing `products_expired` migration will be rewritten to create `tracked_items` table instead.

#### `product_requests` (new migration)

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint | PK, auto-increment | |
| user_id | bigint | FK → users.id, cascade delete | Requesting user |
| name | string(255) | not null | Requested product name |
| barcode | string(13) | not null | Requested barcode |
| description | text(500) | nullable | Optional description |
| status | enum('pending','approved','rejected') | default 'pending' | |
| rejection_reason | text(500) | nullable | Reason if rejected |
| timestamps | | | created_at, updated_at |

## Enums

```php
// app/Enums/UserRole.php
enum UserRole: string {
    case Admin = 'admin';
    case User = 'user';
}

// app/Enums/ReminderStatus.php
enum ReminderStatus: string {
    case Pending = 'pending';
    case Sent = 'sent';
    case Failed = 'failed';
}

// app/Enums/ProductRequestStatus.php
enum ProductRequestStatus: string {
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
}
```

## Application Structure

```
app/
├── Enums/
│   ├── UserRole.php
│   ├── ReminderStatus.php
│   └── ProductRequestStatus.php
├── Http/
│   ├── Controllers/
│   │   ├── Auth/
│   │   │   ├── GoogleAuthController.php      # OAuth for users
│   │   │   └── AdminLoginController.php      # Email/password for admin
│   │   ├── Admin/
│   │   │   ├── ProductController.php
│   │   │   ├── CategoryController.php
│   │   │   ├── ProductRequestController.php
│   │   │   └── UserController.php
│   │   ├── DashboardController.php
│   │   ├── TrackedItemController.php
│   │   ├── BarcodeScanController.php
│   │   ├── ProductRequestController.php
│   │   └── ProfileController.php
│   ├── Middleware/
│   │   ├── EnsureUserIsActive.php
│   │   └── EnsureUserIsAdmin.php
│   └── Requests/
│       ├── Auth/
│       │   └── AdminLoginRequest.php
│       ├── StoreProductRequest.php
│       ├── UpdateProductRequest.php
│       ├── StoreCategoryRequest.php
│       ├── StoreTrackedItemRequest.php
│       ├── UpdateTrackedItemRequest.php
│       ├── StoreProductAdditionRequest.php
│       └── UpdateProfileRequest.php
├── Jobs/
│   └── SendTelegramNotificationJob.php
├── Models/
│   ├── User.php
│   ├── Category.php
│   ├── Product.php
│   ├── TrackedItem.php
│   └── ProductRequest.php
├── Services/
│   └── TelegramNotifierService.php
└── Console/
    └── Commands/
        └── ProcessExpiryRemindersCommand.php
```

## Routes

### Web Routes (`routes/web.php`)

```
# User OAuth routes (Google)
GET    /login                         redirect to Google or show login page    login
GET    /auth/google                   GoogleAuthController@redirect            auth.google
GET    /auth/google/callback          GoogleAuthController@callback            auth.google.callback
POST   /logout                        GoogleAuthController@logout              logout

# Admin auth routes
GET    /admin/login                   AdminLoginController@showLoginForm       admin.login
POST   /admin/login                   AdminLoginController@login              admin.login.submit
POST   /admin/logout                  AdminLoginController@logout             admin.logout

# Authenticated routes (middleware: auth, active)
GET    /dashboard                     DashboardController@index               dashboard
GET    /dashboard/{status}            DashboardController@filtered            dashboard.filtered

# Barcode Scanner
GET    /scan                          BarcodeScanController@index             scan.index
POST   /scan/lookup                   BarcodeScanController@lookup            scan.lookup

# Tracked Items (includes reminder setting in same form)
GET    /tracked-items                 TrackedItemController@index             tracked-items.index
GET    /tracked-items/create          TrackedItemController@create            tracked-items.create
GET    /tracked-items/create/{product}  TrackedItemController@createForProduct  tracked-items.create-for-product
POST   /tracked-items                 TrackedItemController@store             tracked-items.store
GET    /tracked-items/{trackedItem}/edit  TrackedItemController@edit          tracked-items.edit
PUT    /tracked-items/{trackedItem}   TrackedItemController@update            tracked-items.update
DELETE /tracked-items/{trackedItem}   TrackedItemController@destroy           tracked-items.destroy

# Product Requests (User)
GET    /product-requests              ProductRequestController@index          product-requests.index
GET    /product-requests/create       ProductRequestController@create         product-requests.create
POST   /product-requests              ProductRequestController@store          product-requests.store

# Profile (Telegram ID setup)
GET    /profile                       ProfileController@edit                  profile.edit
PUT    /profile                       ProfileController@update                profile.update

# Admin routes (middleware: auth, active, admin)
GET    /admin/users                   Admin\UserController@index              admin.users.index
PUT    /admin/users/{user}/toggle     Admin\UserController@toggleActive       admin.users.toggle
PUT    /admin/users/{user}/role       Admin\UserController@updateRole         admin.users.role

GET    /admin/categories              Admin\CategoryController@index          admin.categories.index
POST   /admin/categories              Admin\CategoryController@store          admin.categories.store
PUT    /admin/categories/{category}   Admin\CategoryController@update         admin.categories.update
DELETE /admin/categories/{category}   Admin\CategoryController@destroy        admin.categories.destroy

GET    /admin/products                Admin\ProductController@index           admin.products.index
GET    /admin/products/create         Admin\ProductController@create          admin.products.create
POST   /admin/products                Admin\ProductController@store           admin.products.store
GET    /admin/products/{product}/edit Admin\ProductController@edit            admin.products.edit
PUT    /admin/products/{product}      Admin\ProductController@update          admin.products.update
DELETE /admin/products/{product}      Admin\ProductController@destroy         admin.products.destroy

GET    /admin/product-requests        Admin\ProductRequestController@index    admin.product-requests.index
PUT    /admin/product-requests/{productRequest}/approve  Admin\ProductRequestController@approve  admin.product-requests.approve
PUT    /admin/product-requests/{productRequest}/reject   Admin\ProductRequestController@reject   admin.product-requests.reject
```

## Key Components

### 1. Authentication Controllers

**GoogleAuthController** (for users):
```php
class GoogleAuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback(): RedirectResponse
    {
        $googleUser = Socialite::driver('google')->user();

        $user = User::updateOrCreate(
            ['google_id' => $googleUser->getId()],
            [
                'name' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'avatar' => $googleUser->getAvatar(),
                'last_login_at' => now(),
            ]
        );

        Auth::login($user, remember: true);
        return redirect()->route('dashboard');
    }
}
```

**AdminLoginController** (for admins):
```php
class AdminLoginController extends Controller
{
    public function showLoginForm(): View
    public function login(AdminLoginRequest $request): RedirectResponse
    public function logout(): RedirectResponse
}
```

### 2. Middleware

**EnsureUserIsAdmin**
- Checks `$request->user()->role === UserRole::Admin`
- Returns 403 and redirects to dashboard if not admin

**EnsureUserIsActive**
- Checks `$request->user()->is_active === true`
- Logs out and redirects to login with message if inactive

### 3. Barcode Scanner (UX Priority Feature)

**Flow:**
1. User opens `/scan` — camera activates using html5-qrcode library
2. Barcode detected → JS sends POST to `/scan/lookup` with barcode value
3. Controller queries `Product::where('barcode', $barcode)->first()`
4. **If found**: redirect to `/tracked-items/create/{product}` — form pre-filled with product info, user only needs to set expiry date + reminder
5. **If not found**: show option to submit a Product Addition Request with barcode pre-filled

**BarcodeScanController:**
```php
class BarcodeScanController extends Controller
{
    public function index(): View  // Shows camera scanner UI
    public function lookup(Request $request): JsonResponse  // Returns product or 404
}
```

The scanner page is the primary entry point for daily use. A large FAB button on the dashboard links directly to it.

### 4. Tracked Item + Reminder (Single Form)

When creating/editing a tracked item, the form includes:
- Product selection (or pre-filled from scan)
- Expiry date input
- Reminder preset selector: "H-7", "H-14", "H-30", "B-1", "B-2", "B-3", or custom date
- The selected preset calculates `remind_at` automatically from `expiry_date`

```php
// In TrackedItem model or StoreTrackedItemRequest
public function calculateRemindAt(string $preset, Carbon $expiryDate): ?Carbon
{
    return match(true) {
        str_starts_with($preset, 'H-') => $expiryDate->subDays((int) substr($preset, 2)),
        str_starts_with($preset, 'B-') => $expiryDate->subMonths((int) substr($preset, 2)),
        $preset === 'custom' => null, // user provides remind_at directly
        default => null, // no reminder
    };
}
```

### 5. TelegramNotifierService

```php
class TelegramNotifierService
{
    public function sendMessage(string $telegramUserId, string $message): bool
    public function isUnrecoverableError(int $statusCode, string $description): bool
}
```

- Uses Laravel HTTP client: `POST https://api.telegram.org/bot{token}/sendMessage`
- `chat_id` parameter = user's `telegram_user_id` from DB
- Bot token stored in `config/services.php` under `telegram.bot_token`
- **No webhook needed** — system only sends messages, doesn't receive them

### 6. Telegram Integration (Simplified)

**How it works:**
1. User pergi ke halaman Profile (`/profile`)
2. User input Telegram User ID mereka (bisa dapat dari @userinfobot di Telegram)
3. Sistem simpan di `users.telegram_user_id`
4. Untuk kirim notifikasi, sistem pakai Bot API `sendMessage` dengan `chat_id` = telegram_user_id
5. **Prerequisite**: User harus `/start` bot terlebih dahulu agar bot bisa mengirim pesan

### 7. SendTelegramNotificationJob

- Dispatched by the scheduler for each tracked item with a due reminder
- Retries up to 3 times with 30-second backoff
- On permanent failure (blocked/invalid chat), clears user's `telegram_user_id`
- On success, updates `reminder_status` to "sent" and sets `reminder_sent_at`

### 8. ProcessExpiryRemindersCommand

- Registered in Laravel scheduler to run `daily()` at 07:00
- Queries `tracked_items` where:
  - `reminder_status = 'pending'`
  - `remind_at <= today`
  - `remind_at IS NOT NULL`
  - User has `telegram_user_id IS NOT NULL` and `is_active = true`
- Dispatches `SendTelegramNotificationJob` for each matching tracked item
- Processes in chunks of 30 to respect Telegram API rate limits

### 9. Product Image Upload

- Stored in `storage/app/public/products/` directory
- Accessed via `Storage::url()` after `php artisan storage:link`
- Max file size: 2MB, accepted types: jpg, png, webp
- Thumbnail generation not needed initially — display at fixed size via CSS

## Views Structure

```
resources/views/
├── layouts/
│   ├── app.blade.php              # Authenticated layout (bottom nav mobile, sidebar desktop)
│   └── guest.blade.php            # Guest layout (login pages)
├── auth/
│   ├── login.blade.php            # "Login with Google" button for users
│   └── admin-login.blade.php     # Email/password form for admins
├── dashboard/
│   └── index.blade.php            # Status summary cards + recent items
├── scan/
│   └── index.blade.php            # Camera barcode scanner (full-screen mobile)
├── tracked-items/
│   ├── index.blade.php            # List with status badges
│   ├── create.blade.php           # Product + expiry date + reminder preset (single page)
│   └── edit.blade.php             # Edit expiry + change reminder
├── product-requests/
│   ├── index.blade.php            # User's own requests with status
│   └── create.blade.php
├── profile/
│   └── edit.blade.php             # Name (readonly), email (readonly), Telegram User ID
├── admin/
│   ├── users/
│   │   └── index.blade.php        # User list with stats
│   ├── categories/
│   │   └── index.blade.php        # Inline CRUD (no separate create/edit pages)
│   ├── products/
│   │   ├── index.blade.php
│   │   ├── create.blade.php       # Includes image upload + category select
│   │   └── edit.blade.php
│   └── product-requests/
│       └── index.blade.php        # Pending requests with approve/reject actions
└── components/
    ├── status-badge.blade.php
    ├── summary-card.blade.php
    ├── nav-link.blade.php
    └── product-card.blade.php     # Product thumbnail + info compact display
```

## User Flow (Quick Track via Scan)

```
┌─────────────────┐    ┌──────────────┐    ┌─────────────────────┐    ┌──────────────────────┐
│ Login w/ Google │───►│  Dashboard   │───►│  Scan Page           │───►│ Set Expiry Date +    │
│ (one click)     │    │  (tap FAB)   │    │  (camera on)         │    │ Pick Reminder Preset │
└─────────────────┘    └──────────────┘    │  Product Found? Yes  │    │ Submit → Done!       │
                                           │  No → request product│    └──────────────────────┘
                                           └─────────────────────┘
```

This flow takes ~10 seconds for a known product: scan → set date → pick reminder → done.

## Configuration

### `config/services.php`

```php
'google' => [
    'client_id' => env('GOOGLE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    'redirect' => env('GOOGLE_REDIRECT_URI', '/auth/google/callback'),
],

'telegram' => [
    'bot_token' => env('TELEGRAM_BOT_TOKEN'),
],
```

### `.env` additions

```
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback

TELEGRAM_BOT_TOKEN=
```

### Dependencies to add

```bash
composer require laravel/socialite
```

## Migration Plan

Existing migrations and how they map to the new schema:

| Existing File | Action | New Purpose |
|---|---|---|
| `0001_01_01_000000_create_users_table.php` | **Modify** | Add `google_id`, `avatar`, `role`, `is_active`, `telegram_user_id`, `last_login_at`. Make `password` nullable. |
| `0001_01_01_000001_create_cache_table.php` | Keep as-is | Cache tables |
| `0001_01_01_000002_create_jobs_table.php` | Keep as-is | Queue jobs tables |
| `2026_06_23_072540_create_products_table.php` | **Rewrite** | Add `category_id`, `name`, `barcode`, `image`, `shelf_life_days` |
| `2026_06_23_072623_create_products_expired_table.php` | **Rewrite** | Rename to `tracked_items` with `user_id`, `product_id`, `expiry_date`, `remind_at`, `reminder_status`, `reminder_sent_at` |
| (new) | **Create** | `categories` table |
| (new) | **Create** | `product_requests` table |

## Security Considerations

1. **OAuth security**: Google OAuth handled by Socialite (proven, secure)
2. **CSRF protection**: All POST/PUT/DELETE routes use CSRF tokens (standard Laravel)
3. **Rate limiting**: Admin login route uses Laravel's built-in throttle middleware
4. **Input validation**: All user input validated through Form Request classes
5. **Authorization**: Middleware stack ensures role-based access; users can only access their own tracked items
6. **Password hashing**: Admin passwords hashed via Laravel's `hashed` cast (bcrypt)
7. **Session invalidation**: Deactivated users have sessions cleared immediately
8. **File upload security**: Image uploads validated for mime type and max size
9. **Telegram User ID validation**: Validated as numeric string

## Queue & Scheduler Configuration

- **Queue driver**: `database` (using existing `jobs` table migration)
- **Scheduler**: `ProcessExpiryRemindersCommand` runs `daily()` at 07:00 local time
- **Job retry**: 3 attempts, 30-second delay between retries
- **Failed jobs**: Logged to `failed_jobs` table (Laravel default)

## Design System Integration

The frontend follows the design tokens defined in `DESIGN.md`:
- Font: Comfortaa (loaded via Google Fonts)
- Status colors: `status-safe` (#2E7D32), `status-warning` (#EF6C00), `status-danger` (#C62828)
- Primary color: #005c86 / container: #0e76a8
- Cards: white background with subtle border (#E0E4E6) and soft shadow
- Rounded corners: 0.5rem default, pill for status badges
- Mobile-first responsive layout with 8px baseline grid
- Bottom navigation on mobile, sidebar on desktop
- FAB button for quick scan access

## Frontend Dependencies

- **html5-qrcode** (npm): Client-side barcode scanner using device camera
- No other JS framework — vanilla JS + Blade is sufficient for this app's complexity

## Traceability Matrix

| Requirement | Components |
|-------------|-----------|
| Req 1: Authentication | GoogleAuthController (user OAuth), AdminLoginController (admin email/pw), Socialite |
| Req 2: RBAC | UserRole enum, EnsureUserIsAdmin middleware, EnsureUserIsActive middleware |
| Req 3: Product Management | Admin\ProductController, Product model, Category model, StoreProductRequest |
| Req 4: Expiry Tracking | TrackedItemController, BarcodeScanController, TrackedItem model |
| Req 5: Product Requests | ProductRequestController, Admin\ProductRequestController, ProductRequest model |
| Req 6: Reminder Config | TrackedItemController (remind_at field in tracked_items form) |
| Req 7: Telegram Delivery | SendTelegramNotificationJob, TelegramNotifierService |
| Req 8: Telegram Linking | ProfileController (input Telegram User ID) |
| Req 9: Scheduled Check | ProcessExpiryRemindersCommand, Laravel Scheduler |
| Req 10: User Monitoring | Admin\UserController |
| Req 11: Barcode Scan | BarcodeScanController, html5-qrcode, scan view |
| Req 12: Dashboard | DashboardController, summary-card component, status-badge component |
| Categories | Admin\CategoryController, Category model |

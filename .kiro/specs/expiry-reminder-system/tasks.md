# Implementation Tasks

## Task 1: Database Schema & Migrations

- [x] Modify `0001_01_01_000000_create_users_table.php`: make `password` nullable, add columns `google_id` (string, nullable, unique), `avatar` (string, nullable), `role` (enum admin/user, default user), `is_active` (boolean, default true), `telegram_user_id` (string 20, nullable), `last_login_at` (timestamp, nullable)
- [x] Create new migration `create_categories_table.php` with `id`, `name` (string 100, unique), `timestamps`
- [x] Rewrite `2026_06_23_072540_create_products_table.php`: add `category_id` (FK → categories, nullable, set null on delete), `name` (string 255), `barcode` (string 50, unique), `image` (string 255, nullable), `shelf_life_days` (integer, nullable), `timestamps`
- [x] Rewrite `2026_06_23_072623_create_products_expired_table.php`: rename table to `tracked_items` with `user_id` (FK → users, cascade), `product_id` (FK → products, cascade), `expiry_date` (date), `remind_at` (date, nullable), `reminder_status` (enum pending/sent/failed, default pending), `reminder_sent_at` (timestamp, nullable), `timestamps`, unique constraint on (user_id, product_id, expiry_date)
- [x] Create new migration `create_product_requests_table.php` with `user_id` (FK → users, cascade), `name` (string 255), `barcode` (string 13), `description` (text, nullable), `status` (enum pending/approved/rejected, default pending), `rejection_reason` (text, nullable), `timestamps`
- [x] Run `php artisan migrate:fresh` to verify all migrations pass

## Task 2: Enums & Models

- [x] Create `app/Enums/UserRole.php` enum with cases Admin, User
- [x] Create `app/Enums/ReminderStatus.php` enum with cases Pending, Sent, Failed
- [x] Create `app/Enums/ProductRequestStatus.php` enum with cases Pending, Approved, Rejected
- [x] Update `app/Models/User.php`: add fillable fields (google_id, avatar, role, is_active, telegram_user_id, last_login_at), add casts for role (UserRole), is_active (boolean), last_login_at (datetime). Add relationships: `trackedItems()`, `productRequests()`
- [x] Create `app/Models/Category.php` with fillable `name`, relationship `products()`
- [x] Create `app/Models/Product.php` with fillable fields, relationship `category()`, `trackedItems()`
- [x] Create `app/Models/TrackedItem.php` with fillable fields, casts for reminder_status (ReminderStatus), expiry_date (date), remind_at (date), reminder_sent_at (datetime). Relationships: `user()`, `product()`. Add `calculateRemindAt()` helper method
- [x] Create `app/Models/ProductRequest.php` with fillable fields, cast status (ProductRequestStatus). Relationships: `user()`
- [x] Create factories for all models: UserFactory (update), CategoryFactory, ProductFactory, TrackedItemFactory, ProductRequestFactory
- [x] Create DatabaseSeeder with admin user (email/password), sample categories, and sample products

## Task 3: Authentication - Google OAuth (Users)

- [x] Install Laravel Socialite: `composer require laravel/socialite`
- [x] Add Google OAuth config to `config/services.php` (client_id, client_secret, redirect from env)
- [x] Add env variables to `.env.example`: GOOGLE_CLIENT_ID, GOOGLE_CLIENT_SECRET, GOOGLE_REDIRECT_URI
- [x] Create `app/Http/Controllers/Auth/GoogleAuthController.php` with `redirect()`, `callback()`, `logout()` methods
- [x] In `callback()`: use `updateOrCreate` on `google_id`, set `last_login_at`, login user with remember
- [x] Register routes: GET `/auth/google` (redirect), GET `/auth/google/callback` (callback), POST `/logout`
- [x] Create `resources/views/auth/login.blade.php` with "Login with Google" button using guest layout
- [x] Handle OAuth errors gracefully (redirect to login with flash error)

## Task 4: Authentication - Admin Email/Password

- [x] Create `app/Http/Controllers/Auth/AdminLoginController.php` with `showLoginForm()`, `login()`, `logout()` methods
- [x] Create `app/Http/Requests/Auth/AdminLoginRequest.php` with email/password validation
- [x] In `login()`: authenticate, verify role is Admin, update `last_login_at`, redirect to admin dashboard
- [x] Register routes: GET `/admin/login`, POST `/admin/login`, POST `/admin/logout`
- [x] Create `resources/views/auth/admin-login.blade.php` with email/password form using guest layout
- [x] Add throttle middleware to admin login route (max 5 attempts per minute)

## Task 5: Middleware & RBAC

- [x] Create `app/Http/Middleware/EnsureUserIsAdmin.php`: check `role === UserRole::Admin`, abort 403 or redirect to dashboard
- [x] Create `app/Http/Middleware/EnsureUserIsActive.php`: check `is_active === true`, logout and redirect if inactive
- [x] Register middleware aliases in `bootstrap/app.php` or route service provider
- [x] Apply `auth` + `active` middleware to all authenticated route groups
- [x] Apply `admin` middleware to all `/admin/*` routes (except login)
- [x] Verify unauthenticated users are redirected to `/login`

## Task 6: Layouts & Base Views (TailwindCSS + Design System)

- [x] Configure TailwindCSS v4 with design tokens from DESIGN.md (colors, typography, spacing, rounded)
- [x] Install Comfortaa font via Google Fonts in layout
- [x] Create `resources/views/layouts/guest.blade.php`: centered card layout for login pages
- [x] Create `resources/views/layouts/app.blade.php`: authenticated layout with bottom navigation (mobile) and sidebar (desktop), FAB button for scan
- [x] Create `resources/views/components/nav-link.blade.php`: navigation link component
- [x] Create `resources/views/components/status-badge.blade.php`: pill-shaped status indicator (expired/expiring-soon/safe)
- [x] Create `resources/views/components/summary-card.blade.php`: dashboard summary card with count + color
- [x] Create `resources/views/components/product-card.blade.php`: product thumbnail + info display
- [x] Run `npm run build` to verify TailwindCSS compiles correctly

## Task 7: Dashboard (Req 12)

- [x] Create `app/Http/Controllers/DashboardController.php` with `index()` and `filtered()` methods
- [x] In `index()`: query authenticated user's tracked items, group counts by status (expired, expiring_soon, safe)
- [x] Classification logic: expired = expiry_date < today, expiring_soon = expiry_date between today and +7 days, safe = expiry_date > +7 days
- [x] Create `resources/views/dashboard/index.blade.php`: 3 summary cards (color-coded) + recent tracked items list + FAB to scan
- [x] In `filtered()`: accept status parameter, return filtered tracked items list for that category
- [x] Register routes: GET `/dashboard`, GET `/dashboard/{status}`
- [x] Handle empty state: show message "No items being tracked" when user has no tracked items

## Task 8: Admin - Category Management (Req 3.7, 3.8)

- [x] Create `app/Http/Controllers/Admin/CategoryController.php` with `index()`, `store()`, `update()`, `destroy()` methods
- [x] Create `app/Http/Requests/StoreCategoryRequest.php`: validate name (required, max 100, unique:categories)
- [x] Create `resources/views/admin/categories/index.blade.php`: inline CRUD — list with add form at top, edit/delete buttons per row
- [x] On delete: set `category_id = null` for products in that category (handled by FK set null)
- [x] Register admin routes for categories

## Task 9: Admin - Product Management (Req 3)

- [x] Create `app/Http/Controllers/Admin/ProductController.php` with `index()`, `create()`, `store()`, `edit()`, `update()`, `destroy()` methods
- [x] Create `app/Http/Requests/StoreProductRequest.php`: validate name, barcode (unique), category_id (exists), image (nullable, mimes jpg/png/webp, max 2MB), shelf_life_days (nullable, integer, min 1)
- [x] Create `app/Http/Requests/UpdateProductRequest.php`: same as store but barcode unique ignores current product
- [x] Handle image upload: store in `storage/app/public/products/`, save path in DB. Delete old image on update/delete
- [x] Create `resources/views/admin/products/index.blade.php`: product list with image thumbnails, category filter, search by name/barcode
- [x] Create `resources/views/admin/products/create.blade.php`: form with image upload, category dropdown, barcode input
- [x] Create `resources/views/admin/products/edit.blade.php`: pre-filled edit form
- [x] On delete: cascade removes tracked_items (handled by FK). Flash notification about affected users
- [x] Register admin routes for products
- [x] Run `php artisan storage:link` to ensure public access to uploaded images

## Task 10: Barcode Scanner (Req 11)

- [x] Install html5-qrcode: `npm install html5-qrcode`
- [x] Create `app/Http/Controllers/BarcodeScanController.php` with `index()` and `lookup()` methods
- [x] In `lookup()`: accept barcode via POST, query `Product::where('barcode', $barcode)->first()`, return JSON (product data or 404)
- [x] Create `resources/views/scan/index.blade.php`: full-screen camera scanner UI with html5-qrcode integration
- [x] Add manual barcode input field as fallback
- [x] On successful scan: JS redirects to `/tracked-items/create/{product_id}`
- [x] On product not found: show message with link to `/product-requests/create?barcode={scanned_barcode}`
- [x] Register routes: GET `/scan`, POST `/scan/lookup`
- [x] Run `npm run build` to include html5-qrcode in compiled assets

## Task 11: Tracked Items CRUD (Req 4, 6)

- [x] Create `app/Http/Controllers/TrackedItemController.php` with `index()`, `create()`, `createForProduct()`, `store()`, `edit()`, `update()`, `destroy()` methods
- [x] Create `app/Http/Requests/StoreTrackedItemRequest.php`: validate product_id (exists), expiry_date (required, date, after:today), remind_preset (nullable), remind_at_custom (nullable, date, before:expiry_date, after_or_equal:today). Validate unique combo (user_id, product_id, expiry_date)
- [x] Create `app/Http/Requests/UpdateTrackedItemRequest.php`: similar validation, ignore current record for uniqueness
- [x] In `store()`: calculate `remind_at` from preset or custom date, create TrackedItem
- [x] In `update()`: recalculate `remind_at`, reset `reminder_status` to pending if remind_at changed
- [x] In `createForProduct()`: pre-fill product info from route parameter
- [x] Create `resources/views/tracked-items/index.blade.php`: list with status badges, product image, expiry date, reminder status. Sort by expiry date ascending
- [x] Create `resources/views/tracked-items/create.blade.php`: product selector (or pre-filled), expiry date picker, reminder preset radio buttons (H-7, H-14, H-30, B-1, B-2, B-3, custom, none)
- [x] Create `resources/views/tracked-items/edit.blade.php`: edit expiry date + change reminder
- [x] Add validation: if user has no `telegram_user_id` set and tries to set a reminder, show warning linking to profile page
- [x] Register tracked item routes

## Task 12: Product Addition Requests (Req 5)

- [x] Create `app/Http/Controllers/ProductRequestController.php` (user-facing) with `index()`, `create()`, `store()` methods
- [x] Create `app/Http/Controllers/Admin/ProductRequestController.php` with `index()`, `approve()`, `reject()` methods
- [x] Create `app/Http/Requests/StoreProductAdditionRequest.php`: validate name (1-255), barcode (8-13 digits, unique in products and pending product_requests), description (nullable, max 500)
- [x] In user `store()`: create ProductRequest with status pending
- [x] In admin `approve()`: create Product from request data, update request status to approved
- [x] In admin `reject()`: validate rejection_reason (1-500), update status to rejected
- [x] Create `resources/views/product-requests/index.blade.php`: user's own requests with status badges
- [x] Create `resources/views/product-requests/create.blade.php`: form with barcode pre-filled if coming from scan
- [x] Create `resources/views/admin/product-requests/index.blade.php`: pending requests queue with approve/reject actions
- [x] Register routes for both user and admin product request controllers

## Task 13: Profile & Telegram Linking (Req 8)

- [x] Create `app/Http/Controllers/ProfileController.php` with `edit()` and `update()` methods
- [x] Create `app/Http/Requests/UpdateProfileRequest.php`: validate telegram_user_id (nullable, numeric, max 20 chars)
- [x] Create `resources/views/profile/edit.blade.php`: show name (readonly, from Google), email (readonly), Telegram User ID input, linking status indicator, instructions on how to find Telegram ID and /start the bot
- [x] In `update()`: save telegram_user_id, flash success message
- [x] Register routes: GET `/profile`, PUT `/profile`

## Task 14: Admin User Monitoring (Req 10)

- [x] Create `app/Http/Controllers/Admin/UserController.php` with `index()`, `toggleActive()`, `updateRole()` methods
- [x] In `index()`: paginate users (15 per page), include stats (total users, active in last 30 days, telegram-linked count). Show each user's name, email, avatar, role, telegram status, tracked items count
- [x] In `toggleActive()`: prevent self-deactivation. On deactivate: set is_active = false, invalidate sessions (delete from sessions table), cancel pending reminders (set reminder_status to failed). On reactivate: set is_active = true
- [x] In `updateRole()`: prevent changing own role, update role enum
- [x] Create `resources/views/admin/users/index.blade.php`: user list with stats header, toggle/role buttons per row
- [x] Register admin user routes

## Task 15: Telegram Notification Service (Req 7)

- [x] Create `app/Services/TelegramNotifierService.php` with `sendMessage(string $telegramUserId, string $message): bool` and `isUnrecoverableError(int $statusCode, string $description): bool`
- [x] Implement `sendMessage()`: POST to `https://api.telegram.org/bot{token}/sendMessage` with chat_id and text. Return true on success, throw/return false on failure
- [x] Implement `isUnrecoverableError()`: return true for 403 (blocked) and 400 (chat not found) status codes
- [x] Add `telegram.bot_token` to `config/services.php` reading from `TELEGRAM_BOT_TOKEN` env
- [x] Add `TELEGRAM_BOT_TOKEN=` to `.env.example`

## Task 16: Notification Job & Scheduler (Req 9)

- [x] Create `app/Jobs/SendTelegramNotificationJob.php`: accept TrackedItem, compose message (product name, expiry date, days remaining), send via TelegramNotifierService
- [x] Configure job: 3 tries, 30-second backoff. On success: update `reminder_status = sent`, set `reminder_sent_at`. On unrecoverable error: clear user's telegram_user_id, mark as failed. On exhausted retries: mark as failed
- [x] Create `app/Console/Commands/ProcessExpiryRemindersCommand.php`: query tracked_items with remind_at <= today, reminder_status = pending, remind_at not null, user has telegram_user_id and is_active. Dispatch job for each in chunks of 30
- [x] Register command in Laravel scheduler (`routes/console.php` or `app/Console/Kernel.php`): run daily at 07:00
- [x] Add notification message format: "⚠️ Reminder: {product_name} akan expired pada {expiry_date} ({days_remaining} hari lagi). Segera cek stok!"

## Task 17: Seeder & Final Setup

- [x] Update `DatabaseSeeder.php`: seed admin user (email: admin@example.com, password: password, role: admin), seed 5-10 categories (Minuman, Makanan Ringan, Susu & Dairy, Roti, Bumbu Dapur, Frozen Food, Snack, Minuman Bersoda), seed 20+ sample products with barcodes
- [x] Run full migration + seed: `php artisan migrate:fresh --seed`
- [x] Run `php artisan storage:link`
- [x] Run `npm run build`
- [x] Verify login flow works (both OAuth and admin)
- [x] Run `vendor/bin/pint --format agent` to format all PHP files

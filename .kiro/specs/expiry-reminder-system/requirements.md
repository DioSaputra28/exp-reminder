# Requirements Document

## Introduction

The Expiry Reminder System is a web application designed for minimarket employees to track product expiry dates and receive timely notifications via Telegram. The system supports multi-user access with role-based permissions (Admin and User), enabling efficient management of product inventory and configurable reminder schedules to prevent expired products from remaining on shelves.

## Glossary

- **System**: The Expiry Reminder web application built with Laravel
- **Admin**: A user with elevated privileges who can add product data, monitor users, and review product addition requests
- **User**: A regular minimarket employee who can track product expiry dates, set reminders, and submit product addition requests
- **Product**: A catalog item managed by Admins containing product name, barcode, category, and shelf life information
- **Tracked_Item**: A record created by a User linking a Product to a specific expiry date for monitoring
- **Reminder**: A scheduled notification configured to alert a user before a tracked item expires
- **Reminder_Offset**: The time duration before the expiry date when a reminder is triggered, expressed as days (H-) or months (B-)
- **Product_Request**: A request submitted by a User asking an Admin to add a new product to the catalog
- **Telegram_Notifier**: The service responsible for sending reminder notifications via the Telegram Bot API
- **Scheduler**: The Laravel scheduled task that checks for products nearing expiry and dispatches notifications

## Requirements

### Requirement 1: User Authentication

**User Story:** As a minimarket employee, I want to log in with my Google account, so that I can quickly access the system without remembering another password.

#### Acceptance Criteria

1. WHEN a user clicks "Login with Google" on the login page, THE System SHALL redirect them to the Google OAuth consent screen
2. WHEN Google returns a successful authentication response, THE System SHALL create a new User account (if first login) or log in the existing user, with a default "User" role
3. IF the Google OAuth flow is cancelled or fails, THEN THE System SHALL redirect the user back to the login page with an error message
4. WHEN an authenticated user requests logout, THE System SHALL terminate the session and redirect to the login page
5. WHEN an Admin navigates to `/admin/login`, THE System SHALL display an email/password login form for admin access
6. IF invalid admin login credentials are submitted, THEN THE System SHALL display an error message indicating that the credentials are incorrect and deny access
7. THE System SHALL store the user's Google ID, name, email, and avatar from the OAuth response

### Requirement 2: Role-Based Access Control

**User Story:** As an Admin, I want to control access based on roles, so that only authorized users can perform their designated actions.

#### Acceptance Criteria

1. THE System SHALL assign exactly one role to each user at any time: Admin or User
2. WHILE a user has the Admin role, THE System SHALL grant access to user monitoring, product data management, product request review features, and all features available to the User role
3. WHILE a user has the User role, THE System SHALL grant access only to viewing products, setting reminders, submitting product addition requests, and the product expiry dashboard, and SHALL deny access to admin-only features
4. IF a user without Admin role attempts to access an admin-only resource, THEN THE System SHALL deny the request, return a 403 Forbidden response, and redirect the user to their dashboard
5. WHEN an Admin changes another user's role, THE System SHALL update the role immediately and enforce the new permissions on the user's next request

### Requirement 3: Product Data Management (Admin)

**User Story:** As an Admin, I want to add and manage product data in the system, so that Users have accurate product records to track expiry dates against.

#### Acceptance Criteria

1. WHEN an Admin submits a valid product data form, THE System SHALL create a new Product record with the product name (maximum 255 characters), barcode (unique, maximum 50 characters), category (selected from existing categories), optional product image, and default shelf life expressed in days (minimum 1 day)
2. IF an Admin submits a product data form with a barcode that already exists in the system, THEN THE System SHALL reject the submission and display a validation error indicating the barcode is already registered
3. WHEN an Admin edits a product record, THE System SHALL update the Product data with the new values and retain the product's associations with existing Tracked_Items
4. WHEN an Admin deletes a product record, THE System SHALL remove the Product and all associated Tracked_Items and Reminders, and send an in-app notification to each affected User who had Tracked_Items linked to that product
5. IF a product data form is submitted with missing required fields, THEN THE System SHALL display validation error messages for each missing field without clearing the previously entered data
6. WHEN an Admin uploads a product image, THE System SHALL validate the file is jpg, png, or webp format with a maximum size of 2MB and store it in the public storage directory
7. THE System SHALL provide a categories management interface where Admin can create, edit, and delete product categories
8. IF an Admin deletes a category that has products assigned, THEN THE System SHALL set the category_id of those products to null rather than deleting the products

### Requirement 4: Product Expiry Tracking (User)

**User Story:** As a minimarket employee, I want to register product items with their expiry dates, so that I can track which items on the shelf are approaching expiration.

#### Acceptance Criteria

1. WHEN a user selects a product from the product catalog and submits an expiry date, THE System SHALL create a tracked item record linking the user, product, and expiry date
2. IF a user submits an expiry date that is not a valid calendar date or is not later than the current date, THEN THE System SHALL reject the submission and display a validation error message indicating the expiry date must be a future date
3. WHEN a user requests their tracked items list, THE System SHALL display all items belonging to that user with their product name, barcode, expiry date, and expiry status classified as "expired" (past current date), "expiring soon" (within 7 days from current date), or "safe" (more than 7 days from current date)
4. WHEN a user edits a tracked item, THE System SHALL update the expiry date and recalculate reminder schedules
5. WHEN a user deletes a tracked item, THE System SHALL remove the tracked item record and all associated Reminders
6. IF a user attempts to track a product that is already in their tracked items list with the same expiry date, THEN THE System SHALL reject the submission and display a message indicating a duplicate entry

### Requirement 5: Product Addition Request

**User Story:** As a minimarket employee, I want to submit a request for a new product to be added to the system, so that I can track products that are not yet in the catalog.

#### Acceptance Criteria

1. WHEN a user submits a product addition request with a product name (1-255 characters), a barcode (8-13 digit numeric string), and an optional description (max 500 characters), THE System SHALL create a Product_Request record with status "pending" and record the submission timestamp
2. IF a user submits a product addition request with a barcode that already exists in the Product catalog or in another pending Product_Request, THEN THE System SHALL reject the submission and display an error message indicating the barcode is already registered or requested
3. IF a product addition request is submitted with a missing product name or missing barcode or values exceeding the allowed length, THEN THE System SHALL display validation error messages for each invalid field
4. WHEN an Admin views the request queue, THE System SHALL display all pending requests showing the requester name, product name, barcode, description, and submission date, ordered by submission date descending
5. WHEN an Admin approves a request, THE System SHALL create a new Product record using the product name and barcode from the request data, and update the request status to "approved"
6. WHEN an Admin rejects a request, THE System SHALL require a rejection reason (1-500 characters), update the request status to "rejected", and store the rejection reason
7. WHEN a user views their submitted requests list, THE System SHALL display each request with its current status, and IF rejected, THE System SHALL display the rejection reason

### Requirement 6: Reminder Configuration

**User Story:** As a minimarket employee, I want to set a reminder for each tracked item, so that I am notified at the right time before a product expires.

#### Acceptance Criteria

1. WHEN a user creates or edits a tracked item, THE System SHALL provide preset reminder options (H-7, H-14, H-30, B-1, B-2, B-3) and a custom date option for setting the reminder date
2. WHEN a user selects a preset (e.g., H-7), THE System SHALL automatically calculate the `remind_at` date by subtracting the offset from the expiry date
3. IF the calculated `remind_at` date is in the past or on/after the expiry date, THEN THE System SHALL reject the reminder and display a validation error message indicating the offset is invalid
4. WHEN a user selects the custom date option, THE System SHALL accept a manually entered date that must be before the expiry date and not in the past
5. THE System SHALL store exactly one reminder date (`remind_at`) per tracked item
6. WHEN a user clears the reminder on a tracked item, THE System SHALL set `remind_at` to null and reset the reminder status to pending

### Requirement 7: Telegram Notification Delivery

**User Story:** As a minimarket employee, I want to receive reminder notifications via Telegram, so that I am alerted about expiring products on a platform I use daily.

#### Acceptance Criteria

1. WHEN a tracked item reaches its Reminder_Offset date, THE Telegram_Notifier SHALL send a notification message to the user's registered Telegram chat
2. THE Telegram_Notifier SHALL include the product name, expiry date, and remaining calendar days until expiry in the notification message
3. IF the Telegram API returns an error, THEN THE System SHALL log the failure and retry the notification up to 3 times with a minimum interval of 30 seconds between each attempt
4. IF all retry attempts fail, THEN THE System SHALL mark the notification as failed and log the error details
5. IF the Telegram API returns an unauthorized or chat-not-found error indicating the user has blocked the bot or the chat ID is invalid, THEN THE System SHALL mark the notification as failed without retrying and update the user's Telegram linking status to inactive

### Requirement 8: Telegram Account Linking

**User Story:** As a minimarket employee, I want to link my Telegram account to the system, so that I can receive notifications on my Telegram chat.

#### Acceptance Criteria

1. WHEN a user navigates to their profile page, THE System SHALL display a Telegram User ID input field
2. WHEN a user submits a valid Telegram User ID (numeric string, max 20 characters), THE System SHALL store the Telegram User ID and mark the account as linked
3. IF a user submits a non-numeric or empty Telegram User ID, THEN THE System SHALL display a validation error message
4. THE System SHALL display the current Telegram linking status (linked or not linked) on the user profile page
5. WHEN a user clears their Telegram User ID and saves, THE System SHALL remove the stored ID and update the linking status to not linked
6. IF a user attempts to set a reminder without a linked Telegram account, THEN THE System SHALL display a message instructing the user to set their Telegram User ID in their profile first
7. THE System SHALL display instructions on how to find Telegram User ID (e.g., via @userinfobot) and that the user must /start the bot first

### Requirement 9: Scheduled Expiry Check

**User Story:** As a minimarket employee, I want the system to automatically check for expiring products daily, so that reminders are sent without manual intervention.

#### Acceptance Criteria

1. THE Scheduler SHALL run once daily and query all tracked items that have `remind_at` set, `reminder_status = 'pending'`, and belong to users with a Telegram User ID set and account active
2. WHEN the current date matches or exceeds a tracked item's `remind_at` date, THE Scheduler SHALL dispatch a Telegram notification job for that tracked item
3. THE Scheduler SHALL process tracked items in batches of no more than 30 notifications per second to remain within Telegram API rate limits
4. WHEN the Telegram_Notifier confirms successful delivery of a notification, THE System SHALL update the tracked item's `reminder_status` to "sent" and set `reminder_sent_at` to prevent duplicate notifications
5. IF the Scheduler encounters a tracked item whose associated user no longer has a Telegram User ID set, THEN THE System SHALL skip that item and log a warning

### Requirement 10: Admin User Monitoring

**User Story:** As an Admin, I want to monitor registered users, so that I can track system usage and manage access.

#### Acceptance Criteria

1. WHILE a user has the Admin role, THE System SHALL display a user overview showing total registered users, active users (users who have logged in within the last 30 days), and users with linked Telegram accounts
2. WHEN an Admin views the user list, THE System SHALL display each user's name, email, role, Telegram linking status, and number of tracked items, paginated at a maximum of 15 users per page
3. WHEN an Admin deactivates a user account, THE System SHALL prevent that user from logging in, invalidate any existing sessions for that user, and cancel all pending reminders for that user
4. IF an Admin attempts to deactivate their own account, THEN THE System SHALL reject the action and display an error message indicating that self-deactivation is not permitted
5. WHEN an Admin reactivates a previously deactivated user account, THE System SHALL restore login access for that user without restoring previously cancelled reminders

### Requirement 11: Barcode Scan for Quick Tracking

**User Story:** As a minimarket employee, I want to scan a product barcode to quickly find it in the system, so that I can set its expiry date without manually searching.

#### Acceptance Criteria

1. WHEN a user opens the scan page, THE System SHALL activate the device camera and begin detecting barcodes using a client-side barcode scanner library
2. WHEN a barcode is detected and matches a product in the products table, THE System SHALL redirect the user to the tracked item creation form with the product pre-filled
3. IF a detected barcode does not match any product in the system, THEN THE System SHALL display a message indicating the product is not found and offer an option to submit a Product Addition Request with the barcode pre-filled
4. THE System SHALL provide a manual barcode input field as a fallback for devices without camera access
5. THE System SHALL display the scan page as the primary action accessible via a FAB (Floating Action Button) on the dashboard

### Requirement 12: Product Expiry Dashboard

**User Story:** As a minimarket employee, I want to see a dashboard overview of my products grouped by expiry status, so that I can quickly identify items needing attention.

#### Acceptance Criteria

1. WHEN a user accesses the dashboard, THE System SHALL display counts of the authenticated user's tracked items grouped into three categories: expired (expiry date before today), expiring soon (expiry date from today up to and including 7 days from today), and safe (expiry date more than 7 days from today)
2. THE System SHALL color-code the dashboard categories using status-danger for expired, status-warning for expiring soon, and status-safe for safe products
3. WHEN a user clicks on a dashboard category, THE System SHALL navigate to a filtered product list showing only the authenticated user's tracked items in that category
4. THE System SHALL recalculate the dashboard counts from current data each time the user accesses the dashboard page
5. IF the authenticated user has no tracked items, THEN THE System SHALL display zero for all category counts and show a message indicating no items are being tracked

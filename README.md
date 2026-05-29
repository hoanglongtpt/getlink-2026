# GetLink 2026

A Laravel application that integrates Web2m for payment, Getstock for file downloading, and Google Drive API for cloud storage.

## Project Overview

This application supports:

- User registration and login with email/password
- Google OAuth login
- Wallet balance management (`Xu`)
- Web2m payment webhook handling
- Download workflow with cache hit and cache miss handling
- Background job processing for Getstock polling and Google Drive upload
- Admin panel for users, transactions, resources, and settings

## Main Components

- `users`: application users, Google ID, wallet balance, role
- `transactions`: payment history and converted Xu
- `resources`: cached downloads and Google Drive metadata
- `download_histories`: download request history, Getstock metadata, direct links
- `settings`: admin configuration such as `download_fee`

## Setup Instructions

### Requirements

- PHP 8.1 or higher
- Composer
- MySQL
- Redis (recommended for queue processing)

### Installation

1. Clone the repository:

   ```bash
   git clone <repository-url> .
   ```

2. Install dependencies:

   ```bash
   composer install
   ```

3. Copy the environment file:

   ```bash
   cp .env.example .env
   ```

4. Configure your `.env` values:

   ```env
   APP_NAME=GetLink
   APP_ENV=local
   APP_KEY=base64:YOUR_APP_KEY
   APP_URL=http://localhost

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=getlink_2026
   DB_USERNAME=root
   DB_PASSWORD=

   QUEUE_CONNECTION=database
   CACHE_DRIVER=file
   SESSION_DRIVER=file
   ```

5. Add third-party API variables:

   ```env
   GETSTOCK_EMAIL=your_getstock_email
   GETSTOCK_PASSWORD=your_getstock_password
   GETSTOCK_ACCESS_TOKEN=
   WEB2M_WEBHOOK_SECRET=your_web2m_webhook_secret

   GOOGLE_CLIENT_ID=your_google_client_id
   GOOGLE_CLIENT_SECRET=your_google_client_secret
   GOOGLE_REDIRECT_URI=http://localhost/auth/google/callback
   ```

6. Run database migrations and seed default data:

   ```bash
   php artisan migrate --seed
   ```

7. Create a storage symlink:

   ```bash
   php artisan storage:link
   ```

8. Start the application:

   ```bash
   php artisan serve
   ```

### Queue Worker

To process background jobs, run:

```bash
php artisan queue:work
```

## Admin Access

Seeded default admin credentials:

- Email: `admin@example.com`
- Password: `password`

> Change the default password immediately in production.

## Google Service Account

Upload the Google service account JSON via the admin settings page. The JSON file is saved to `storage/app/google-service-account.json` and used for Google Drive uploads.

## Important Notes

- Store all secrets and API credentials only in `.env`.
- `GETSTOCK_ACCESS_TOKEN` can be optionally configured directly, otherwise the service uses login credentials.
- `download_fee` is stored in the `settings` table and can be updated from the admin panel.
- Background jobs are used to poll Getstock and upload to Google Drive.

## Useful Commands

- `composer install`
- `php artisan migrate --seed`
- `php artisan serve`
- `php artisan queue:work`

## Project Structure

- `app/Services/GetstockService.php`
- `app/Services/GoogleDriveService.php`
- `app/Jobs/ProcessGetstockDownload.php`
- `app/Jobs/ProcessDownloadedResource.php`
- `app/Http/Controllers/DownloadController.php`
- `app/Http/Controllers/AdminController.php`
- `app/Http/Controllers/AuthController.php`
- `app/Http/Controllers/ProfileController.php`

## License

This project is released under the MIT License.

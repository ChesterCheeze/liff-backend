# Admin Account Management

This document describes how to create and manage admin accounts in the application.

## Creating Admin Accounts

There are two methods to create admin accounts:

### 1. Using Artisan Command (Recommended for Production)

The application provides a secure interactive command to create admin accounts:

```bash
php artisan admin:create
```

You will be prompted to enter:
- Admin email address
- Admin name
- Password (hidden input)
- Password confirmation

Alternatively, you can provide email and name via command options:

```bash
php artisan admin:create --email=admin@example.com --name="Admin User"
```

The command performs validation to ensure:
- Valid email format
- Unique email address
- Name minimum length of 3 characters
- Password minimum length of 8 characters
- Password confirmation matches

### 2. Using Database Seeder (Development Only)

For development environments, you can use the database seeder to create a default admin account:

```bash
php artisan db:seed --class=AdminSeeder
```

> **Note**: This seeder only works in `local` or `development` environments and creates an admin account with:
> - Email: admin@example.com
> - Password: password

## Role-Based Access Control

### Middleware Protection

Admin-only routes can be protected using the `admin` middleware:

```php
Route::middleware(['auth', 'admin'])->group(function () {
    // Admin-only routes here
});
```

### Checking Admin Status

You can check if a user is an admin using the `isAdmin()` method:

```php
if ($user->isAdmin()) {
    // Admin-only logic here
}
```

## Security Considerations

- Admin accounts should only be created by authorized personnel
- Use strong passwords (minimum 8 characters)
- Always use the `admin` middleware to protect admin routes
- Regularly audit admin accounts and their activities
- Never commit sensitive admin credentials to version control
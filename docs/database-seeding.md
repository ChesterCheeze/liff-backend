# Database Seeder Documentation

## Overview

This document provides comprehensive information about the database seeding system for the Laravel Backend App, including configuration, usage, and best practices.

## Available Seeders

### AdminSeeder

The `AdminSeeder` class is responsible for creating and managing administrative users in the system. It provides a robust, configurable approach to admin user management.

#### Features

- **Environment-based configuration**: Configure admin credentials via environment variables
- **Duplicate prevention**: Automatically handles existing admin users
- **Multiple admin support**: Option to seed multiple admin accounts
- **Email verification**: Automatically sets email verification timestamps
- **Secure password handling**: Uses Laravel's Hash facade for password encryption

#### Configuration

Configure the admin seeder using the following environment variables in your `.env` file:

```env
# Primary admin user configuration
ADMIN_NAME="System Administrator"
ADMIN_EMAIL="admin@example.com"
ADMIN_PASSWORD="your-secure-password"

# Enable multiple admin users (optional)
SEED_MULTIPLE_ADMINS=true
```

#### Default Values

If environment variables are not set, the seeder will use these defaults:

- **Name**: "System Administrator"
- **Email**: "admin@example.com"
- **Password**: "admin123"
- **Role**: "admin"

#### Usage

##### Running the Seeder

```bash
# Run all seeders
php artisan db:seed

# Run only the AdminSeeder
php artisan db:seed --class=AdminSeeder

# Fresh migration with seeding
php artisan migrate:fresh --seed
```

##### Environment Setup

1. **Copy environment file:**
   ```bash
   cp .env.example .env
   ```

2. **Configure admin credentials:**
   ```env
   ADMIN_NAME="Your Admin Name"
   ADMIN_EMAIL="admin@yourdomain.com"
   ADMIN_PASSWORD="your-secure-password"
   ```

3. **Run the seeder:**
   ```bash
   php artisan db:seed --class=AdminSeeder
   ```

#### Multiple Admin Users

When `SEED_MULTIPLE_ADMINS=true` is set, the seeder will create additional admin accounts:

- **Admin User 2**: admin2@example.com (password: admin123)
- **Super Admin**: superadmin@example.com (password: superadmin123)

#### Security Considerations

1. **Change default passwords** immediately after seeding
2. **Use strong passwords** in production environments
3. **Set appropriate environment variables** for production
4. **Enable email verification** for admin accounts
5. **Regularly rotate admin passwords**

#### Database Structure

The seeder creates users with the following structure:

```php
[
    'name' => 'Admin Name',
    'email' => 'admin@example.com',
    'password' => 'hashed_password',
    'role' => 'admin',
    'email_verified_at' => '2025-01-01 00:00:00',
    'created_at' => '2025-01-01 00:00:00',
    'updated_at' => '2025-01-01 00:00:00',
]
```

#### Error Handling

The seeder includes built-in error handling:

- **Duplicate email prevention**: Updates existing users instead of creating duplicates
- **Role validation**: Ensures admin role is properly set
- **Password hashing**: Automatically hashes passwords using Laravel's Hash facade

## DatabaseSeeder

The main `DatabaseSeeder` class orchestrates all seeding operations.

### Current Configuration

```php
public function run(): void
{
    $this->call([
        AdminSeeder::class,
    ]);
}
```

### Adding New Seeders

To add new seeders to the database seeding process:

1. Create your seeder class:
   ```bash
   php artisan make:seeder YourSeederName
   ```

2. Add it to the `DatabaseSeeder`:
   ```php
   public function run(): void
   {
       $this->call([
           AdminSeeder::class,
           YourSeederName::class,
       ]);
   }
   ```

## Best Practices

### Development Environment

1. **Use realistic test data** that mimics production scenarios
2. **Seed enough data** to test application features thoroughly
3. **Include edge cases** in your test data
4. **Use factories** for generating large datasets

### Production Environment

1. **Never seed sensitive data** directly
2. **Use environment variables** for configuration
3. **Implement proper access controls** for seeding commands
4. **Log seeding operations** for audit trails
5. **Test seeding scripts** in staging before production

### Code Quality

1. **Follow Laravel conventions** for seeder naming and structure
2. **Use type hints** for method parameters and return types
3. **Include proper error handling** for edge cases
4. **Add meaningful comments** for complex logic
5. **Use transactions** for multi-step seeding operations

## Common Commands

```bash
# Fresh database with seeding
php artisan migrate:fresh --seed

# Seed without migration
php artisan db:seed

# Seed specific class
php artisan db:seed --class=AdminSeeder

# Show seeding status
php artisan db:seed --help

# Create new seeder
php artisan make:seeder NewSeederName
```

## Troubleshooting

### Common Issues

1. **Duplicate key errors**: Check for existing records before seeding
2. **Permission errors**: Ensure database user has proper permissions
3. **Memory issues**: Use chunking for large datasets
4. **Environment issues**: Verify `.env` file configuration

### Debug Mode

Enable debug mode to see detailed seeding output:

```bash
php artisan db:seed --class=AdminSeeder --verbose
```

## Related Documentation

- [Admin Management](admin-management.md)
- [Laravel Seeding Documentation](https://laravel.com/docs/seeding)
- [Database Migrations](https://laravel.com/docs/migrations)
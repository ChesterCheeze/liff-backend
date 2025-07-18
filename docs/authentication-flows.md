# Authentication & Login Flows

This document outlines the authentication system and login flows for new developers working on the backend application.

## Overview

The application supports multiple authentication mechanisms:
1. **Web Authentication** - Unified session-based authentication for all users (regular users and admins)
2. **API Authentication** - Token-based authentication using Laravel Sanctum
3. **Line OA Authentication** - Special authentication for Line Official Account users

## Authentication Configuration

### Guards and Providers

The authentication system is configured in `config/auth.php`:

- **Default guard**: `web` (session-based, unified for all users)
- **User provider**: Eloquent model `App\Models\User`
- **Line OA provider**: Eloquent model `App\Models\LineOAUser`

**Key Design Decision**: The application uses a **unified authentication guard** where both regular users and admins authenticate through the same `web` guard. Access control is managed through role-based permissions rather than separate authentication sessions.

### Models

#### User Model (`app/Models/User.php`)
- Uses `HasApiTokens`, `HasFactory`, `Notifiable` traits
- Contains `isAdmin()` method to check admin role
- Fillable fields: `name`, `email`, `password`, `role`

#### LineOAUser Model (`app/Models/LineOAUser.php`)
- Uses `HasApiTokens`, `HasFactory`, `Notifiable` traits
- Contains `isAdmin()` method for admin role checking
- Fillable fields: `line_id`, `name`, `picture_url`, `role`

## Authentication Flows

### 1. Regular User Web Authentication

#### Registration Flow
**Route**: `POST /register`
**Controller**: `LoginController@register`

1. User submits registration form with name, email, password, and password confirmation
2. Request validation:
   - `name`: required, string, max 255 characters
   - `email`: required, valid email, max 255 characters, unique in users table
   - `password`: required, string, min 8 characters, must be confirmed
3. User record created with bcrypt password hashing
4. User automatically logged in using `Auth::login()`
5. Session regenerated for security
6. Redirect to home page with success message

#### Login Flow
**Route**: `POST /login`
**Controller**: `LoginController@authenticate`

1. User submits login form with email and password
2. Credentials validated (email format, password required)
3. `Auth::attempt()` checks credentials against users table
4. If successful:
   - Session regenerated with `$request->session()->regenerate()`
   - Redirect to intended URL or home page
5. If failed:
   - Redirect back with error message
   - Only email field retained in form

#### Logout Flow
**Route**: `POST /logout`
**Controller**: `LoginController@logout`

1. `Auth::logout()` clears authentication
2. Session invalidated and token regenerated
3. Redirect to home page with success message

### 2. Admin Authentication

**Important**: Admin authentication uses the same unified `web` guard as regular users. Access control is managed through role verification rather than separate authentication sessions.

#### Admin Login Flow
**Route**: `POST /admin/login`
**Controller**: `LoginController@authenticateAdmin`

1. Admin submits login form with email and password
2. Credentials validated (same as regular login)
3. `Auth::attempt()` attempts authentication with web guard
4. If credentials valid:
   - Check if user has admin role using `$user->isAdmin()`
   - If admin role confirmed:
     - Session regenerated
     - Redirect to `/admin/dashboard`
   - If not admin:
     - Logout from web guard
     - Redirect to admin login with access denied error
5. If credentials invalid:
   - Redirect to admin login with error message

#### Admin Logout Flow
**Route**: `POST /admin/logout`
**Controller**: `LoginController@logoutAdmin`

1. `Auth::logout()` clears authentication from web guard
2. Session invalidated and token regenerated
3. Redirect to admin login page

#### Admin Route Protection
Admin routes are protected by middleware group:
```php
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(...)
```

**IsAdmin Middleware** (`app/Http/Middleware/IsAdmin.php`):
1. Checks if user is authenticated with web guard
2. Verifies user has admin role using `isAdmin()` method
3. Returns 403 error if not authorized

#### Unified Authentication Benefits

**Key Advantage**: Since admins and regular users share the same authentication session:
- **Seamless Homepage Experience**: Admins appear logged in when visiting the homepage
- **Consistent Navigation**: Admin users see appropriate navigation and user info everywhere
- **Simplified Session Management**: Single authentication state across the application
- **Role-Based UI**: Templates use `Auth::user()->isAdmin()` to show role-appropriate content

### 3. API Authentication

#### API Login Flow
**Route**: `POST /api/login`
**Controller**: `LoginController@apiLogin`

1. Client submits credentials (email, password)
2. Credentials validated
3. `Auth::attempt()` verifies credentials
4. If successful:
   - Generate Sanctum API token using `$user->createToken('api-token')`
   - Return JSON response with access token, token type, and user data
5. If failed:
   - Return 401 Unauthorized with error message

#### API Token Usage
Protected API routes use `auth:sanctum` middleware:
```php
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
```

### 4. Line OA User Authentication

#### Line User Registration/Login Flow
**Route**: `POST /api/lineuser`
**Controller**: `LineOAUserController@store`

1. Line OA client submits user data:
   - `lineId`: required
   - `name`: required
   - `pictureUrl`: required
2. Check if user already exists by `line_id`
3. If new user:
   - Create LineOAUser record
   - Generate Sanctum API token
   - Return success message with API token
4. If existing user:
   - Return message indicating user already registered

## Security Features

### Session Management
- Session regeneration on login/logout for security
- Session invalidation on logout
- CSRF protection via `VerifyCsrfToken` middleware

### Password Security
- Passwords hashed using bcrypt
- Minimum 8 character requirement
- Password confirmation required for registration

### Role-Based Access Control
- Admin role checking in User and LineOAUser models
- Admin middleware for route protection
- **Unified Guard System**: Single authentication session with role-based access control
- Smart redirect system for admin users accessing login pages

### API Security
- Laravel Sanctum for API token management
- Token-based authentication for API endpoints
- Ability to revoke tokens when needed

## Key Files Reference

### Routes
- `routes/web.php` - Web authentication routes
- `routes/api.php` - API authentication routes

### Controllers
- `app/Http/Controllers/LoginController.php:19` - Web login method
- `app/Http/Controllers/LoginController.php:93` - Admin login method
- `app/Http/Controllers/LoginController.php:38` - API login method
- `app/Http/Controllers/LineOAUserController.php:11` - Line OA user registration

### Models
- `app/Models/User.php:47` - Admin role checking
- `app/Models/LineOAUser.php:18` - Line user admin checking

### Middleware
- `app/Http/Middleware/IsAdmin.php:13` - Admin authorization (unified guard)
- `app/Http/Middleware/Authenticate.php` - General authentication
- `app/Http/Middleware/RedirectIfAuthenticated.php:23` - Smart admin/user redirect logic

### Configuration
- `config/auth.php:38` - Authentication guards configuration (unified web guard)
- `config/auth.php:66` - User providers configuration

## Unified Authentication Architecture

### Design Decision: Single Guard System

The application uses a **unified authentication guard** approach where both regular users and administrators authenticate through the same `web` guard. This architectural decision provides several key benefits:

#### Benefits

1. **Seamless User Experience**
   - Admins remain authenticated when navigating between admin panel and main application
   - Homepage correctly displays admin as logged in
   - Consistent navigation and user interface across all areas

2. **Simplified Session Management**
   - Single authentication state to manage
   - Reduced complexity in session handling
   - Eliminates issues with multiple concurrent sessions

3. **Role-Based Security**
   - Access control managed through user roles rather than separate authentication systems
   - Clear separation of concerns: authentication vs. authorization
   - Easier to extend with additional roles in the future

4. **Development Efficiency**
   - Simpler to debug authentication issues
   - Consistent `Auth::user()` usage throughout the application
   - Reduced middleware complexity

#### Technical Implementation

- **Single Guard**: All users authenticate through the `web` guard
- **Role Verification**: Admin access controlled via `$user->isAdmin()` checks
- **Smart Redirects**: Middleware intelligently routes users based on their role and intended destination
- **Unified Session**: Single session stores authentication state for entire application

#### Security Considerations

While using a unified guard, security is maintained through:
- Role-based access control at the route and controller level
- Admin-specific middleware that verifies both authentication and authorization
- Proper session management with regeneration on login/logout
- CSRF protection across all authenticated routes

## Testing Authentication

The application includes comprehensive tests for authentication flows:
- `tests/Feature/Admin/AdminAuthenticationTest.php` - Admin authentication tests
- `tests/Feature/Admin/AdminLoginTest.php` - Admin login specific tests

## Development Notes

1. **Unified Authentication**: The system uses a single `web` guard for both regular users and admins, with role-based access control providing security separation
2. **Role Checking**: Admin access is controlled through role verification using the `isAdmin()` method
3. **Seamless User Experience**: Admins remain authenticated across all parts of the application (homepage, admin dashboard, etc.)
4. **API Tokens**: Sanctum provides stateless API authentication with revokable tokens
5. **Line Integration**: Special handling for Line Official Account users with separate model and registration flow
6. **Security First**: All authentication flows include proper session management and CSRF protection
7. **Smart Redirects**: Already authenticated admins are automatically redirected to dashboard when accessing login pages

## Common Debugging Tips

1. **Login Issues**: Check if user exists and has correct role in database
2. **Admin Access Denied**: Verify user has `role = 'admin'` in users table
3. **API Authentication**: Ensure Sanctum is properly configured and tokens are being sent in Authorization header
4. **Session Issues**: Clear cache and sessions if experiencing authentication state problems
5. **Admin Not Showing as Logged In**: Verify unified guard system is working - admins should appear authenticated on homepage after logging into admin panel
6. **Redirect Issues**: Check `RedirectIfAuthenticated` middleware for proper admin/user routing logic
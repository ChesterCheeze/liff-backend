# Agent Guidelines for Laravel Backend App

## Commands
- Run all tests: `php artisan test`
- Run single test: `php artisan test --filter TestName`
- Run tests in a directory: `php artisan test tests/Feature/Admin`
- Code style check/fix: `./vendor/bin/pint`
- Clear cache: `php artisan cache:clear`

## Code Style
- Use PSR-4 autoloading (App\ namespace for app code)
- Indentation: 4 spaces (2 spaces for YAML)
- File encoding: UTF-8 with LF line endings
- Models: PascalCase singular (e.g., User, Survey)
- Controllers: PascalCase + Controller (e.g., UserController)
- Methods: camelCase
- Variables/properties: camelCase
- Database columns: snake_case
- Error handling: Use Laravel's built-in exception handling through app/Exceptions/Handler.php
- Type hints: Required for method parameters and return types
- Imports: Group by type (Laravel core, models, other)
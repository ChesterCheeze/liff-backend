# Tailwind CSS Setup and Troubleshooting

## Required Setup
1. Install dependencies:
```bash
npm install
```

2. Build assets:
```bash
npm run build
```

3. Layout Requirements
- Every Blade layout must include Vite assets:
```php
@vite(['resources/css/app.css', 'resources/js/app.js'])
```
- Must be inside `<head>` tag with proper HTML structure:
```html
<!DOCTYPE html>
<html>
<head>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <!-- content -->
</body>
</html>
```

## Common Issues

### Styles Not Applying
If Tailwind styles are not working:

1. Check Build Status
- Ensure assets are built: `npm run build`
- Look for build output in `public/build/`

2. Check Layout Structure
- Verify Vite assets are included
- Confirm proper HTML structure with head/body tags

3. Development
- Use `npm run dev` during development
- Rebuild assets after Tailwind config changes

## Configuration
Tailwind is configured to process:
- Blade templates: `./resources/**/*.blade.php`
- JavaScript files: `./resources/**/*.js`
- Vue components: `./resources/**/*.vue`
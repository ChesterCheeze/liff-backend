# Theme Toggle Feature Documentation

## Overview
The theme toggle feature provides users with the ability to switch between light and dark themes. The implementation uses Alpine.js for state management and localStorage for persistence.

## Implementation Details

### State Management
- Theme state is managed through Alpine.js using a `dark` boolean variable
- The initial state is determined by:
  1. User's previous preference stored in localStorage
  2. System preference (prefers-color-scheme) if no stored preference exists

### Storage
- Theme preference is stored in `localStorage.theme` as either 'dark' or 'light'
- Storage is updated whenever the theme changes

### UI Components
- Located in `resources/views/components/top-bar.blade.php`
- Toggle button with sun/moon icons
- Smooth transitions and animations
- ARIA labels for accessibility

### Theme Application
- Dark mode classes are toggled on the `html` element
- Theme changes are applied immediately
- CSS classes are defined in Tailwind with dark: variants

## Usage
To implement the theme toggle in new pages:
1. Ensure the page inherits from the main layout
2. Use Tailwind's dark: variants for styling
3. Add dark mode specific styles where needed
# Admin Profile Management Feature

## Overview
Complete admin profile management system with avatar upload, profile information updates, and password change functionality.

## Features

### 1. Profile Information Management
- Update admin name
- Update admin email address
- Real-time validation
- Session data synchronization

### 2. Avatar Management
- Upload profile photo (JPG, PNG, GIF)
- Maximum file size: 2MB
- Image preview before upload
- Remove existing avatar
- Automatic fallback to initials
- Secure file storage in `storage/app/public/avatars`

### 3. Password Management
- Current password verification
- New password with confirmation
- Minimum 8 characters requirement
- Secure password hashing
- Independent form submission

### 4. User Experience
- Modern split-card design
- Real-time avatar preview
- Password visibility toggle
- Success/error notifications
- Form validation with error messages
- Account creation and update timestamps

## Routes

```php
GET    /admin/profile          - View profile page
PUT    /admin/profile/update   - Update profile information
PUT    /admin/profile/password - Update password
```

## Files Created/Modified

### Views
- `resources/views/admin/profile/index.blade.php` - Main profile page

### Controllers
- `app/Http/Controllers/Admin/ProfileController.php` - Profile management logic

### Routes
- `routes/web.php` - Added profile routes under admin middleware

### Layout
- `resources/views/layouts/admin.blade.php` - Updated dropdown menu with profile link

## Usage

### Accessing Profile Page
1. Click on your avatar in the top-right corner
2. Select "My Profile" from the dropdown menu
3. Or navigate directly to `/admin/profile`

### Updating Profile Information
1. Edit your name or email in the Profile Information section
2. Optionally upload a new avatar or remove existing one
3. Click "Save Changes"
4. Success message will appear on successful update

### Changing Password
1. Enter your current password
2. Enter new password (minimum 8 characters)
3. Confirm new password
4. Click "Update Password"
5. Success message will appear on successful update

## Security Features

- Current password verification before password change
- CSRF protection on all forms
- File type validation for avatar uploads
- File size limits (2MB max)
- Secure file storage with proper permissions
- Session data synchronization after updates
- Admin authentication middleware protection

## Avatar Storage

Avatars are stored in:
- Physical path: `storage/app/public/avatars/`
- Public URL: `/storage/avatars/`
- Naming convention: `admin_avatar_{user_id}_{timestamp}.{extension}`

Old avatars are automatically deleted when:
- A new avatar is uploaded
- The avatar is manually removed

## Database

Uses existing `users` table with `avatar_url` column:
- Column: `avatar_url` (string, nullable)
- Already exists in migration: `2014_10_12_000000_create_users_table.php`

## JavaScript Features

### Avatar Preview
```javascript
function previewAvatar(input) {
    // Shows preview of selected image before upload
}
```

### Password Visibility Toggle
```javascript
function togglePasswordVisibility(fieldId) {
    // Toggles between password and text input types
}
```

## Validation Rules

### Profile Update
- `name`: required, string, max 255 characters
- `email`: required, email, max 255 characters, unique (except current user)
- `avatar`: nullable, image (jpeg, jpg, png, gif), max 2MB

### Password Update
- `current_password`: required, string, must match existing password
- `password`: required, string, min 8 characters, must be confirmed
- `password_confirmation`: required, must match password

## Error Handling

All forms include:
- Server-side validation
- User-friendly error messages
- Visual error indicators
- Success/error flash messages
- Exception handling with user feedback

## Design System

Follows the admin dashboard design system:
- Tailwind CSS framework
- Green color scheme (green-700 primary)
- Phosphor Icons
- Rounded corners (rounded-xl, rounded-2xl)
- Consistent spacing and typography
- Responsive design
- Smooth transitions and hover effects

## Session Management

After profile updates, the session is synchronized with:
```php
session(['admin_user' => [
    'id' => $admin->id,
    'name' => $admin->name,
    'email' => $admin->email,
    'avatar_url' => $admin->avatar_url,
    'is_admin' => $admin->is_admin
]]);
```

This ensures the sidebar and header display updated information immediately.

## Future Enhancements

Potential improvements:
- Two-factor authentication setup
- Email change verification
- Activity log/audit trail
- Profile completion percentage
- Social media links
- Timezone and language preferences
- Notification preferences
- API token management

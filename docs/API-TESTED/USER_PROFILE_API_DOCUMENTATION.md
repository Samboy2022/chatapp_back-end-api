# 👤 User Profile API - Complete Documentation

**Base URL:** `http://your-domain.com/api`

All endpoints require authentication via Bearer token unless specified otherwise.

---

## 📋 Table of Contents

1. [Get Authenticated User](#1-get-authenticated-user)
2. [Update Profile](#2-update-profile)
3. [Update Profile (Settings)](#3-update-profile-settings)
4. [Get Profile (Settings)](#4-get-profile-settings)
5. [Change Password](#5-change-password)
6. [Delete Account](#6-delete-account)
7. [Export User Data](#7-export-user-data)
8. [Upload Avatar](#8-upload-avatar)

---

## 1. Get Authenticated User

Get the currently authenticated user's profile information.

**Endpoint:** `GET /api/auth/user`

**Headers:**
```json
{
  "Authorization": "Bearer {token}"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "phone_number": "+1234567890",
      "country_code": "+1",
      "avatar_url": "https://res.cloudinary.com/xxx/image/upload/avatar.jpg",
      "about": "Hello, I am using this app!",
      "is_online": true,
      "last_seen_at": "2025-10-09T12:00:00.000000Z",
      "created_at": "2025-01-01T00:00:00.000000Z",
      "updated_at": "2025-10-09T12:00:00.000000Z",
      "contacts": []
    }
  }
}
```

**Error Response (401):**
```json
{
  "message": "Unauthenticated."
}
```

---

## 2. Update Profile

Update user profile information including name, email, phone, about, and avatar URL.

**Endpoint:** `PUT /api/auth/profile`

**Headers:**
```json
{
  "Authorization": "Bearer {token}",
  "Content-Type": "application/json"
}
```

**Body (JSON):**
```json
{
  "name": "Jane Smith",                    // Optional: string, max 255 chars
  "email": "jane@example.com",             // Optional: valid email, must be unique
  "phone_number": "+9876543210",           // Optional: string, max 20 chars, must be unique
  "country_code": "+98",                   // Optional: string, max 5 chars
  "about": "Living my best life!",         // Optional: string, max 500 chars
  "avatar_url": "https://..."              // Optional: valid URL, max 500 chars
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Profile updated successfully",
  "data": {
    "user": {
      "id": 1,
      "name": "Jane Smith",
      "email": "jane@example.com",
      "phone_number": "+9876543210",
      "country_code": "+98",
      "avatar_url": "https://...",
      "about": "Living my best life!",
      "is_online": true,
      "last_seen_at": "2025-10-09T12:00:00.000000Z",
      "created_at": "2025-01-01T00:00:00.000000Z",
      "updated_at": "2025-10-09T12:05:00.000000Z"
    }
  }
}
```

**Error Response (422):**
```json
{
  "success": false,
  "message": "Validation errors",
  "errors": {
    "email": ["The email has already been taken."],
    "phone_number": ["The phone number has already been taken."]
  }
}
```

---

## 3. Update Profile (Settings)

Alternative endpoint for updating profile through settings.

**Endpoint:** `PUT /api/settings/profile`

**Headers:**
```json
{
  "Authorization": "Bearer {token}",
  "Content-Type": "application/json"
}
```

**Body (JSON):**
```json
{
  "name": "Updated Name",                  // Optional: string, max 255 chars
  "email": "updated@example.com",          // Optional: valid email, must be unique
  "about": "Updated about text"            // Optional: string, max 500 chars
}
```

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Updated Name",
    "email": "updated@example.com",
    "phone_number": "+1234567890",
    "about": "Updated about text",
    "avatar_url": "https://..."
  },
  "message": "Profile updated successfully"
}
```

---

## 4. Get Profile (Settings)

Get user profile from settings endpoint.

**Endpoint:** `GET /api/settings/profile`

**Headers:**
```json
{
  "Authorization": "Bearer {token}"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "phone_number": "+1234567890",
    "country_code": "+1",
    "avatar_url": "https://...",
    "about": "Hello, I am using this app!",
    "last_seen_at": "2025-10-09T12:00:00.000000Z",
    "is_online": true,
    "created_at": "2025-01-01T00:00:00.000000Z",
    "updated_at": "2025-10-09T12:00:00.000000Z"
  },
  "message": "Profile retrieved successfully"
}
```

---

## 5. Change Password

Change user password (can be combined with profile update).

**Endpoint:** `PUT /api/auth/profile`

**Headers:**
```json
{
  "Authorization": "Bearer {token}",
  "Content-Type": "application/json"
}
```

**Body (JSON):**
```json
{
  "current_password": "oldpassword123",           // Required: current password
  "new_password": "newpassword456",               // Required: min 8 chars
  "new_password_confirmation": "newpassword456"   // Required: must match new_password
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Profile updated successfully",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      ...
    }
  }
}
```

**Error Response (422) - Wrong Current Password:**
```json
{
  "success": false,
  "message": "Current password is incorrect"
}
```

**Error Response (422) - Validation Error:**
```json
{
  "success": false,
  "message": "Validation errors",
  "errors": {
    "new_password": [
      "The new password must be at least 8 characters.",
      "The new password confirmation does not match."
    ]
  }
}
```

---

## 6. Delete Account

Permanently delete user account (soft delete).

**Endpoint:** `DELETE /api/settings/delete-account`

**Headers:**
```json
{
  "Authorization": "Bearer {token}",
  "Content-Type": "application/json"
}
```

**Body (JSON):**
```json
{
  "password": "userpassword123",           // Required: user's current password
  "confirmation": "DELETE_MY_ACCOUNT"      // Required: exact text "DELETE_MY_ACCOUNT"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Account deleted successfully"
}
```

**Error Response (422) - Wrong Password:**
```json
{
  "success": false,
  "message": "Password is incorrect"
}
```

**Error Response (422) - Wrong Confirmation:**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "confirmation": ["The selected confirmation is invalid."]
  }
}
```

---

## 7. Export User Data

Export all user data (GDPR compliance).

**Endpoint:** `GET /api/settings/export-data`

**Headers:**
```json
{
  "Authorization": "Bearer {token}"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "profile": {
      "name": "John Doe",
      "email": "john@example.com",
      "phone_number": "+1234567890",
      "about": "Hello, I am using this app!",
      "created_at": "2025-01-01T00:00:00.000000Z"
    },
    "privacy_settings": {
      "last_seen_privacy": "everyone",
      "profile_photo_privacy": "everyone",
      "about_privacy": "everyone",
      "status_privacy": "everyone",
      "read_receipts_enabled": true
    },
    "export_generated_at": "2025-10-09T12:00:00.000000Z",
    "note": "This is a sample export. In production, this would include messages, media, and other user data."
  },
  "message": "Data export generated successfully"
}
```

---

## 8. Upload Avatar

Upload a new profile picture (avatar).

**Endpoint:** `POST /api/media/upload/avatar`

**Headers:**
```json
{
  "Authorization": "Bearer {token}",
  "Content-Type": "multipart/form-data"
}
```

**Body (FormData):**
```json
{
  "avatar": "[IMAGE_FILE]"    // Required: Image file (max 5MB)
}
```

**Success Response (201):**
```json
{
  "success": true,
  "data": {
    "id": 5,
    "public_id": "avatars/avatar_1_1728480000",
    "avatar_url": "https://res.cloudinary.com/xxx/image/upload/avatar.jpg",
    "thumbnail_url": "https://res.cloudinary.com/xxx/image/upload/c_fill,h_100,w_100/avatar.jpg",
    "small_url": "https://res.cloudinary.com/xxx/image/upload/c_fill,h_50,w_50/avatar.jpg"
  },
  "message": "Avatar uploaded successfully"
}
```

**Note:** Avatar upload automatically updates the user's `avatar_url` field.

---

## 🔄 Complete Profile Update Workflow

### Step 1: Upload Avatar
```bash
curl -X POST http://localhost:8000/api/media/upload/avatar \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "avatar=@avatar.jpg"
```

### Step 2: Update Profile with Avatar URL
```bash
curl -X PUT http://localhost:8000/api/auth/profile \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Updated Name",
    "about": "My new status",
    "avatar_url": "https://res.cloudinary.com/xxx/image/upload/avatar.jpg"
  }'
```

### Step 3: Verify Profile
```bash
curl -X GET http://localhost:8000/api/auth/user \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## 🔐 Combined Operations

### Update Profile AND Change Password
```json
{
  "name": "New Name",
  "email": "newemail@example.com",
  "about": "New about text",
  "current_password": "oldpass123",
  "new_password": "newpass456",
  "new_password_confirmation": "newpass456"
}
```

### Update Multiple Fields
```json
{
  "name": "Jane Smith",
  "email": "jane@example.com",
  "phone_number": "+9876543210",
  "country_code": "+98",
  "about": "Living my best life!"
}
```

---

## ✅ Validation Rules

| Field | Rules |
|-------|-------|
| `name` | optional, string, max:255 |
| `email` | optional, email, max:255, unique |
| `phone_number` | optional, string, max:20, unique |
| `country_code` | optional, string, max:5 |
| `about` | optional, string, max:500 |
| `avatar_url` | optional, url, max:500 |
| `current_password` | required_with:new_password, string |
| `new_password` | optional, string, min:8, confirmed |
| `password` (delete) | required, string |
| `confirmation` (delete) | required, in:DELETE_MY_ACCOUNT |

---

## 🧪 Testing with cURL

### Get Profile
```bash
curl -X GET http://localhost:8000/api/auth/user \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Update Name
```bash
curl -X PUT http://localhost:8000/api/auth/profile \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"name": "New Name"}'
```

### Update Email
```bash
curl -X PUT http://localhost:8000/api/auth/profile \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"email": "newemail@example.com"}'
```

### Change Password
```bash
curl -X PUT http://localhost:8000/api/auth/profile \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "current_password": "oldpass123",
    "new_password": "newpass456",
    "new_password_confirmation": "newpass456"
  }'
```

### Delete Account
```bash
curl -X DELETE http://localhost:8000/api/settings/delete-account \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "password": "userpassword123",
    "confirmation": "DELETE_MY_ACCOUNT"
  }'
```

---

## 📊 Response Status Codes

| Code | Meaning |
|------|---------|
| 200 | Success (GET, PUT, DELETE) |
| 201 | Created (Avatar upload) |
| 401 | Unauthenticated |
| 422 | Validation Error |
| 500 | Server Error |

---

## 🔒 Security Notes

1. ✅ All endpoints require authentication
2. ✅ Passwords are hashed using bcrypt
3. ✅ Email and phone must be unique
4. ✅ Password change requires current password
5. ✅ Account deletion requires password + confirmation
6. ✅ Sensitive fields (password, tokens) are hidden in responses
7. ✅ Avatar uploads are validated (image type, size limit)
8. ✅ Soft delete preserves data integrity

---

## 📝 Notes

- Profile updates are **partial** - only send fields you want to update
- Avatar upload automatically updates user's `avatar_url`
- Account deletion is **soft delete** - data can be recovered
- Export data endpoint provides GDPR-compliant data export
- All timestamps are in ISO 8601 format
- Phone numbers should include country code

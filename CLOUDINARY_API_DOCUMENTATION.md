# Cloudinary Media API Documentation

## Overview

All media uploads in the application now use Cloudinary for storage and delivery. This document provides complete API documentation for media operations.

## Base URL

```
Production: https://fnskills.ng/api
Development: http://localhost:8000/api
```

## Authentication

All endpoints require Bearer token authentication:

```http
Authorization: Bearer {your_access_token}
```

---

## Media Upload Endpoints

### 1. Upload User Avatar

Upload or update the authenticated user's profile picture.

**Endpoint:** `POST /api/media/upload-avatar`

**Content-Type:** `multipart/form-data`

**Request:**
```http
POST /api/media/upload-avatar
Authorization: Bearer {token}
Content-Type: multipart/form-data

avatar: [image file]
```

**Validation:**
- File is required
- Must be an image (jpeg, jpg, png, gif)
- Maximum size: 5MB

**Response (Success - 201):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "public_id": "avatars/avatar_5_1234567890",
    "avatar_url": "https://res.cloudinary.com/dd5ckivdo/image/upload/avatars/avatar_5_1234567890.jpg",
    "thumbnail_url": "https://res.cloudinary.com/dd5ckivdo/image/upload/c_fill,h_100,w_100/avatars/avatar_5_1234567890.jpg",
    "small_url": "https://res.cloudinary.com/dd5ckivdo/image/upload/c_fill,h_50,w_50/avatars/avatar_5_1234567890.jpg"
  },
  "message": "Avatar uploaded successfully"
}
```

**Response (Error - 422):**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "avatar": ["The avatar must be an image."]
  }
}
```

**Flutter Example:**
```dart
Future<Map<String, dynamic>> uploadAvatar(File imageFile) async {
  var request = http.MultipartRequest(
    'POST',
    Uri.parse('$baseUrl/api/media/upload-avatar'),
  );
  
  request.headers['Authorization'] = 'Bearer $token';
  request.files.add(await http.MultipartFile.fromPath(
    'avatar',
    imageFile.path,
  ));
  
  var response = await request.send();
  var responseData = await response.stream.bytesToString();
  return json.decode(responseData);
}
```

---

### 2. Upload Status Media

Upload media for a status/story (image or video).

**Endpoint:** `POST /api/media/upload-status-media`

**Content-Type:** `multipart/form-data`

**Request:**
```http
POST /api/media/upload-status-media
Authorization: Bearer {token}
Content-Type: multipart/form-data

media: [file]
type: image|video
```

**Validation:**
- `media`: Required file, max 50MB
- `type`: Required, must be 'image' or 'video'

**Response (Success - 201):**
```json
{
  "success": true,
  "data": {
    "public_id": "status/status_1234567890",
    "url": "https://res.cloudinary.com/dd5ckivdo/image/upload/status/status_1234567890.jpg",
    "thumbnail_url": "https://res.cloudinary.com/dd5ckivdo/image/upload/c_fill,h_200,w_200/status/status_1234567890.jpg",
    "type": "image",
    "format": "jpg",
    "size": 102400,
    "size_formatted": "100 KB"
  },
  "message": "Status media uploaded successfully"
}
```

**Flutter Example:**
```dart
Future<Map<String, dynamic>> uploadStatusMedia(File file, String type) async {
  var request = http.MultipartRequest(
    'POST',
    Uri.parse('$baseUrl/api/media/upload-status-media'),
  );
  
  request.headers['Authorization'] = 'Bearer $token';
  request.fields['type'] = type; // 'image' or 'video'
  request.files.add(await http.MultipartFile.fromPath(
    'media',
    file.path,
  ));
  
  var response = await request.send();
  var responseData = await response.stream.bytesToString();
  return json.decode(responseData);
}
```

---

### 3. Upload Chat Avatar

Upload an avatar for a group chat.

**Endpoint:** `POST /api/media/upload-chat-avatar`

**Content-Type:** `multipart/form-data`

**Request:**
```http
POST /api/media/upload-chat-avatar
Authorization: Bearer {token}
Content-Type: multipart/form-data

chat_avatar: [image file]
chat_id: 123
```

**Validation:**
- `chat_avatar`: Required image file, max 5MB
- `chat_id`: Required, must exist in chats table

**Response (Success - 201):**
```json
{
  "success": true,
  "data": {
    "public_id": "chat-avatars/chat_123_1234567890",
    "avatar_url": "https://res.cloudinary.com/dd5ckivdo/image/upload/chat-avatars/chat_123_1234567890.jpg",
    "thumbnail_url": "https://res.cloudinary.com/dd5ckivdo/image/upload/c_fill,h_200,w_200/chat-avatars/chat_123_1234567890.jpg"
  },
  "message": "Chat avatar uploaded successfully"
}
```

---

### 4. Upload Message Media

Upload media for a message (image, video, audio, document).

**Endpoint:** `POST /api/media/upload`

**Content-Type:** `multipart/form-data`

**Request:**
```http
POST /api/media/upload
Authorization: Bearer {token}
Content-Type: multipart/form-data

file: [file]
type: image|video|audio|document|voice
chat_id: 123 (optional)
```

**Validation:**
- `file`: Required, max 100MB
- `type`: Required, must be one of: image, video, audio, document, voice
- `chat_id`: Optional, must exist in chats table

**Response (Success - 201):**
```json
{
  "success": true,
  "data": {
    "id": 456,
    "public_id": "media/images/image_1234567890",
    "url": "https://res.cloudinary.com/dd5ckivdo/image/upload/media/images/image_1234567890.jpg",
    "thumbnail_url": "https://res.cloudinary.com/dd5ckivdo/image/upload/c_fill,h_200,w_200/media/images/image_1234567890.jpg",
    "type": "image",
    "format": "jpg",
    "resource_type": "image",
    "size": 204800,
    "size_formatted": "200 KB",
    "width": 1920,
    "height": 1080,
    "uploaded_by": 5,
    "uploaded_at": "2026-01-22T10:30:00.000000Z",
    "chat_id": 123
  },
  "message": "File uploaded successfully"
}
```

**Flutter Example:**
```dart
Future<Map<String, dynamic>> uploadMessageMedia(
  File file,
  String type,
  int? chatId,
) async {
  var request = http.MultipartRequest(
    'POST',
    Uri.parse('$baseUrl/api/media/upload'),
  );
  
  request.headers['Authorization'] = 'Bearer $token';
  request.fields['type'] = type;
  if (chatId != null) {
    request.fields['chat_id'] = chatId.toString();
  }
  
  request.files.add(await http.MultipartFile.fromPath(
    'file',
    file.path,
  ));
  
  var response = await request.send();
  var responseData = await response.stream.bytesToString();
  return json.decode(responseData);
}
```

---

## Media Retrieval Endpoints

### 5. Get User Media

Get all media files uploaded by the authenticated user.

**Endpoint:** `GET /api/media/user`

**Query Parameters:**
- `type` (optional): Filter by type (image, video, audio, document)
- `limit` (optional): Number of results (default: 50)

**Request:**
```http
GET /api/media/user?type=image&limit=20
Authorization: Bearer {token}
```

**Response (Success - 200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "public_id": "media/images/image_123",
      "url": "https://res.cloudinary.com/dd5ckivdo/image/upload/media/images/image_123.jpg",
      "thumbnail_url": "https://res.cloudinary.com/dd5ckivdo/image/upload/c_fill,h_200,w_200/media/images/image_123.jpg",
      "type": "image",
      "format": "jpg",
      "size": 102400,
      "size_formatted": "100 KB",
      "created_at": "2026-01-22T10:00:00.000000Z"
    }
  ],
  "count": 1
}
```

---

### 6. Get Chat Media

Get all media files for a specific chat.

**Endpoint:** `GET /api/media/chat/{chat_id}`

**Query Parameters:**
- `type` (optional): Filter by type
- `limit` (optional): Number of results (default: 50)

**Request:**
```http
GET /api/media/chat/123?type=image
Authorization: Bearer {token}
```

**Response:** Same format as Get User Media

---

### 7. Get Media by ID

Get a specific media file by ID.

**Endpoint:** `GET /api/media/{id}`

**Request:**
```http
GET /api/media/456
Authorization: Bearer {token}
```

**Response (Success - 200):**
```json
{
  "success": true,
  "data": {
    "id": 456,
    "public_id": "media/images/image_123",
    "url": "https://res.cloudinary.com/dd5ckivdo/image/upload/media/images/image_123.jpg",
    "thumbnail_url": "https://res.cloudinary.com/dd5ckivdo/image/upload/c_fill,h_200,w_200/media/images/image_123.jpg",
    "type": "image",
    "format": "jpg",
    "size": 102400,
    "created_at": "2026-01-22T10:00:00.000000Z"
  }
}
```

---

### 8. Get Media Statistics

Get media usage statistics for the authenticated user.

**Endpoint:** `GET /api/media/stats`

**Request:**
```http
GET /api/media/stats
Authorization: Bearer {token}
```

**Response (Success - 200):**
```json
{
  "success": true,
  "data": {
    "total_files": 150,
    "total_size": 52428800,
    "total_size_formatted": "50 MB",
    "by_type": {
      "images": 100,
      "videos": 30,
      "audios": 15,
      "documents": 5
    },
    "recent_uploads": [
      {
        "id": 456,
        "type": "image",
        "url": "https://res.cloudinary.com/dd5ckivdo/image/upload/media/images/image_123.jpg",
        "created_at": "2026-01-22T10:00:00.000000Z"
      }
    ]
  }
}
```

---

## Media Deletion Endpoint

### 9. Delete Media

Delete a media file from Cloudinary.

**Endpoint:** `DELETE /api/media/delete`

**Content-Type:** `application/json`

**Request:**
```http
DELETE /api/media/delete
Authorization: Bearer {token}
Content-Type: application/json

{
  "public_id": "media/images/image_123",
  "resource_type": "image"
}
```

**Request Body:**
- `public_id`: Required, Cloudinary public ID
- `resource_type`: Optional, one of: image, video, raw (default: image)

**Response (Success - 200):**
```json
{
  "success": true,
  "message": "File deleted successfully"
}
```

**Response (Error - 403):**
```json
{
  "success": false,
  "message": "Unauthorized to delete this file"
}
```

**Flutter Example:**
```dart
Future<Map<String, dynamic>> deleteMedia(String publicId, String resourceType) async {
  final response = await http.delete(
    Uri.parse('$baseUrl/api/media/delete'),
    headers: {
      'Authorization': 'Bearer $token',
      'Content-Type': 'application/json',
    },
    body: json.encode({
      'public_id': publicId,
      'resource_type': resourceType,
    }),
  );
  
  return json.decode(response.body);
}
```

---

## Image Transformations

Cloudinary provides automatic image transformations. You can modify URLs to get different sizes:

### Avatar Sizes

```
Full: https://res.cloudinary.com/dd5ckivdo/image/upload/avatars/avatar_5.jpg
Thumbnail (100x100): https://res.cloudinary.com/dd5ckivdo/image/upload/c_fill,h_100,w_100/avatars/avatar_5.jpg
Small (50x50): https://res.cloudinary.com/dd5ckivdo/image/upload/c_fill,h_50,w_50/avatars/avatar_5.jpg
```

### Custom Transformations

You can add transformations to any Cloudinary URL:

```
// Resize to 300x300
https://res.cloudinary.com/dd5ckivdo/image/upload/c_fill,h_300,w_300/path/to/image.jpg

// Resize with quality
https://res.cloudinary.com/dd5ckivdo/image/upload/c_fill,h_300,w_300,q_80/path/to/image.jpg

// Convert to WebP
https://res.cloudinary.com/dd5ckivdo/image/upload/f_webp/path/to/image.jpg

// Blur
https://res.cloudinary.com/dd5ckivdo/image/upload/e_blur:300/path/to/image.jpg
```

---

## Error Codes

| Code | Description |
|------|-------------|
| 200 | Success |
| 201 | Created successfully |
| 400 | Bad request |
| 401 | Unauthorized (invalid/missing token) |
| 403 | Forbidden (no permission) |
| 404 | Resource not found |
| 422 | Validation error |
| 500 | Server error |

---

## Rate Limiting

- API calls are rate-limited to prevent abuse
- Default: 60 requests per minute per user
- Exceeded limit returns 429 Too Many Requests

---

## Best Practices

### 1. Image Optimization

- Upload images in appropriate sizes (don't upload 4K images for avatars)
- Use Cloudinary transformations to get the right size
- Leverage automatic format conversion (WebP, AVIF)

### 2. Caching

- Cache Cloudinary URLs in your app
- Use `CachedNetworkImage` in Flutter for better performance
- Cloudinary URLs are CDN-backed and highly cacheable

### 3. Error Handling

Always handle errors gracefully:

```dart
try {
  final result = await uploadAvatar(imageFile);
  if (result['success']) {
    // Handle success
  } else {
    // Handle API error
    showError(result['message']);
  }
} catch (e) {
  // Handle network error
  showError('Network error: $e');
}
```

### 4. Progress Tracking

For large files, track upload progress:

```dart
var request = http.MultipartRequest('POST', uri);
request.files.add(await http.MultipartFile.fromPath('file', file.path));

var streamedResponse = await request.send();
var totalBytes = streamedResponse.contentLength ?? 0;
var receivedBytes = 0;

streamedResponse.stream.listen(
  (chunk) {
    receivedBytes += chunk.length;
    var progress = receivedBytes / totalBytes;
    // Update UI with progress
  },
);
```

---

## Testing

### Test Credentials

Use these test endpoints in development:

```
Base URL: http://localhost:8000/api
Test Token: Use the token from login endpoint
```

### Sample Test Flow

1. **Login** to get access token
2. **Upload avatar** using the token
3. **Verify** the avatar URL is returned
4. **Display** the image in your app
5. **Delete** the image if needed

---

## Support

For issues or questions:
- Check the error message in the response
- Review Laravel logs: `storage/logs/laravel.log`
- Verify Cloudinary credentials in `.env`
- Check Cloudinary dashboard for upload status

---

## Changelog

### Version 2.0 (Current)
- ✅ All uploads now use Cloudinary
- ✅ Fully-qualified URLs in all responses
- ✅ Automatic thumbnail generation
- ✅ CDN delivery for all images
- ✅ No local storage dependency

### Version 1.0 (Legacy)
- ❌ Used local storage
- ❌ Relative paths in responses
- ❌ Manual thumbnail generation
- ❌ No CDN delivery

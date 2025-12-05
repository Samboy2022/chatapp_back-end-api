# 📁 Media & File Upload API - CORRECT ENDPOINTS

**Base URL:** `http://your-domain.com/api`

All endpoints require authentication via Bearer token in the Authorization header.

---

## 1. Upload General Media File

**Endpoint:** `POST /api/media/upload`

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
  "file": "[FILE]",           // Required: The file to upload
  "type": "image",            // Required: image|video|audio|document|voice
  "chat_id": 123              // Optional: Associate with a chat
}
```

**Success Response (201):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "public_id": "media/images/abc123",
    "url": "https://res.cloudinary.com/xxx/image/upload/xxx.jpg",
    "thumbnail_url": "https://res.cloudinary.com/xxx/image/upload/c_fill,h_200,w_200/xxx.jpg",
    "type": "image",
    "format": "jpg",
    "resource_type": "image",
    "size": 102400,
    "size_formatted": "100 KB",
    "width": 1920,
    "height": 1080,
    "uploaded_by": 1,
    "uploaded_at": "2025-10-09T12:00:00.000000Z",
    "chat_id": 123
  },
  "message": "File uploaded successfully"
}
```

**Error Response (422):**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "file": ["The file field is required."],
    "type": ["The type field is required."]
  }
}
```

---

## 2. Upload Avatar Image

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
    "id": 2,
    "public_id": "avatars/avatar_1_1728480000",
    "avatar_url": "https://res.cloudinary.com/xxx/image/upload/avatar.jpg",
    "thumbnail_url": "https://res.cloudinary.com/xxx/image/upload/c_fill,h_100,w_100/avatar.jpg",
    "small_url": "https://res.cloudinary.com/xxx/image/upload/c_fill,h_50,w_50/avatar.jpg"
  },
  "message": "Avatar uploaded successfully"
}
```

**Error Response (422):**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "avatar": ["The avatar field is required.", "The avatar must be an image."]
  }
}
```

---

## 3. Upload Chat Avatar

**Endpoint:** `POST /api/media/upload/chat-avatar`

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
  "chat_avatar": "[IMAGE_FILE]",  // Required: Image file (max 5MB)
  "chat_id": 123                  // Required: Chat ID
}
```

**Success Response (201):**
```json
{
  "success": true,
  "data": {
    "public_id": "chat-avatars/chat_123_1728480000",
    "avatar_url": "https://res.cloudinary.com/xxx/image/upload/chat-avatar.jpg",
    "thumbnail_url": "https://res.cloudinary.com/xxx/image/upload/c_fill,h_200,w_200/chat-avatar.jpg"
  },
  "message": "Chat avatar uploaded successfully"
}
```

**Error Response (422):**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "chat_avatar": ["The chat avatar field is required."],
    "chat_id": ["The chat id field is required.", "The selected chat id is invalid."]
  }
}
```

---

## 4. Upload Status Media

**Endpoint:** `POST /api/media/upload/status`

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
  "media": "[FILE]",          // Required: Image or video file (max 50MB)
  "type": "image"             // Required: image|video
}
```

**Success Response (201):**
```json
{
  "success": true,
  "data": {
    "public_id": "status/status_1728480000",
    "url": "https://res.cloudinary.com/xxx/image/upload/status.jpg",
    "thumbnail_url": "https://res.cloudinary.com/xxx/image/upload/c_fill,h_200,w_200/status.jpg",
    "type": "image",
    "format": "jpg",
    "size": 204800,
    "size_formatted": "200 KB"
  },
  "message": "Status media uploaded successfully"
}
```

**Error Response (422):**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "media": ["The media field is required."],
    "type": ["The type field is required.", "The selected type is invalid."]
  }
}
```

---

## 5. Delete Uploaded Media

**Endpoint:** `DELETE /api/media/delete`

**Headers:**
```json
{
  "Authorization": "Bearer {token}",
  "Content-Type": "application/json"
}
```

**Body (JSON):**

**Option 1 - Using public_id:**
```json
{
  "public_id": "media/images/abc123",     // Required (if file_path not provided)
  "resource_type": "image"                // Optional: image|video|raw (default: image)
}
```

**Option 2 - Using file_path (backward compatible):**
```json
{
  "file_path": "/storage/avatars/avatar.jpg",  // Required (if public_id not provided)
  "resource_type": "image"                     // Optional: image|video|raw (default: image)
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "File deleted successfully"
}
```

**Error Response (403):**
```json
{
  "success": false,
  "message": "Unauthorized to delete this file"
}
```

**Error Response (422):**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "public_id": ["The public id field is required when file path is not present."],
    "file_path": ["The file path field is required when public id is not present."]
  }
}
```

---

## Additional Media Endpoints

### 6. Get User's Media Files

**Endpoint:** `GET /api/media/user`

**Query Parameters:**
- `type` (optional): Filter by type (image, video, audio, document)
- `limit` (optional): Number of results (default: 50)

**Success Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "public_id": "media/images/abc123",
      "url": "https://res.cloudinary.com/xxx/image/upload/xxx.jpg",
      "type": "image",
      "size_formatted": "100 KB",
      "created_at": "2025-10-09T12:00:00.000000Z"
    }
  ],
  "count": 1
}
```

---

### 7. Get Chat Media Files

**Endpoint:** `GET /api/media/chat/{chatId}`

**Query Parameters:**
- `type` (optional): Filter by type
- `limit` (optional): Number of results (default: 50)

**Success Response (200):**
```json
{
  "success": true,
  "data": [...],
  "count": 5
}
```

---

### 8. Get Media Statistics

**Endpoint:** `GET /api/media/stats`

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "total_files": 25,
    "total_size": 52428800,
    "total_size_formatted": "50 MB",
    "by_type": {
      "images": 15,
      "videos": 5,
      "audios": 3,
      "documents": 2
    },
    "recent_uploads": [...]
  }
}
```

---

### 9. Get Media File by ID

**Endpoint:** `GET /api/media/{id}`

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "public_id": "media/images/abc123",
    "url": "https://res.cloudinary.com/xxx/image/upload/xxx.jpg",
    "thumbnail_url": "https://res.cloudinary.com/xxx/image/upload/c_fill,h_200,w_200/xxx.jpg",
    "type": "image",
    "format": "jpg",
    "size": 102400,
    "size_formatted": "100 KB",
    "width": 1920,
    "height": 1080,
    "created_at": "2025-10-09T12:00:00.000000Z"
  }
}
```

---

## Key Differences from Your Original Endpoints:

### ❌ WRONG (Your Original):
```
POST /media/upload/avatar
Body: FormData with 'file' field

POST /media/upload/chat-avatar
Body: FormData with 'chat_avatar' field  ❌ (was 'avatar')

POST /media/upload/status
Body: FormData with 'media' field  ❌ (was 'file')

DELETE /media/delete
Body: {'file_path': '/storage/avatars/avatar.jpg'}  ❌ (now supports both)
```

### ✅ CORRECT (Fixed Implementation):
```
POST /api/media/upload/avatar
Body: FormData with 'avatar' field ✅

POST /api/media/upload/chat-avatar
Body: FormData with 'chat_avatar' field ✅

POST /api/media/upload/status
Body: FormData with 'media' field ✅

DELETE /api/media/delete
Body: {'public_id': 'media/images/abc123'} ✅
OR
Body: {'file_path': '/storage/avatars/avatar.jpg'} ✅ (backward compatible)
```

---

## Validation Rules Summary:

| Endpoint | Field | Rules |
|----------|-------|-------|
| `/upload` | `file` | required, file, max:100MB |
| `/upload` | `type` | required, in:image,video,audio,document,voice |
| `/upload/avatar` | `avatar` | required, image, max:5MB |
| `/upload/chat-avatar` | `chat_avatar` | required, image, max:5MB |
| `/upload/chat-avatar` | `chat_id` | required, exists:chats,id |
| `/upload/status` | `media` | required, file, max:50MB |
| `/upload/status` | `type` | required, in:image,video |
| `/delete` | `public_id` | required_without:file_path |
| `/delete` | `file_path` | required_without:public_id |

---

## Testing with cURL:

### Upload Avatar:
```bash
curl -X POST http://your-domain.com/api/media/upload/avatar \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "avatar=@/path/to/avatar.jpg"
```

### Upload Chat Avatar:
```bash
curl -X POST http://your-domain.com/api/media/upload/chat-avatar \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "chat_avatar=@/path/to/chat-avatar.jpg" \
  -F "chat_id=123"
```

### Upload Status Media:
```bash
curl -X POST http://your-domain.com/api/media/upload/status \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "media=@/path/to/status.jpg" \
  -F "type=image"
```

### Delete Media:
```bash
curl -X DELETE http://your-domain.com/api/media/delete \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"public_id": "media/images/abc123", "resource_type": "image"}'
```

---

## Notes:

1. ✅ All endpoints are **authenticated** - require Bearer token
2. ✅ File uploads use **multipart/form-data**
3. ✅ Delete endpoint uses **application/json**
4. ✅ All successful uploads return **201 Created**
5. ✅ Validation errors return **422 Unprocessable Entity**
6. ✅ Unauthorized access returns **401 Unauthorized**
7. ✅ Forbidden actions return **403 Forbidden**
8. ✅ Files are stored in **Cloudinary** (not local storage)
9. ✅ Database tracks all uploaded files in `media_files` table
10. ✅ Users can only delete their own files

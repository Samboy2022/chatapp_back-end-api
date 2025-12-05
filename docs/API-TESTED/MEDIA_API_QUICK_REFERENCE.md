# 📋 Media Upload API - Quick Reference

## ⚠️ IMPORTANT: Field Name Corrections

### What Changed:

| Endpoint | ❌ OLD Field Name | ✅ CORRECT Field Name |
|----------|------------------|----------------------|
| Upload Avatar | `file` | **`avatar`** |
| Upload Chat Avatar | `avatar` | **`chat_avatar`** |
| Upload Status | `file` | **`media`** |
| Delete Media | Only `file_path` | **`public_id`** or `file_path` |

---

## 🚀 Quick Test Commands

### 1. Upload Avatar
```bash
curl -X POST http://localhost:8000/api/media/upload/avatar \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "avatar=@avatar.jpg"
```

### 2. Upload Chat Avatar
```bash
curl -X POST http://localhost:8000/api/media/upload/chat-avatar \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "chat_avatar=@chat-avatar.jpg" \
  -F "chat_id=1"
```

### 3. Upload Status Media
```bash
curl -X POST http://localhost:8000/api/media/upload/status \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "media=@status.jpg" \
  -F "type=image"
```

### 4. Upload General Media
```bash
curl -X POST http://localhost:8000/api/media/upload \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "file=@document.pdf" \
  -F "type=document"
```

### 5. Delete Media
```bash
curl -X DELETE http://localhost:8000/api/media/delete \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"public_id":"media/images/abc123","resource_type":"image"}'
```

---

## 📝 All Endpoints Summary

| Method | Endpoint | Field Name | Required Fields |
|--------|----------|------------|-----------------|
| POST | `/api/media/upload` | `file`, `type` | file, type |
| POST | `/api/media/upload/avatar` | `avatar` | avatar |
| POST | `/api/media/upload/chat-avatar` | `chat_avatar`, `chat_id` | chat_avatar, chat_id |
| POST | `/api/media/upload/status` | `media`, `type` | media, type |
| DELETE | `/api/media/delete` | `public_id` OR `file_path` | public_id OR file_path |
| GET | `/api/media/user` | - | - |
| GET | `/api/media/chat/{chatId}` | - | - |
| GET | `/api/media/stats` | - | - |
| GET | `/api/media/{id}` | - | - |

---

## 🎯 Validation Rules

### Upload General Media (`/api/media/upload`)
- `file`: required, file, max:100MB
- `type`: required, in:image,video,audio,document,voice
- `chat_id`: optional, exists:chats,id

### Upload Avatar (`/api/media/upload/avatar`)
- `avatar`: required, image, max:5MB

### Upload Chat Avatar (`/api/media/upload/chat-avatar`)
- `chat_avatar`: required, image, max:5MB
- `chat_id`: required, exists:chats,id

### Upload Status Media (`/api/media/upload/status`)
- `media`: required, file, max:50MB
- `type`: required, in:image,video

### Delete Media (`/api/media/delete`)
- `public_id`: required_without:file_path, string
- `file_path`: required_without:public_id, string
- `resource_type`: optional, in:image,video,raw

---

## 📦 Response Structures

### Upload Success (201)
```json
{
  "success": true,
  "data": {
    "id": 1,
    "public_id": "media/images/abc123",
    "url": "https://...",
    "thumbnail_url": "https://...",
    "type": "image",
    "format": "jpg",
    "size": 102400,
    "size_formatted": "100 KB"
  },
  "message": "File uploaded successfully"
}
```

### Validation Error (422)
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "field_name": ["Error message"]
  }
}
```

### Delete Success (200)
```json
{
  "success": true,
  "message": "File deleted successfully"
}
```

### Unauthorized (401)
```json
{
  "message": "Unauthenticated."
}
```

### Forbidden (403)
```json
{
  "success": false,
  "message": "Unauthorized to delete this file"
}
```

---

## 🧪 Testing Checklist

- [ ] Upload avatar with correct field name `avatar`
- [ ] Upload chat avatar with correct field name `chat_avatar`
- [ ] Upload status with correct field name `media`
- [ ] Upload general media with field name `file`
- [ ] Delete media using `public_id`
- [ ] Delete media using `file_path` (backward compatible)
- [ ] Test validation errors for missing fields
- [ ] Test authentication requirement
- [ ] Test file size limits
- [ ] Test file type validation
- [ ] Test unauthorized delete attempt
- [ ] Get user media list
- [ ] Get chat media list
- [ ] Get media statistics
- [ ] Get media by ID

---

## 🔧 Files Modified

1. **Controller**: `app/Http/Controllers/Api/MediaController.php`
   - Fixed `uploadChatAvatar()` to accept `chat_avatar` field
   - Fixed `uploadStatusMedia()` to accept `media` field
   - Enhanced `delete()` to support both `public_id` and `file_path`
   - Added authorization check for delete

2. **Tests**: `tests/Feature/MediaUploadApiTest.php`
   - Comprehensive test suite with 25+ test cases
   - Tests all endpoints with correct field names
   - Tests validation rules
   - Tests authorization

3. **Factory**: `database/factories/MediaFileFactory.php`
   - Created factory for testing
   - Supports different media types
   - Includes helper methods

4. **Unit Tests**: `tests/Unit/CloudinaryServiceTest.php`
   - Tests CloudinaryService methods
   - Validates response structures

---

## 📚 Documentation Files

1. `MEDIA_UPLOAD_CORRECT_ENDPOINTS.md` - Complete API documentation
2. `MEDIA_UPLOAD_POSTMAN_COLLECTION.json` - Postman collection for testing
3. `MEDIA_API_QUICK_REFERENCE.md` - This quick reference guide

---

## ✅ What's Fixed

1. ✅ Avatar upload now uses `avatar` field (not `file`)
2. ✅ Chat avatar upload now uses `chat_avatar` field (not `avatar`)
3. ✅ Status upload now uses `media` field (not `file`)
4. ✅ Delete endpoint supports both `public_id` and `file_path`
5. ✅ Delete endpoint checks user authorization
6. ✅ All endpoints properly validated
7. ✅ Comprehensive test coverage
8. ✅ Complete documentation

---

## 🎉 Ready to Test!

Run the tests:
```bash
php artisan test --filter=MediaUploadApiTest
```

Or test manually using the Postman collection or cURL commands above.

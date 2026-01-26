# Cloudinary Image Upload Test Documentation

## Overview

This document provides comprehensive information about testing image uploads to Cloudinary in the chat application.

## Test Files

### 1. PHPUnit Feature Test
**Location:** `tests/Feature/CloudinaryImageUploadTest.php`

Automated test suite using Laravel's testing framework with database transactions.

### 2. Standalone PHP Test Script
**Location:** `docs/API-TESTED/test_cloudinary_image_upload.php`

Manual test script that can be run independently to verify Cloudinary integration.

---

## Prerequisites

### Environment Configuration

Add the following to your `.env` file:

```env
CLOUDINARY_CLOUD_NAME=your_cloud_name
CLOUDINARY_API_KEY=your_api_key
CLOUDINARY_API_SECRET=your_api_secret
```

### Required PHP Extensions
- GD Library (for image creation in tests)
- cURL (for API requests)
- JSON

---

## Running the Tests

### Option 1: PHPUnit Test Suite

```bash
# Run all Cloudinary tests
php artisan test --filter CloudinaryImageUploadTest

# Run specific test
php artisan test --filter test_can_upload_image_to_cloudinary_via_api

# Run with verbose output
php artisan test --filter CloudinaryImageUploadTest --verbose
```

### Option 2: Standalone PHP Script

```bash
# Navigate to the test directory
cd docs/API-TESTED

# Run the test script
php test_cloudinary_image_upload.php
```

**Configuration:**
Edit the script to update:
- `$BASE_URL` - Your API base URL
- `$TEST_EMAIL` - Test user email
- `$TEST_PASSWORD` - Test user password

---

## Test Coverage

### 1. Basic Image Upload
**Endpoint:** `POST /api/media/upload`

**Test:** Uploads a standard image to Cloudinary

**Validates:**
- ✓ HTTP 201 status code
- ✓ Success response structure
- ✓ Cloudinary URL in response
- ✓ Public ID generation
- ✓ Thumbnail URL generation
- ✓ Database record creation
- ✓ Metadata (width, height, size, format)

**Example Request:**
```bash
curl -X POST http://localhost:8000/api/media/upload \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "file=@image.jpg" \
  -F "type=image"
```

**Expected Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "public_id": "media/images/abc123",
    "url": "https://res.cloudinary.com/your-cloud/image/upload/v123/media/images/abc123.jpg",
    "thumbnail_url": "https://res.cloudinary.com/your-cloud/image/upload/c_fill,h_200,w_200/v123/media/images/abc123.jpg",
    "type": "image",
    "format": "jpg",
    "resource_type": "image",
    "size": 102400,
    "size_formatted": "100 KB",
    "width": 800,
    "height": 600,
    "uploaded_by": 1,
    "uploaded_at": "2025-01-22T10:30:00.000000Z"
  },
  "message": "File uploaded successfully"
}
```

---

### 2. Avatar Upload
**Endpoint:** `POST /api/media/upload-avatar`

**Test:** Uploads user avatar with multiple size variants

**Validates:**
- ✓ Multiple size URLs (avatar, thumbnail, small)
- ✓ User avatar_url field update
- ✓ Proper folder structure (avatars/)
- ✓ Public ID format: `avatar_{userId}_{timestamp}`

**Example Request:**
```bash
curl -X POST http://localhost:8000/api/media/upload-avatar \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "avatar=@avatar.jpg"
```

**Expected Response:**
```json
{
  "success": true,
  "data": {
    "id": 2,
    "public_id": "avatars/avatar_1_1737545400",
    "avatar_url": "https://res.cloudinary.com/.../avatars/avatar_1_1737545400.jpg",
    "thumbnail_url": "https://res.cloudinary.com/.../c_fill,h_100,w_100/avatars/avatar_1_1737545400.jpg",
    "small_url": "https://res.cloudinary.com/.../c_fill,h_50,w_50/avatars/avatar_1_1737545400.jpg"
  },
  "message": "Avatar uploaded successfully"
}
```

---

### 3. Status Media Upload
**Endpoint:** `POST /api/media/upload-status-media`

**Test:** Uploads media for status/stories feature

**Validates:**
- ✓ Support for image and video types
- ✓ Thumbnail generation for images
- ✓ Proper folder structure (status/)
- ✓ Size formatting

**Example Request:**
```bash
curl -X POST http://localhost:8000/api/media/upload-status-media \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "media=@status.jpg" \
  -F "type=image"
```

---

### 4. Multiple Format Support
**Test:** Uploads images in different formats

**Supported Formats:**
- ✓ JPEG (.jpg, .jpeg)
- ✓ PNG (.png)
- ✓ GIF (.gif)
- ✓ WebP (.webp)

**Validates:**
- ✓ Format detection
- ✓ Proper MIME type handling
- ✓ Format preservation or conversion

---

### 5. Validation Tests

#### Missing File
```bash
curl -X POST http://localhost:8000/api/media/upload \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"type": "image"}'
```

**Expected:** HTTP 422 with validation error

#### Missing Type
```bash
curl -X POST http://localhost:8000/api/media/upload \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "file=@image.jpg"
```

**Expected:** HTTP 422 with validation error

#### Invalid File Type
```bash
curl -X POST http://localhost:8000/api/media/upload-avatar \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "avatar=@document.pdf"
```

**Expected:** HTTP 422 with validation error

#### File Size Limit
```bash
# Avatar limit: 5MB
# General media limit: 100MB
```

**Expected:** HTTP 422 when exceeded

---

### 6. Authentication Tests

**Test:** Attempts upload without authentication

```bash
curl -X POST http://localhost:8000/api/media/upload \
  -F "file=@image.jpg" \
  -F "type=image"
```

**Expected:** HTTP 401 Unauthorized

---

### 7. Media Retrieval

#### Get User Media
**Endpoint:** `GET /api/media/user`

```bash
curl -X GET http://localhost:8000/api/media/user \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Query Parameters:**
- `type` - Filter by type (image, video, audio, document)
- `limit` - Limit results (default: 50)

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "public_id": "media/images/abc123",
      "url": "https://...",
      "type": "image",
      "created_at": "2025-01-22T10:30:00.000000Z"
    }
  ],
  "count": 1
}
```

#### Get Media Statistics
**Endpoint:** `GET /api/media/stats`

```bash
curl -X GET http://localhost:8000/api/media/stats \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Response:**
```json
{
  "success": true,
  "data": {
    "total_files": 10,
    "total_size": 5242880,
    "total_size_formatted": "5 MB",
    "by_type": {
      "images": 7,
      "videos": 2,
      "audios": 1,
      "documents": 0
    },
    "recent_uploads": [...]
  }
}
```

---

### 8. Media Deletion

**Endpoint:** `POST /api/media/delete`

**Test:** Deletes media from Cloudinary and database

```bash
curl -X POST http://localhost:8000/api/media/delete \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "public_id": "media/images/abc123",
    "resource_type": "image"
  }'
```

**Validates:**
- ✓ Deletion from Cloudinary
- ✓ Database record removal
- ✓ Authorization check (user owns file)
- ✓ Proper error handling

**Authorization Test:**
- ✓ User cannot delete other users' files (HTTP 403)

---

## Test Data

### Image Specifications

**Standard Image:**
- Dimensions: 800x600
- Format: JPEG
- Quality: 90%

**Avatar:**
- Dimensions: 500x500
- Format: JPEG
- Square aspect ratio

**Status Media:**
- Dimensions: 1080x1920 (9:16 ratio)
- Format: JPEG
- Vertical orientation

---

## Cloudinary Integration Details

### Upload Configuration

**Default Options:**
```php
[
    'folder' => 'media',
    'resource_type' => 'auto',
    'use_filename' => true,
    'unique_filename' => true,
]
```

### Folder Structure
```
cloudinary-root/
├── media/
│   ├── images/
│   ├── videos/
│   ├── audios/
│   └── documents/
├── avatars/
├── status/
├── chat-avatars/
└── logos/
```

### Transformation Examples

**Thumbnail (200x200):**
```
c_fill,h_200,w_200
```

**Avatar Thumbnail (100x100):**
```
c_fill,h_100,w_100
```

**Small Avatar (50x50):**
```
c_fill,h_50,w_50
```

---

## Error Handling

### Common Errors

**1. Missing Credentials**
```json
{
  "success": false,
  "message": "Upload failed",
  "error": "Cloudinary credentials not configured"
}
```

**2. Upload Failed**
```json
{
  "success": false,
  "message": "Upload failed",
  "error": "Invalid image file"
}
```

**3. Unauthorized Deletion**
```json
{
  "success": false,
  "message": "Unauthorized to delete this file"
}
```

**4. File Not Found**
```json
{
  "success": false,
  "message": "Media file not found",
  "error": "No query results for model [App\\Models\\MediaFile]"
}
```

---

## Performance Considerations

### File Size Limits
- **Avatar:** 5MB max
- **Status Media:** 50MB max
- **General Media:** 100MB max

### Optimization
- Automatic format optimization by Cloudinary
- Thumbnail generation for images
- Progressive JPEG encoding
- WebP format support

---

## Database Schema

### media_files Table
```sql
CREATE TABLE media_files (
    id BIGINT PRIMARY KEY,
    user_id BIGINT NOT NULL,
    public_id VARCHAR(255) NOT NULL,
    url TEXT NOT NULL,
    thumbnail_url TEXT,
    type VARCHAR(50) NOT NULL,
    format VARCHAR(50),
    resource_type VARCHAR(50),
    size BIGINT,
    size_formatted VARCHAR(50),
    width INT,
    height INT,
    folder VARCHAR(255),
    chat_id BIGINT,
    usage_type VARCHAR(50),
    metadata JSON,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

---

## Troubleshooting

### Tests Skipped
**Issue:** Tests are skipped with message "Cloudinary credentials not configured"

**Solution:** Add credentials to `.env`:
```env
CLOUDINARY_CLOUD_NAME=your_cloud_name
CLOUDINARY_API_KEY=your_api_key
CLOUDINARY_API_SECRET=your_api_secret
```

### Upload Fails
**Issue:** Upload returns 500 error

**Check:**
1. Cloudinary credentials are correct
2. Network connectivity to Cloudinary
3. File permissions
4. PHP GD extension installed

### Authentication Fails
**Issue:** Cannot get authentication token

**Solution:**
1. Run migrations: `php artisan migrate`
2. Create test user manually
3. Check database connection

---

## Best Practices

### 1. Cleanup
Always clean up test uploads from Cloudinary to avoid storage costs:
```php
protected function tearDown(): void
{
    foreach ($this->uploadedPublicIds as $publicId) {
        $this->cloudinaryService->delete($publicId, 'image');
    }
    parent::tearDown();
}
```

### 2. Test Isolation
Use database transactions to ensure test isolation:
```php
use RefreshDatabase;
```

### 3. Mocking
For unit tests, mock Cloudinary service to avoid API calls:
```php
$mock = Mockery::mock(CloudinaryService::class);
$mock->shouldReceive('upload')->andReturn([...]);
```

### 4. Environment-Specific Testing
Use different Cloudinary folders for testing:
```php
$folder = app()->environment('testing') ? 'test/media' : 'media';
```

---

## CI/CD Integration

### GitHub Actions Example
```yaml
- name: Run Cloudinary Tests
  env:
    CLOUDINARY_CLOUD_NAME: ${{ secrets.CLOUDINARY_CLOUD_NAME }}
    CLOUDINARY_API_KEY: ${{ secrets.CLOUDINARY_API_KEY }}
    CLOUDINARY_API_SECRET: ${{ secrets.CLOUDINARY_API_SECRET }}
  run: php artisan test --filter CloudinaryImageUploadTest
```

---

## Additional Resources

- [Cloudinary PHP SDK Documentation](https://cloudinary.com/documentation/php_integration)
- [Laravel File Upload Documentation](https://laravel.com/docs/filesystem)
- [PHPUnit Documentation](https://phpunit.de/documentation.html)

---

## Test Results Interpretation

### Success Criteria
- ✓ All tests pass (100% success rate)
- ✓ Images uploaded to Cloudinary
- ✓ Database records created
- ✓ URLs are accessible
- ✓ Thumbnails generated
- ✓ Cleanup successful

### Failure Investigation
1. Check error messages in test output
2. Verify Cloudinary dashboard for uploads
3. Check database for records
4. Review Laravel logs: `storage/logs/laravel.log`
5. Enable debug mode: `APP_DEBUG=true`

---

## Maintenance

### Regular Tasks
- Monitor Cloudinary storage usage
- Clean up old test files
- Update test data as needed
- Review and update validation rules
- Check for deprecated Cloudinary API features

---

**Last Updated:** January 22, 2025
**Version:** 1.0.0

# Cloudinary Migration Guide

## Overview

This guide explains how the Laravel application has been refactored to use Cloudinary exclusively for all image storage, replacing local storage completely.

## What Changed

### 1. **All Image Uploads Now Use Cloudinary**

- User avatars
- Admin profile pictures
- App logos
- Status/Story media (images & videos)
- Chat group avatars
- Message media (images, videos, audio, documents)

### 2. **Database Storage**

All image fields in the database now store **Cloudinary URLs** instead of local paths:
- `users.avatar_url`
- `chats.avatar_url`
- `statuses.media_url` & `statuses.thumbnail_url`
- `messages.media_url` & `messages.thumbnail_url`
- `settings.logo_url`

### 3. **Model Accessors**

All models with image fields now have accessors that ensure Cloudinary URLs are always returned:
- `User::getAvatarUrlAttribute()`
- `Status::getMediaUrlAttribute()` & `Status::getThumbnailUrlAttribute()`
- `Chat::getAvatarUrlAttribute()`
- `Message::getMediaUrlAttribute()` & `Message::getThumbnailUrlAttribute()`

### 4. **API Responses**

All API endpoints now return fully-qualified Cloudinary URLs. No relative paths are returned.

## Configuration

### Environment Variables

Ensure these are set in your `.env` file:

```env
# Cloudinary Configuration
CLOUDINARY_CLOUD_NAME=your_cloud_name
CLOUDINARY_API_KEY=your_api_key
CLOUDINARY_API_SECRET=your_api_secret
CLOUDINARY_URL=cloudinary://api_key:api_secret@cloud_name
CLOUDINARY_UPLOAD_PRESET=your_upload_preset

# Media Configuration
MEDIA_DISK=cloudinary
MAX_FILE_SIZE=10240
ALLOWED_IMAGE_TYPES=jpg,jpeg,png,gif,webp
ALLOWED_VIDEO_TYPES=mp4,avi,mov,wmv,flv
ALLOWED_AUDIO_TYPES=mp3,wav,aac,ogg,m4a
ALLOWED_DOCUMENT_TYPES=pdf,doc,docx,xls,xlsx,ppt,pptx,txt
```

## Migration Process

### Step 1: Backup Your Data

Before migrating, create a backup:

```bash
php artisan db:backup
```

### Step 2: Test Migration (Dry Run)

Run a dry run to see what will be migrated without making changes:

```bash
php artisan cloudinary:migrate --dry-run
```

### Step 3: Migrate Specific Types

You can migrate specific types of images:

```bash
# Migrate only avatars
php artisan cloudinary:migrate --type=avatars

# Migrate only statuses
php artisan cloudinary:migrate --type=statuses

# Migrate only chat avatars
php artisan cloudinary:migrate --type=chats

# Migrate only message media
php artisan cloudinary:migrate --type=messages

# Migrate only logos
php artisan cloudinary:migrate --type=logos
```

### Step 4: Migrate Everything

Migrate all images at once:

```bash
php artisan cloudinary:migrate --type=all --force
```

The `--force` flag skips the confirmation prompt.

### Step 5: Verify Migration

After migration, verify that:
1. All images are accessible via Cloudinary URLs
2. The Flutter app can load all images
3. No broken image links exist

## API Endpoints

### Upload Endpoints

All upload endpoints now use Cloudinary:

#### 1. Upload User Avatar
```http
POST /api/media/upload-avatar
Content-Type: multipart/form-data

avatar: [file]
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "public_id": "avatars/avatar_1_1234567890",
    "avatar_url": "https://res.cloudinary.com/your-cloud/image/upload/avatars/avatar_1_1234567890.jpg",
    "thumbnail_url": "https://res.cloudinary.com/your-cloud/image/upload/c_fill,h_100,w_100/avatars/avatar_1_1234567890.jpg",
    "small_url": "https://res.cloudinary.com/your-cloud/image/upload/c_fill,h_50,w_50/avatars/avatar_1_1234567890.jpg"
  },
  "message": "Avatar uploaded successfully"
}
```

#### 2. Upload Status Media
```http
POST /api/media/upload-status-media
Content-Type: multipart/form-data

media: [file]
type: image|video
```

**Response:**
```json
{
  "success": true,
  "data": {
    "public_id": "status/status_1234567890",
    "url": "https://res.cloudinary.com/your-cloud/image/upload/status/status_1234567890.jpg",
    "thumbnail_url": "https://res.cloudinary.com/your-cloud/image/upload/c_fill,h_200,w_200/status/status_1234567890.jpg",
    "type": "image",
    "format": "jpg",
    "size": 102400,
    "size_formatted": "100 KB"
  },
  "message": "Status media uploaded successfully"
}
```

#### 3. Upload Chat Avatar
```http
POST /api/media/upload-chat-avatar
Content-Type: multipart/form-data

chat_avatar: [file]
chat_id: 123
```

#### 4. Upload Message Media
```http
POST /api/media/upload
Content-Type: multipart/form-data

file: [file]
type: image|video|audio|document|voice
chat_id: 123 (optional)
```

### Delete Endpoint

```http
DELETE /api/media/delete
Content-Type: application/json

{
  "public_id": "avatars/avatar_1_1234567890",
  "resource_type": "image"
}
```

## Flutter Integration

### Image Display

All image URLs from the API are now fully-qualified Cloudinary URLs. Display them directly:

```dart
// User Avatar
Image.network(user.avatarUrl)

// Status Media
Image.network(status.mediaUrl)

// Message Media
Image.network(message.mediaUrl)

// Chat Avatar
Image.network(chat.avatarUrl)
```

### Image Upload

When uploading images from Flutter:

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

### Caching

Cloudinary URLs are CDN-backed and optimized. Use Flutter's `CachedNetworkImage` for better performance:

```dart
import 'package:cached_network_image/cached_network_image.dart';

CachedNetworkImage(
  imageUrl: user.avatarUrl,
  placeholder: (context, url) => CircularProgressIndicator(),
  errorWidget: (context, url, error) => Icon(Icons.error),
)
```

## Image Transformations

Cloudinary provides powerful image transformations. The service automatically generates:

### Avatar Sizes
- **Full size**: Original uploaded image
- **Thumbnail**: 100x100px (for lists)
- **Small**: 50x50px (for compact views)

### Status Thumbnails
- **Thumbnail**: 200x200px (for status previews)

### Custom Transformations

You can request custom transformations via the CloudinaryService:

```php
$cloudinary = app(CloudinaryService::class);

// Get a 300x300 thumbnail
$thumbnailUrl = $cloudinary->getTransformedUrl($publicId, 300, 300);

// Get a 500x500 thumbnail with different crop mode
$thumbnailUrl = $cloudinary->getTransformedUrl($publicId, 500, 500, 'thumb');
```

## Error Handling

### Upload Failures

All upload endpoints return detailed error messages:

```json
{
  "success": false,
  "message": "Upload failed",
  "error": "File size exceeds maximum limit"
}
```

### Common Errors

1. **Invalid Cloudinary credentials**: Check your `.env` configuration
2. **File too large**: Adjust `MAX_FILE_SIZE` or Cloudinary upload limits
3. **Invalid file type**: Check `ALLOWED_*_TYPES` configuration
4. **Network timeout**: Increase PHP `max_execution_time` for large files

## Performance Optimization

### 1. CDN Delivery

All images are served through Cloudinary's global CDN, ensuring fast delivery worldwide.

### 2. Automatic Format Optimization

Cloudinary automatically serves images in the most efficient format (WebP, AVIF) based on browser support.

### 3. Lazy Loading

Implement lazy loading in your Flutter app to load images only when needed.

### 4. Responsive Images

Use Cloudinary's responsive image features to serve appropriately sized images for different devices.

## Cleanup

### Remove Local Storage

After successful migration, you can safely remove local storage files:

```bash
# Remove old avatar files
rm -rf storage/app/public/avatars/*

# Remove old status files
rm -rf storage/app/public/status/*

# Remove old media files
rm -rf storage/app/public/media/*

# Remove old logos
rm -rf storage/app/public/logos/*
```

### Remove Storage Link

The symbolic link to public storage is no longer needed:

```bash
rm public/storage
```

## Rollback Plan

If you need to rollback:

1. **Keep local files**: Don't delete local files until you're confident the migration is successful
2. **Database backup**: Restore from backup if needed
3. **Revert code**: Use git to revert to the previous version

## Monitoring

### Cloudinary Dashboard

Monitor your usage in the Cloudinary dashboard:
- Storage usage
- Bandwidth usage
- Transformation usage
- API calls

### Laravel Logs

Check Laravel logs for upload/delete errors:

```bash
tail -f storage/logs/laravel.log
```

## Cost Considerations

### Free Tier Limits

Cloudinary's free tier includes:
- 25 GB storage
- 25 GB bandwidth/month
- 25,000 transformations/month

### Optimization Tips

1. **Delete unused images**: Implement cleanup for expired statuses
2. **Use appropriate sizes**: Don't upload unnecessarily large images
3. **Cache transformations**: Reuse transformation URLs when possible

## Support

For issues or questions:
1. Check Cloudinary documentation: https://cloudinary.com/documentation
2. Review Laravel logs
3. Check the migration command output
4. Verify environment variables

## Testing Checklist

- [ ] User avatar upload works
- [ ] User avatar displays correctly in app
- [ ] Status media upload works (images)
- [ ] Status media upload works (videos)
- [ ] Status media displays correctly
- [ ] Chat avatar upload works
- [ ] Message media upload works (all types)
- [ ] Message media displays correctly
- [ ] App logo upload works (admin panel)
- [ ] App logo displays correctly
- [ ] Old local images migrated successfully
- [ ] Image deletion works
- [ ] Thumbnails generate correctly
- [ ] API returns Cloudinary URLs
- [ ] Flutter app loads all images
- [ ] No broken image links

## Conclusion

Your Laravel application now uses Cloudinary exclusively for all image storage. This provides:
- ✅ Scalable cloud storage
- ✅ Global CDN delivery
- ✅ Automatic image optimization
- ✅ No local storage dependency
- ✅ Better performance
- ✅ Easier deployment

All images are now served through Cloudinary's secure URLs, fully compatible with your Flutter frontend.

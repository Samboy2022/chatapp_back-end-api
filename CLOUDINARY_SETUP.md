# Cloudinary Setup & Configuration

## Quick Start

This application uses **Cloudinary** for all image and media storage. Follow these steps to set up Cloudinary for your application.

## Step 1: Create Cloudinary Account

1. Go to [Cloudinary](https://cloudinary.com/)
2. Sign up for a free account
3. Verify your email address
4. Log in to your dashboard

## Step 2: Get Your Credentials

From your Cloudinary Dashboard:

1. Click on **Dashboard** in the left sidebar
2. You'll see your **Account Details**:
   - Cloud Name
   - API Key
   - API Secret

## Step 3: Configure Environment Variables

Add these to your `.env` file:

```env
# Cloudinary Configuration
CLOUDINARY_CLOUD_NAME=your_cloud_name_here
CLOUDINARY_API_KEY=your_api_key_here
CLOUDINARY_API_SECRET=your_api_secret_here
CLOUDINARY_URL=cloudinary://your_api_key:your_api_secret@your_cloud_name
CLOUDINARY_UPLOAD_PRESET=your_upload_preset (optional)

# Media Configuration
MEDIA_DISK=cloudinary
MAX_FILE_SIZE=10240
ALLOWED_IMAGE_TYPES=jpg,jpeg,png,gif,webp
ALLOWED_VIDEO_TYPES=mp4,avi,mov,wmv,flv
ALLOWED_AUDIO_TYPES=mp3,wav,aac,ogg,m4a
ALLOWED_DOCUMENT_TYPES=pdf,doc,docx,xls,xlsx,ppt,pptx,txt
```

### Example Configuration

```env
CLOUDINARY_CLOUD_NAME=dd5ckivdo
CLOUDINARY_API_KEY=179752827411527
CLOUDINARY_API_SECRET=quuYt9gqmeSea8vaXMHDz4KBYzU
CLOUDINARY_URL=cloudinary://179752827411527:quuYt9gqmeSea8vaXMHDz4KBYzU@dd5ckivdo
```

## Step 4: Install Dependencies

The Cloudinary PHP SDK is already included in `composer.json`. If you need to reinstall:

```bash
composer require cloudinary/cloudinary_php
```

## Step 5: Test Configuration

Test your Cloudinary connection:

```bash
php artisan tinker
```

Then run:

```php
$cloudinary = app(\App\Services\CloudinaryService::class);
$result = $cloudinary->getFileInfo('test', 'image');
print_r($result);
```

## Step 6: Migrate Existing Images (If Any)

If you have existing images in local storage, migrate them to Cloudinary:

```bash
# Dry run first (no changes)
php artisan cloudinary:migrate --dry-run

# Migrate all images
php artisan cloudinary:migrate --type=all --force
```

## Cloudinary Folders Structure

The application organizes media in Cloudinary folders:

```
cloudinary://
├── avatars/           # User profile pictures
├── chat-avatars/      # Group chat avatars
├── logos/             # Application logos
├── status/            # Status/Story media
└── media/
    ├── images/        # Message images
    ├── videos/        # Message videos
    ├── audios/        # Message audio files
    └── documents/     # Message documents
```

## Upload Presets (Optional)

Upload presets allow unsigned uploads from the client. To create one:

1. Go to **Settings** → **Upload** in Cloudinary Dashboard
2. Scroll to **Upload presets**
3. Click **Add upload preset**
4. Configure:
   - **Preset name**: `farmersnetworkupload` (or your choice)
   - **Signing Mode**: Signed (recommended) or Unsigned
   - **Folder**: Leave empty (handled by app)
   - **Access Mode**: Public
5. Save the preset
6. Add to `.env`: `CLOUDINARY_UPLOAD_PRESET=farmersnetworkupload`

## Security Best Practices

### 1. Protect Your Credentials

- ✅ Never commit `.env` file to git
- ✅ Use environment variables in production
- ✅ Rotate API secrets periodically
- ✅ Use signed uploads (not unsigned)

### 2. Access Control

- ✅ Set appropriate folder permissions
- ✅ Use signed URLs for private content
- ✅ Implement rate limiting on uploads

### 3. Validation

The application validates:
- File types (images, videos, audio, documents)
- File sizes (configurable limits)
- User authentication (all uploads require login)

## Cloudinary Features Used

### 1. Image Transformations

Automatic generation of:
- Thumbnails (100x100, 200x200)
- Small avatars (50x50)
- Optimized formats (WebP, AVIF)

### 2. CDN Delivery

All media is delivered through Cloudinary's global CDN for:
- Fast loading worldwide
- Automatic caching
- DDoS protection

### 3. Automatic Optimization

- Format conversion (WebP for supported browsers)
- Quality optimization
- Responsive images

### 4. Video Processing

- Automatic transcoding
- Thumbnail generation
- Adaptive bitrate streaming

## Monitoring & Analytics

### Cloudinary Dashboard

Monitor your usage:

1. **Media Library**: View all uploaded files
2. **Reports**: Check bandwidth and storage usage
3. **Transformations**: Monitor transformation usage
4. **API Calls**: Track API usage

### Laravel Logs

Check upload/delete operations:

```bash
tail -f storage/logs/laravel.log | grep Cloudinary
```

## Free Tier Limits

Cloudinary's free tier includes:

| Resource | Limit |
|----------|-------|
| Storage | 25 GB |
| Bandwidth | 25 GB/month |
| Transformations | 25,000/month |
| API Calls | Unlimited |

### Upgrade Options

If you exceed free tier limits:
- **Plus Plan**: $99/month (100 GB storage, 100 GB bandwidth)
- **Advanced Plan**: $249/month (250 GB storage, 250 GB bandwidth)
- **Custom Plans**: Contact Cloudinary sales

## Troubleshooting

### Issue: "Invalid Cloudinary credentials"

**Solution:**
1. Verify credentials in `.env`
2. Check for extra spaces or quotes
3. Ensure `CLOUDINARY_URL` format is correct
4. Test credentials in Cloudinary dashboard

### Issue: "Upload failed"

**Solution:**
1. Check file size (max 100MB by default)
2. Verify file type is allowed
3. Check Cloudinary quota (free tier limits)
4. Review Laravel logs for detailed error

### Issue: "Images not loading"

**Solution:**
1. Verify Cloudinary URLs are correct
2. Check browser console for CORS errors
3. Ensure images are public (not private)
4. Test URL directly in browser

### Issue: "Slow uploads"

**Solution:**
1. Check internet connection
2. Reduce image size before upload
3. Use Cloudinary's upload widget for better performance
4. Consider using background jobs for large files

## Performance Optimization

### 1. Lazy Loading

Implement lazy loading in Flutter:

```dart
import 'package:cached_network_image/cached_network_image.dart';

CachedNetworkImage(
  imageUrl: imageUrl,
  placeholder: (context, url) => CircularProgressIndicator(),
  errorWidget: (context, url, error) => Icon(Icons.error),
)
```

### 2. Responsive Images

Use appropriate image sizes:

```dart
// For list items (small)
final smallUrl = imageUrl.replaceAll('/upload/', '/upload/c_fill,h_50,w_50/');

// For detail view (medium)
final mediumUrl = imageUrl.replaceAll('/upload/', '/upload/c_fill,h_300,w_300/');

// For full screen (large)
final largeUrl = imageUrl; // Original size
```

### 3. Caching Strategy

```dart
// Cache images for 7 days
CachedNetworkImage(
  imageUrl: imageUrl,
  cacheManager: CacheManager(
    Config(
      'customCacheKey',
      stalePeriod: Duration(days: 7),
    ),
  ),
)
```

## Cost Optimization

### 1. Delete Unused Media

Implement cleanup for:
- Expired statuses (24 hours)
- Deleted messages
- Removed user accounts

```bash
# Run cleanup command
php artisan cloudinary:cleanup
```

### 2. Optimize Before Upload

- Compress images before upload
- Use appropriate resolutions
- Convert videos to efficient formats

### 3. Use Transformations Wisely

- Cache transformation URLs
- Reuse common transformations
- Avoid generating unique transformations per request

## Backup Strategy

### 1. Database Backup

Backup your database regularly (includes Cloudinary URLs):

```bash
php artisan db:backup
```

### 2. Cloudinary Backup

Cloudinary automatically backs up your media. For additional backup:

1. Use Cloudinary's backup add-on
2. Download media library periodically
3. Use Cloudinary's API to export metadata

## Migration from Local Storage

If migrating from local storage:

1. **Backup everything first**
   ```bash
   tar -czf storage_backup.tar.gz storage/app/public/
   ```

2. **Run migration**
   ```bash
   php artisan cloudinary:migrate --type=all
   ```

3. **Verify migration**
   - Check all images load correctly
   - Test upload/delete operations
   - Verify API responses

4. **Clean up local storage**
   ```bash
   rm -rf storage/app/public/avatars/*
   rm -rf storage/app/public/media/*
   rm -rf storage/app/public/status/*
   ```

## Support Resources

- **Cloudinary Documentation**: https://cloudinary.com/documentation
- **PHP SDK Docs**: https://cloudinary.com/documentation/php_integration
- **Community Forum**: https://community.cloudinary.com/
- **Support**: support@cloudinary.com

## Checklist

Before going to production:

- [ ] Cloudinary account created
- [ ] Credentials added to `.env`
- [ ] Dependencies installed
- [ ] Configuration tested
- [ ] Existing images migrated (if any)
- [ ] Upload functionality tested
- [ ] Delete functionality tested
- [ ] Flutter app tested with Cloudinary URLs
- [ ] Monitoring set up
- [ ] Backup strategy in place
- [ ] Cost limits configured
- [ ] Security reviewed

## Next Steps

1. ✅ Complete Cloudinary setup
2. ✅ Test all upload endpoints
3. ✅ Migrate existing images
4. ✅ Update Flutter app to use new URLs
5. ✅ Monitor usage and performance
6. ✅ Implement cleanup for expired content

Your application is now ready to use Cloudinary for all media storage! 🎉

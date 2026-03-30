# Image Display Fix for Deployment

This guide ensures images display correctly when the app is deployed to production hosting.

## Required Setup

### 1. Set APP_URL in Production

In your production `.env`, set `APP_URL` to your full API domain:

```env
APP_ENV=production
APP_URL=https://your-domain.com
```

**Important:** Use `https://` if your site uses SSL. Images will use this base URL.

### 2. Create Storage Link

Run this command after deploying (or add to your deployment script):

```bash
php artisan storage:link
```

This creates a symbolic link from `public/storage` → `storage/app/public` so uploaded files are accessible.

**Shared hosting:** If your host doesn't support symlinks, you may need to:
- Copy files from `storage/app/public` to `public/storage` periodically, or
- Use Cloudinary for all media (recommended for production)

### 3. Cloudinary (Recommended for Production)

For reliable image delivery, configure Cloudinary in `.env`:

```env
CLOUDINARY_CLOUD_NAME=your_cloud_name
CLOUDINARY_API_KEY=your_api_key
CLOUDINARY_API_SECRET=your_api_secret
```

New uploads will use Cloudinary. To migrate existing local images, run:

```bash
php artisan migrate:images-to-cloudinary
```

## What Was Fixed

- **User avatars, chat avatars, status media, message media** now return full absolute URLs (e.g. `https://your-domain.com/storage/avatars/xxx.jpg`) instead of relative paths
- **Production URL forcing** – In production, the app forces HTTPS and uses `APP_URL` for all generated URLs
- **Mixed storage** – The app correctly handles both Cloudinary URLs and local storage paths

## Troubleshooting

| Issue | Solution |
|-------|----------|
| Images show as broken | Verify `APP_URL` matches your actual domain (with https://) |
| 404 on /storage/ URLs | Run `php artisan storage:link` |
| Old images still broken | Run the Cloudinary migration or ensure files exist in `storage/app/public` |
| Wrong domain in URLs | Clear config cache: `php artisan config:clear` |

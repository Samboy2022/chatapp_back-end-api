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

## Repairing Broken Images (After Database Migration)

If you have moved your database from Local to Production, many images might still point to `localhost`. Follow these steps to fix them:

### 1. Normalize Database URLs
Visit this URL on your hosted site:
`https://your-domain.com/host-fix/normalize-urls`

This tool will scan your users, statuses, messages, and settings tables and convert any absolute `localhost` URLs into clean, environment-agnostic relative paths.

### 2. Test Storage Accessibility
Visit this URL to verify the server can write and serve files:
`https://your-domain.com/host-fix/test-upload`

It will create a test file and give you a link to view it. If the link doesn't work, your `storage:link` is either missing or incorrectly configured.

## Troubleshooting

| Issue | Solution |
|-------|----------|
| Images show as broken | 1. Verify `APP_URL` in `.env`<br>2. Run `/host-fix/normalize-urls` |
| 404 on /storage/ URLs | Run `/host-fix/symlink` or `php artisan storage:link` |
| Can't upload new images | Ensure `storage/app/public` is writable (755 or 775) |
| Mixed storage issues | Ensure Cloudinary credentials are set in `.env` if using it |
| Wrong domain in URLs | Clear config cache: `/host-fix/update-url?url=https://your-domain.com` |


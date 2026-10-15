#!/usr/bin/env bash
# Deploy to farmersnetwork.com.ng (cPanel account fnskills, SSH host "farmersnet" in ~/.ssh/config).
#
#   ./deploy.sh                 deploy the current code
#   ./deploy.sh "commit msg"    commit all changes, push to GitHub, then deploy
#   ./deploy.sh --dry-run       show which files would be uploaded, change nothing
#
# Safe by design:
#   - uploads only new/changed files; never deletes anything on the server
#     (except stale compiled assets inside public/build)
#   - never uploads .env, vendor/, node_modules/ or storage/ (user uploads stay untouched)
#   - runs only pending migrations (never fresh/refresh/seed)
#   - runs composer install on the server only when composer.lock changed
set -euo pipefail

SSH_HOST="farmersnet"
APP_DIR="farmersnetwork.com.ng"
SITE_URL="https://farmersnetwork.com.ng"
BRANCH="main"

cd "$(dirname "$0")"

EXCLUDES=(
    --exclude='.env' --exclude='.env.*' --include='.env.example'
    --exclude='/vendor/' --exclude='/node_modules/' --exclude='/storage/'
    --exclude='/bootstrap/cache/*.php' --exclude='/public/storage' --exclude='/public/hot'
    --exclude='/public/build/' --exclude='/public/debug.php' --exclude='/vendor-prod.zip'
    --exclude='.git/' --exclude='.github/' --exclude='.claude/' --exclude='.idea/' --exclude='.vscode/'
    --exclude='.DS_Store' --exclude='*.log' --exclude='.phpunit.result.cache'
    --exclude='/tests/' --exclude='/deploy.sh'
)

if [ "${1:-}" = "--dry-run" ]; then
    rsync -rlc --dry-run --itemize-changes "${EXCLUDES[@]}" ./ "$SSH_HOST:$APP_DIR/" | grep -v '^\.d' || true
    exit 0
fi

if [ $# -gt 0 ] && [ -n "$(git status --porcelain)" ]; then
    git add -A
    git commit -m "$1"
fi
if [ "$(git rev-parse --abbrev-ref HEAD)" = "$BRANCH" ]; then
    echo "==> Pushing $BRANCH to GitHub"
    git push origin "$BRANCH" || echo "   (push failed, continuing with deploy)"
fi

CHANGES=$(rsync -rlc --dry-run --itemize-changes "${EXCLUDES[@]}" ./ "$SSH_HOST:$APP_DIR/" | grep -v '^\.d' || true)

echo "==> Checking PHP syntax of changed files"
echo "$CHANGES" | awk '{print $2}' | grep '\.php$' | while read -r f; do
    php -l "$f" >/dev/null || { echo "Syntax error in $f, deploy aborted." >&2; exit 1; }
done

echo "==> Building assets"
npm run build --silent

echo "==> Uploading changed files"
echo "${CHANGES:-   (no code changes)}"
rsync -rlc "${EXCLUDES[@]}" ./ "$SSH_HOST:$APP_DIR/"
rsync -rlc --delete public/build/ "$SSH_HOST:$APP_DIR/public/build/"

RUN_COMPOSER=0
echo "$CHANGES" | grep -qE ' composer\.(lock|json)$' && RUN_COMPOSER=1

echo "==> Finishing on server"
ssh "$SSH_HOST" "APP_DIR=$APP_DIR RUN_COMPOSER=$RUN_COMPOSER bash -s" <<'REMOTE'
set -euo pipefail
cd ~/"$APP_DIR"
if [ "$RUN_COMPOSER" = "1" ]; then
    echo "composer.lock changed: installing dependencies"
    composer install --no-dev --optimize-autoloader --no-interaction
fi
php artisan migrate --force
php artisan optimize:clear >/dev/null
php artisan optimize >/dev/null || { echo "optimize failed; running uncached"; php artisan optimize:clear >/dev/null; }
REMOTE

echo "==> Health check"
for path in /up /api/health; do
    code=$(curl -s -o /dev/null -w "%{http_code}" "$SITE_URL$path")
    echo "   $SITE_URL$path -> $code"
    [ "$code" = "200" ] || { echo "Health check failed. Check storage/logs/laravel.log on the server." >&2; exit 1; }
done
echo "Deploy complete."

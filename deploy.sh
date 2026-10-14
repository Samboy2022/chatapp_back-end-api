#!/usr/bin/env bash
# Deploy to chat.wazobiaapp.com (cPanel account billsver, SSH host "cpanel" in ~/.ssh/config).
#
#   ./deploy.sh                 push committed work and deploy
#   ./deploy.sh "commit msg"    commit all changes with that message, push, deploy
set -euo pipefail

SSH_HOST="cpanel"
APP_DIR="chatapp-api"
BRANCH="main"

cd "$(dirname "$0")"

if [ -n "$(git status --porcelain)" ]; then
    if [ $# -eq 0 ]; then
        echo "You have uncommitted changes. Commit them, or run: ./deploy.sh \"your commit message\"" >&2
        exit 1
    fi
    git add -A
    git commit -m "$1"
fi

echo "==> Pushing $BRANCH to GitHub"
git push origin "$BRANCH"

echo "==> Deploying on server"
ssh "$SSH_HOST" "APP_DIR=$APP_DIR BRANCH=$BRANCH bash -s" <<'REMOTE'
set -euo pipefail
cd ~/"$APP_DIR"
git fetch origin "$BRANCH"
git reset --hard "origin/$BRANCH"
composer install --no-dev --optimize-autoloader --no-interaction --quiet
php artisan migrate --force
php artisan optimize:clear
php artisan optimize
echo "Deployed $(git log -1 --oneline)"
REMOTE

echo "==> Health check"
curl -s -o /dev/null -w "https://chat.wazobiaapp.com/up -> %{http_code}\n" https://chat.wazobiaapp.com/up

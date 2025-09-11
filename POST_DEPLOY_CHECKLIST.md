# Travel Order System - VPS Post-Deployment Checklist

Follow these steps after you upload the project to your VPS file manager.

1) Put env in place
- Ensure .env.production exists in the project root (I prepared it for you).
- If your host allows running scripts, copy it as .env:
  cp .env.production .env
- Otherwise, use the File Manager to duplicate .env.production to .env.

2) Install PHP dependencies (Composer)
- If you have SSH access, run:
  composer install --no-dev --optimize-autoloader
- If no SSH, use your hosting provider’s Composer feature, or request support to run it in the project root.

3) Set correct permissions
- If you have SSH, run:
  bash fix-permissions.sh
- If you only have File Manager, ensure these paths are writable by the web server:
  /storage
  /bootstrap/cache

4) Application key
- If you have SSH, run:
  php artisan key:generate --force
- If you can’t run SSH, keep the APP_KEY value that’s already in .env.production.

5) Database
- In phpMyAdmin, open SQL tab and paste the contents of database-setup.sql (I prepared it for you). This creates the DB user and grants permissions.
- Then run migrations (SSH):
  php artisan migrate --force
- If you don’t have SSH, let me know and I’ll prepare a minimal PHP runner to trigger migrations once via browser.

6) Optimize for production (SSH)
  php artisan config:cache && php artisan route:cache && php artisan view:cache

7) Storage symlink (SSH)
  php artisan storage:link

8) Web server document root
- Ensure your domain (travelorder.dictr2.online) points to the project’s public directory, not the repository root. In most control panels this is a setting called “Document Root” or “Public Path”. Set it to:
  <project_path>/public

9) Test
- Visit https://travelorder.dictr2.online
- If you see 500 error, check logs in storage/logs/laravel.log

Need help with a no-SSH setup? Reply here and I’ll add a safe, one-time web installer route to run migrations and caches from your browser, then auto-disable it.


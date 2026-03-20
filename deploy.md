name: Deploy Laravel

on:
  push:
    branches:
      - main

jobs:
  deploy:
    runs-on: ubuntu-latest

    steps:
      - name: Deploy ke VPS
        uses: appleboy/ssh-action@v1.0.0
        with:
          host: ${{ secrets.SERVER_HOST }}
          username: ${{ secrets.SERVER_USER }}
          key: ${{ secrets.SERVER_SSH_KEY }}
          script: |
            cd /var/www/appele

            git pull origin main

            composer install --no-dev --optimize-autoloader

            npm install
            npm run build

   
            sudo chown -R www-data:www-data storage bootstrap/cache
            sudo chmod -R 775 storage bootstrap/cache

            php artisan migrate --force

            php artisan config:cache
            php artisan route:cache
            php artisan view:cache
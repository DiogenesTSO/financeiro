#!/bin/sh

# Mude para o diretório da aplicação
cd /var/www/html

# Instala dependências do frontend e compila os assets
echo "Building frontend assets..."
npm install
npm run build

# Limpe e otimize a aplicação.
echo "Optimizing Laravel..."
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Garanta que as permissões estão corretas
echo "Fixing permissions..."
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Executa as migrações do banco de dados
echo "Running database migrations..."
php artisan migrate --force

# Inicia o servidor Apache
echo "Starting Apache server..."
apache2-foreground

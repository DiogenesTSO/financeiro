#!/bin/sh

# Mude para o diretório da aplicação
cd /var/www/html

# Otimize a aplicação (o build dos assets já foi feito no Dockerfile)
echo "Optimizing Laravel..."
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Executa as migrações do banco de dados
echo "Running database migrations..."
php artisan migrate --force

# Inicia o servidor Apache
echo "Starting Apache server..."
apache2-foreground

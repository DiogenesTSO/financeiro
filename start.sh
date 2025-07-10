#!/bin/sh

# Executa as migrações do banco de dados.
# O --force é necessário para rodar em ambiente de produção sem perguntas.
echo "Running database migrations..."
php artisan migrate --force

# Inicia o servidor Apache em primeiro plano (foreground).
# O 'apache2-foreground' é o comando padrão para a imagem Docker que estamos usando.
echo "Starting Apache server..."
apache2-foreground

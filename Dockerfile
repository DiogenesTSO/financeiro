# Use uma imagem oficial do PHP com Apache
FROM php:8.2-apache

# Instale as dependências do sistema, incluindo Node.js e NPM
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    locales \
    zip \
    jpegoptim optipng pngquant gifsicle \
    vim \
    unzip \
    git \
    curl \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    libicu-dev \
    libpq-dev \
    nodejs \
    npm \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip intl pdo_pgsql

# Instale o Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# ... (o resto do Dockerfile continua igual) ...

# Configure o Apache para apontar para a pasta public do Laravel
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
RUN a2enmod rewrite

# Defina o diretório de trabalho
WORKDIR /var/www/html

# Copie os arquivos de dependência primeiro para otimizar o cache do Docker
COPY composer.json composer.lock ./

# Instale as dependências do Composer SEM executar scripts
RUN composer install --no-interaction --optimize-autoloader --no-dev --no-scripts

# Agora copie o resto dos arquivos da sua aplicação
COPY . .

# Corrija as permissões para o Laravel
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Copie o script de inicialização para o contêiner
COPY start.sh /usr/local/bin/start.sh

# Torne o script executável
RUN chmod +x /usr/local/bin/start.sh

# Defina o script como o comando de inicialização padrão do contêiner
CMD ["/usr/local/bin/start.sh"]

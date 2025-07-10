# Use uma imagem oficial do PHP com Apache
FROM php:8.2-apache

# Instale as dependências do sistema, incluindo Node.js e NPM
RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libfreetype6-dev locales zip \
    jpegoptim optipng pngquant gifsicle vim unzip git curl \
    libzip-dev libonig-dev libxml2-dev libicu-dev libpq-dev \
    nodejs npm \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip intl pdo_pgsql

# Instale o Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configure o Apache
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
RUN a2enmod rewrite

# Defina o diretório de trabalho
WORKDIR /var/www/html

# Copie os arquivos de dependência do PHP e instale
COPY composer.json composer.lock ./
RUN composer install --no-interaction --optimize-autoloader --no-dev --no-scripts

# --- INÍCIO DAS MUDANÇAS ---

# Copie os arquivos de dependência do Node e instale
COPY package.json package-lock.json ./
RUN npm install

# Copie o resto da aplicação
COPY . .

# Compile os assets para produção
RUN npm run build

# --- FIM DAS MUDANÇAS ---

# Corrija as permissões
RUN chown -R www-data:www-data storage bootstrap/cache
RUN chmod -R 775 storage bootstrap/cache

# Copie e configure o script de inicialização
COPY start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh
CMD ["/usr/local/bin/start.sh"]

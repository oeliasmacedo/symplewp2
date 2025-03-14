FROM php:8.4-fpm

# Argumentos
ARG user=www-data
ARG uid=1000

# Instala dependências do sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    supervisor \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd soap \
    && pecl install redis \
    && docker-php-ext-enable redis

# Limpa cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Instala Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Cria diretório do sistema
RUN mkdir -p /var/www/wp-manager

# Cria diretórios de log
RUN mkdir -p /var/log/wp-manager \
    && mkdir -p /var/log/php \
    && mkdir -p /var/log/supervisor \
    && chown -R www-data:www-data /var/log

# Define diretório de trabalho
WORKDIR /var/www/wp-manager

# Copia arquivos do projeto
COPY . .

# Copia configurações do PHP
COPY php-fpm.conf /usr/local/etc/php-fpm.d/www.conf

# Define permissões
RUN chown -R www-data:www-data /var/www/wp-manager \
    && chmod -R 755 /var/www/wp-manager \
    && chmod -R 775 /var/www/wp-manager/storage

# Configura usuário
USER ${user}

# Expõe porta
EXPOSE 9000

# Inicia PHP-FPM
CMD ["php-fpm"] 
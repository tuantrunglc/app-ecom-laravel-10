# Sử dụng PHP 8.1 với Apache
FROM php:8.1-apache

# Thiết lập thư mục làm việc
WORKDIR /var/www/html

# Cài đặt các dependencies hệ thống và Node.js
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    mariadb-client \
    netcat-openbsd \
    && curl -fsSL https://deb.nodesource.com/setup_16.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Verify Node.js installation
RUN node --version && npm --version

# Cài đặt PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Cài đặt Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Cấu hình Apache
RUN a2enmod rewrite

# Copy composer files
COPY composer.json composer.lock ./

# Cài đặt dependencies PHP
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Copy package.json và cài đặt Node.js dependencies
COPY package*.json ./
RUN npm install

# Copy source code
COPY --chown=www-data:www-data . /var/www/html

# Khôi phục vendor nếu cần
RUN if [ ! -d "/var/www/html/vendor" ]; then \
        composer install --no-dev --optimize-autoloader --no-interaction --no-scripts; \
    fi

# Cleanup node_modules để giảm kích thước image
RUN rm -rf node_modules

# Cấu hình Apache
COPY docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf

# Copy initialization script
COPY docker/scripts/init.sh /usr/local/bin/init.sh
RUN chmod +x /usr/local/bin/init.sh

# Copy và setup entrypoint script
COPY docker/scripts/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Thiết lập quyền
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Expose port 80
EXPOSE 80

# Start với entrypoint
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

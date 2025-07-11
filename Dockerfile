FROM php:8.2-fpm

# Set working directory
WORKDIR /var/www

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nodejs \
    npm \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy composer files first for better caching
COPY composer.json composer.lock ./

# Install PHP dependencies with error handling
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist || \
    (echo "Composer install failed, trying with --ignore-platform-reqs" && \
     composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist --ignore-platform-reqs)

# Copy the rest of the application
COPY . /var/www

# Copy existing application directory permissions
COPY --chown=www-data:www-data . /var/www

# Install Node.js dependencies (only if package.json exists)
COPY package.json package-lock.json* ./
RUN if [ -f package.json ]; then npm install; fi

# Build assets (only if vite.config.js exists)
RUN if [ -f vite.config.js ]; then npm run build; fi

# Create storage directories and set permissions
RUN mkdir -p /var/www/storage/framework/{cache,sessions,views} \
    && mkdir -p /var/www/storage/logs \
    && chmod -R 775 /var/www/storage \
    && chmod -R 775 /var/www/bootstrap/cache

# Change ownership of our applications
RUN chown -R www-data:www-data /var/www

# Expose port 9000
EXPOSE 9000

# Start Laravel development server on port 9000
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=9000"] 
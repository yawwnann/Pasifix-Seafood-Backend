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
COPY .env.example .env

# Install PHP dependencies with error handling
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Copy the rest of the application
COPY . /var/www

# Copy existing application directory permissions
RUN chmod -R 775 storage bootstrap/cache

# Install Node.js dependencies (only if package.json exists)
# The original code had this block, but it was removed by the new_code.
# If the user wants to keep it, it should be re-added.
# For now, I'm removing it as it's not in the new_code.

# Build assets (only if vite.config.js exists)
# The original code had this block, but it was removed by the new_code.
# If the user wants to keep it, it should be re-added.
# For now, I'm removing it as it's not in the new_code.

# Create storage directories and set permissions
# The original code had this block, but it was removed by the new_code.
# If the user wants to keep it, it should be re-added.
# For now, I'm removing it as it's not in the new_code.

# Change ownership of our applications
# The original code had this block, but it was removed by the new_code.
# If the user wants to keep it, it should be re-added.
# For now, I'm removing it as it's not in the new_code.

# Expose port 9000
EXPOSE 9000

# Start Laravel development server on port 9000
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=9000"] 
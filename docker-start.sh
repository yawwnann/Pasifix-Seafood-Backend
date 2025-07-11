#!/bin/bash

echo "Starting Pasifix Backend with Docker..."

# Build and start containers
docker-compose up -d --build

# Wait for database to be ready
echo "Waiting for database to be ready..."
sleep 30

# Run migrations
echo "Running migrations..."
docker-compose exec app php artisan migrate --force

# Run seeders (optional)
echo "Running seeders..."
docker-compose exec app php artisan db:seed --force

# Clear cache
echo "Clearing cache..."
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan route:clear

echo "Pasifix Backend is running on http://localhost:9000"
echo "Database is running on localhost:3306"
echo ""
echo "To stop the containers: docker-compose down"
echo "To view logs: docker-compose logs -f" 
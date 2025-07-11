# Pasifix Backend - Docker Setup

## Prerequisites

-   Docker
-   Docker Compose

## Quick Start

### Windows

```bash
# Run the batch file
docker-start.bat
```

### Linux/Mac

```bash
# Make the script executable
chmod +x docker-start.sh

# Run the script
./docker-start.sh
```

### Manual Setup

```bash
# Build and start containers
docker-compose up -d --build

# Wait for database to be ready (30 seconds)
# Then run migrations
docker-compose exec app php artisan migrate --force

# Run seeders (optional)
docker-compose exec app php artisan db:seed --force

# Clear cache
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan route:clear
```

## Access Points

-   **Backend API**: http://localhost:9000
-   **Database**: localhost:3306
-   **Filament Admin**: http://localhost:9000/admin

## Environment Variables

The Docker setup includes these environment variables:

```
APP_ENV=local
APP_DEBUG=true
APP_KEY=base64:4pseE1/iEPRU+L1Xl3vMLjLkLRNgYLPUS5ZJ0oUzJPA=
APP_URL=http://localhost:9000
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=pasifix
DB_USERNAME=root
DB_PASSWORD=password
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
CLOUDINARY_CLOUD_NAME=dm3icigfr
CLOUDINARY_API_KEY=368671751925851
CLOUDINARY_API_SECRET=vkeX0XBXwodlqFPWbBUNUQQf80E
```

## Useful Commands

```bash
# View logs
docker-compose logs -f

# Stop containers
docker-compose down

# Restart containers
docker-compose restart

# Access container shell
docker-compose exec app bash

# Run artisan commands
docker-compose exec app php artisan migrate
docker-compose exec app php artisan db:seed
docker-compose exec app php artisan cache:clear

# View container status
docker-compose ps
```

## Troubleshooting

### Port 9000 already in use

```bash
# Check what's using port 9000
netstat -ano | findstr :9000

# Kill the process
taskkill /PID <PID> /F
```

### Database connection issues

```bash
# Check if database container is running
docker-compose ps

# Restart database container
docker-compose restart db
```

### Permission issues

```bash
# Fix storage permissions
docker-compose exec app chmod -R 775 storage
docker-compose exec app chmod -R 775 bootstrap/cache
```

## Development

The application is configured to run on port 9000 with the following features:

-   Laravel 12
-   PHP 8.2
-   MySQL 8.0
-   Node.js for asset compilation
-   Cloudinary integration
-   Filament admin panel

## Production

For production deployment, modify the Dockerfile and docker-compose.yml to:

1. Use production environment variables
2. Optimize for performance
3. Use proper SSL certificates
4. Configure proper database credentials

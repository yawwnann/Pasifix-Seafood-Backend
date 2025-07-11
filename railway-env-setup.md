# Railway Environment Variables Setup

This guide will help you set up all the required environment variables in Railway to eliminate the Laravel warnings.

## Required Environment Variables for Railway

Add these environment variables in your Railway project settings:

### Basic App Configuration

```
APP_NAME=Pasifix
APP_ENV=production
APP_KEY=base64:your-app-key-here
APP_DEBUG=false
APP_URL=https://your-railway-app-url.railway.app
```

### Database Configuration

```
DB_CONNECTION=mysql
DB_HOST=your-mysql-host
DB_PORT=3306
DB_DATABASE=your-database-name
DB_USERNAME=your-username
DB_PASSWORD=your-password
DB_URL=mysql://username:password@host:port/database
```

### Cache Configuration

```
CACHE_DRIVER=file
DB_CACHE_CONNECTION=mysql
DB_CACHE_TABLE=cache
DB_CACHE_LOCK_CONNECTION=mysql
DB_CACHE_LOCK_TABLE=cache_locks
```

### Session Configuration

```
SESSION_DRIVER=database
SESSION_CONNECTION=mysql
SESSION_STORE=database
SESSION_SECURE_COOKIE=true
SESSION_TABLE=sessions
```

### Queue Configuration

```
QUEUE_CONNECTION=database
DB_QUEUE_CONNECTION=mysql
DB_QUEUE_TABLE=jobs
DB_QUEUE=default
DB_QUEUE_RETRY_AFTER=90
QUEUE_FAILED_DRIVER=database-uuids
```

### Logging Configuration

```
LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug
LOG_STACK=single
LOG_STDERR_FORMATTER=
LOG_SLACK_WEBHOOK_URL=
LOG_SLACK_USERNAME=Laravel Log
LOG_SLACK_EMOJI=:boom:
LOG_PAPERTRAIL_HANDLER=
PAPERTRAIL_URL=
PAPERTRAIL_PORT=
```

### Mail Configuration

```
MAIL_MAILER=log
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME=Pasifix
MAIL_URL=
MAIL_SCHEME=
MAIL_EHLO_DOMAIN=
MAIL_SENDMAIL_PATH=
MAIL_LOG_CHANNEL=
POSTMARK_MESSAGE_STREAM_ID=
POSTMARK_TOKEN=
```

### Redis Configuration (Optional - set to null if not using)

```
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_URL=
REDIS_USERNAME=
REDIS_CLIENT=phpredis
REDIS_CLUSTER=redis
REDIS_PERSISTENT=false
REDIS_PREFIX=
REDIS_DB=0
REDIS_CACHE_DB=1
REDIS_CACHE_CONNECTION=cache
REDIS_CACHE_LOCK_CONNECTION=default
REDIS_QUEUE_CONNECTION=default
REDIS_QUEUE=default
REDIS_QUEUE_RETRY_AFTER=90
```

### Memcached Configuration (Optional - set to null if not using)

```
MEMCACHED_HOST=127.0.0.1
MEMCACHED_PORT=11211
MEMCACHED_PERSISTENT_ID=
MEMCACHED_USERNAME=
MEMCACHED_PASSWORD=
```

### AWS Configuration (Optional - set to null if not using)

```
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_URL=
AWS_ENDPOINT=
AWS_USE_PATH_STYLE_ENDPOINT=false
DYNAMODB_ENDPOINT=
DYNAMODB_CACHE_TABLE=cache
```

### Cloudinary Configuration (Required for your app)

```
CLOUDINARY_CLOUD_NAME=your-cloudinary-cloud-name
CLOUDINARY_API_KEY=your-cloudinary-api-key
CLOUDINARY_API_SECRET=your-cloudinary-api-secret
CLOUDINARY_NOTIFICATION_URL=
```

### Other Optional Services

```
SQS_PREFIX=https://sqs.us-east-1.amazonaws.com/your-account-id
SQS_QUEUE=default
SQS_SUFFIX=
RESEND_KEY=
SLACK_BOT_USER_OAUTH_TOKEN=
SLACK_BOT_USER_DEFAULT_CHANNEL=
```

## Steps to Set Up in Railway:

1. Go to your Railway project dashboard
2. Navigate to the "Variables" tab
3. Add each environment variable listed above
4. For optional services (Redis, Memcached, AWS, etc.), you can set them to empty values if you're not using them
5. Make sure to set `APP_KEY` to a proper Laravel application key
6. Update database credentials to match your Railway MySQL service
7. Set `APP_URL` to your Railway app URL

## Generate App Key

If you need to generate a new app key, you can run this command locally and copy the result:

```bash
php artisan key:generate --show
```

## Important Notes:

-   Set `APP_ENV=production` for production deployment
-   Set `APP_DEBUG=false` for production
-   Make sure your database credentials match your Railway MySQL service
-   For Cloudinary, use your actual Cloudinary credentials
-   For optional services, you can leave them empty if not using them

This should eliminate all the Laravel environment variable warnings you're seeing.

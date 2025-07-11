<?php

/**
 * Railway Environment Variables Generator
 * 
 * This script helps generate the proper environment variables for Railway deployment
 * Run this script locally to get the values you need to set in Railway
 */

echo "=== Railway Environment Variables Generator ===\n\n";

// Generate APP_KEY
$appKey = 'base64:' . base64_encode(random_bytes(32));
echo "APP_KEY={$appKey}\n\n";

// Database configuration (you'll need to update these with your Railway MySQL credentials)
echo "=== Database Configuration ===\n";
echo "DB_CONNECTION=mysql\n";
echo "DB_HOST=your-mysql-host-from-railway\n";
echo "DB_PORT=3306\n";
echo "DB_DATABASE=your-database-name\n";
echo "DB_USERNAME=your-username\n";
echo "DB_PASSWORD=your-password\n";
echo "DB_URL=mysql://username:password@host:port/database\n\n";

// Basic app configuration
echo "=== Basic App Configuration ===\n";
echo "APP_NAME=Pasifix\n";
echo "APP_ENV=production\n";
echo "APP_DEBUG=false\n";
echo "APP_URL=https://your-railway-app-url.railway.app\n\n";

// Cache configuration
echo "=== Cache Configuration ===\n";
echo "CACHE_DRIVER=file\n";
echo "DB_CACHE_CONNECTION=mysql\n";
echo "DB_CACHE_TABLE=cache\n";
echo "DB_CACHE_LOCK_CONNECTION=mysql\n";
echo "DB_CACHE_LOCK_TABLE=cache_locks\n\n";

// Session configuration
echo "=== Session Configuration ===\n";
echo "SESSION_DRIVER=database\n";
echo "SESSION_CONNECTION=mysql\n";
echo "SESSION_STORE=database\n";
echo "SESSION_SECURE_COOKIE=true\n";
echo "SESSION_TABLE=sessions\n\n";

// Queue configuration
echo "=== Queue Configuration ===\n";
echo "QUEUE_CONNECTION=database\n";
echo "DB_QUEUE_CONNECTION=mysql\n";
echo "DB_QUEUE_TABLE=jobs\n";
echo "DB_QUEUE=default\n";
echo "DB_QUEUE_RETRY_AFTER=90\n";
echo "QUEUE_FAILED_DRIVER=database-uuids\n\n";

// Logging configuration
echo "=== Logging Configuration ===\n";
echo "LOG_CHANNEL=stack\n";
echo "LOG_DEPRECATIONS_CHANNEL=null\n";
echo "LOG_LEVEL=debug\n";
echo "LOG_STACK=single\n";
echo "LOG_STDERR_FORMATTER=\n";
echo "LOG_SLACK_WEBHOOK_URL=\n";
echo "LOG_SLACK_USERNAME=Laravel Log\n";
echo "LOG_SLACK_EMOJI=:boom:\n";
echo "LOG_PAPERTRAIL_HANDLER=\n";
echo "PAPERTRAIL_URL=\n";
echo "PAPERTRAIL_PORT=\n\n";

// Mail configuration
echo "=== Mail Configuration ===\n";
echo "MAIL_MAILER=log\n";
echo "MAIL_HOST=mailpit\n";
echo "MAIL_PORT=1025\n";
echo "MAIL_USERNAME=null\n";
echo "MAIL_PASSWORD=null\n";
echo "MAIL_ENCRYPTION=null\n";
echo "MAIL_FROM_ADDRESS=noreply@yourdomain.com\n";
echo "MAIL_FROM_NAME=Pasifix\n";
echo "MAIL_URL=\n";
echo "MAIL_SCHEME=\n";
echo "MAIL_EHLO_DOMAIN=\n";
echo "MAIL_SENDMAIL_PATH=\n";
echo "MAIL_LOG_CHANNEL=\n";
echo "POSTMARK_MESSAGE_STREAM_ID=\n";
echo "POSTMARK_TOKEN=\n\n";

// Redis configuration (optional - set to empty if not using)
echo "=== Redis Configuration (Optional) ===\n";
echo "REDIS_HOST=127.0.0.1\n";
echo "REDIS_PASSWORD=null\n";
echo "REDIS_PORT=6379\n";
echo "REDIS_URL=\n";
echo "REDIS_USERNAME=\n";
echo "REDIS_CLIENT=phpredis\n";
echo "REDIS_CLUSTER=redis\n";
echo "REDIS_PERSISTENT=false\n";
echo "REDIS_PREFIX=\n";
echo "REDIS_DB=0\n";
echo "REDIS_CACHE_DB=1\n";
echo "REDIS_CACHE_CONNECTION=cache\n";
echo "REDIS_CACHE_LOCK_CONNECTION=default\n";
echo "REDIS_QUEUE_CONNECTION=default\n";
echo "REDIS_QUEUE=default\n";
echo "REDIS_QUEUE_RETRY_AFTER=90\n\n";

// Memcached configuration (optional - set to empty if not using)
echo "=== Memcached Configuration (Optional) ===\n";
echo "MEMCACHED_HOST=127.0.0.1\n";
echo "MEMCACHED_PORT=11211\n";
echo "MEMCACHED_PERSISTENT_ID=\n";
echo "MEMCACHED_USERNAME=\n";
echo "MEMCACHED_PASSWORD=\n\n";

// AWS configuration (optional - set to empty if not using)
echo "=== AWS Configuration (Optional) ===\n";
echo "AWS_ACCESS_KEY_ID=\n";
echo "AWS_SECRET_ACCESS_KEY=\n";
echo "AWS_DEFAULT_REGION=us-east-1\n";
echo "AWS_BUCKET=\n";
echo "AWS_URL=\n";
echo "AWS_ENDPOINT=\n";
echo "AWS_USE_PATH_STYLE_ENDPOINT=false\n";
echo "DYNAMODB_ENDPOINT=\n";
echo "DYNAMODB_CACHE_TABLE=cache\n\n";

// Cloudinary configuration (required for your app)
echo "=== Cloudinary Configuration (Required) ===\n";
echo "CLOUDINARY_CLOUD_NAME=your-cloudinary-cloud-name\n";
echo "CLOUDINARY_API_KEY=your-cloudinary-api-key\n";
echo "CLOUDINARY_API_SECRET=your-cloudinary-api-secret\n";
echo "CLOUDINARY_NOTIFICATION_URL=\n\n";

// Other optional services
echo "=== Other Optional Services ===\n";
echo "SQS_PREFIX=https://sqs.us-east-1.amazonaws.com/your-account-id\n";
echo "SQS_QUEUE=default\n";
echo "SQS_SUFFIX=\n";
echo "RESEND_KEY=\n";
echo "SLACK_BOT_USER_OAUTH_TOKEN=\n";
echo "SLACK_BOT_USER_DEFAULT_CHANNEL=\n\n";

echo "=== Instructions ===\n";
echo "1. Copy the APP_KEY value above\n";
echo "2. Update the database credentials with your Railway MySQL service details\n";
echo "3. Update the APP_URL with your Railway app URL\n";
echo "4. Update Cloudinary credentials with your actual values\n";
echo "5. Add all these variables to your Railway project's environment variables\n";
echo "6. For optional services (Redis, Memcached, AWS), you can leave them empty if not using\n\n";

echo "=== Important Notes ===\n";
echo "- Set APP_ENV=production for production deployment\n";
echo "- Set APP_DEBUG=false for production\n";
echo "- Make sure your database credentials match your Railway MySQL service\n";
echo "- For Cloudinary, use your actual Cloudinary credentials\n";
echo "- For optional services, you can leave them empty if not using them\n";
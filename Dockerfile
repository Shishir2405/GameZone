FROM php:8.2-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    default-mysql-client \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions for MySQL
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Set working directory
WORKDIR /app

# Copy application files
COPY . .

# Expose port
EXPOSE 8000

# Start command
CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-8000} -t ."]
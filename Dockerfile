FROM php:8.2-cli

# Install required PHP extensions
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Set working directory
WORKDIR /app

# Copy application files
COPY . .

# Expose port
EXPOSE 8000

# Start command
CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-8000} -t ."]
FROM php:8.2-cli

# Install mysqli extension
RUN docker-php-ext-install mysqli

# Set working directory
WORKDIR /app

# Copy application files
COPY . .

# Expose port
EXPOSE 8000

# Start command
CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-8000} -t ."]
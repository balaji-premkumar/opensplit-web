# OpenSplit - Laravel 11 / PHP 8.4 FPM
# Uses pre-built base image for fast builds

ARG BASE_IMAGE=ghcr.io/balaji-premkumar/opensplit-web-base:latest
FROM ${BASE_IMAGE}

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY --chown=appuser:appgroup . .

# Switch to non-root user
USER appuser

# Expose port 9000 for PHP-FPM
EXPOSE 9000

CMD ["php-fpm"]

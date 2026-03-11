# docker/nginx/Dockerfile
# Stage 1: Build assets
FROM node:22-bookworm-slim AS builder

# Set working directory
WORKDIR /var/www

# Copy Laravel application code
COPY . /var/www

# Install Node.js dependencies and build assets
RUN npm ci && npm run build

# Stage 2: Nginx production image
FROM nginxinc/nginx-unprivileged:1.27-alpine AS runner

# Copy custom Nginx configuration
# -----------------------------------------------------------
# Replace the default Nginx configuration with our custom one
# that is optimized for serving a Laravel application.
# -----------------------------------------------------------
COPY ./docker/production/nginx/nginx.conf /etc/nginx/nginx.conf

# Copy Laravel's public assets from the builder stage
# -----------------------------------------------------------
# We only need the 'public' directory from our Laravel app.
# -----------------------------------------------------------
COPY --chown=nginx:nginx --from=builder /var/www/public /var/www/public

# Use a built-in non-root user for security
USER nginx

# Set the working directory to the public folder
WORKDIR /var/www/public

# Expose port 8080 (nginx-unprivileged default)
EXPOSE 8080
CMD ["nginx", "-g", "daemon off;"]
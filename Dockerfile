# Use the official PHP image with Apache
FROM php:8.1-apache

# Copy your application code to the container's web root
COPY . /var/www/html/

# Expose port 80 for the web server
EXPOSE 80

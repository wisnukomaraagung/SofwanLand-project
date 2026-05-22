FROM php:8.2-apache

# Install PDO MySQL extension
RUN docker-php-ext-install pdo pdo_mysql

# Enable Apache mod_rewrite for URL rewriting (if needed)
RUN a2enmod rewrite

# Serve the app from the `public` directory
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri 's!DocumentRoot /var/www/html!DocumentRoot ${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/000-default.conf \
    && sed -ri 's!<Directory /var/www/>!<Directory ${APACHE_DOCUMENT_ROOT}>!g' /etc/apache2/apache2.conf

# Copy application source code to the container
COPY . /var/www/html/

# Set the correct permissions for Apache
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

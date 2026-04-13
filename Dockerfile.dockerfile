FROM php:8.2-apache

# Instalar extensões necessárias
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Ativar mod_rewrite
RUN a2enmod rewrite

# Definir pasta pública correta
ENV APACHE_DOCUMENT_ROOT /var/www/html/asy/public_html

# Ajustar configuração do Apache para novo DocumentRoot
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Criar diretório asy
RUN mkdir -p /var/www/html/asy

# Copiar projeto para dentro de /asy
COPY . /var/www/html/asy

# Permissões
RUN chown -R www-data:www-data /var/www/html

# Remover aviso ServerName
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

EXPOSE 80

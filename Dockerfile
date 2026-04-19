FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
        nginx \
        supervisor \
        tzdata \
        curl \
    && cp /usr/share/zoneinfo/Europe/Zurich /etc/localtime \
    && echo "Europe/Zurich" > /etc/timezone \
    && apk del tzdata \
    && mkdir -p /run/nginx /var/log/supervisor

RUN echo "date.timezone = Europe/Zurich" > /usr/local/etc/php/conf.d/timezone.ini \
    && echo "expose_php = Off" > /usr/local/etc/php/conf.d/hardening.ini \
    && echo "display_errors = Off" >> /usr/local/etc/php/conf.d/hardening.ini \
    && echo "log_errors = On" >> /usr/local/etc/php/conf.d/hardening.ini

COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/php-fpm-pool.conf /usr/local/etc/php-fpm.d/zz-www.conf
COPY docker/supervisord.conf /etc/supervisord.conf

WORKDIR /var/www/html

# App-Dateien (alles ausser Dev-Kram siehe .dockerignore)
COPY --chown=www-data:www-data . /var/www/html/

# Logs-Dir (wird vom Compose als Volume ueberschrieben)
RUN mkdir -p /var/www/html/logs/log_forms \
    && chown -R www-data:www-data /var/www/html/logs

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=3s --retries=3 \
    CMD wget -qO- http://127.0.0.1/ >/dev/null 2>&1 || exit 1

CMD ["supervisord", "-c", "/etc/supervisord.conf"]

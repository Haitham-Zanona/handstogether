FROM webdevops/php-nginx:8.3-alpine

WORKDIR /app

COPY . /app

RUN chown -R application:application /app \
 && chmod -R 775 /app/storage /app/bootstrap/cache

COPY ./render/nginx.conf /opt/docker/etc/nginx/vhost.conf
COPY ./render/start.sh /start.sh
RUN chmod +x /start.sh

EXPOSE 80
CMD ["/start.sh"]

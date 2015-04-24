FROM richarvey/nginx-php-fpm
RUN rm -rf
COPY ./site /usr/share/nginx/html
EXPOSE 80
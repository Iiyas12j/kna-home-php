FROM php:8.2-cli

WORKDIR /app

COPY . .

CMD sh -c "php -d output_buffering=4096 -S 0.0.0.0:${PORT} -t public"


FROM nginx:1.21.3-alpine

COPY public /app/public
COPY docker/nginx.conf /etc/nginx/conf.d/default.conf
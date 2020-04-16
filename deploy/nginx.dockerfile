FROM nginx:1.17.9-alpine
COPY nginx.conf /etc/nginx/conf.d/default.conf
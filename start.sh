#!/bin/bash
# Start script for Production (Coolify/Nixpacks with Nginx + PHP-FPM)
set -e

echo "Starting Lampegan Motor CMS in production mode..."

# Clear Laravel caches to ensure fresh start
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Buat symbolic link untuk folder storage
php artisan storage:link --force || true
mkdir -p storage/framework/{cache,sessions,views} bootstrap/cache /var/log/nginx /tmp/nginx_client_body /tmp/nginx_fastcgi /tmp/nginx_proxy
chmod -R 775 storage bootstrap/cache || true

# Run database migrations automatically
php artisan migrate --force || true

# Check if nginx and php-fpm are available
if command -v nginx >/dev/null 2>&1 && command -v php-fpm >/dev/null 2>&1; then
    echo "Starting with high-performance Nginx + PHP-FPM..."
    
    NGINX_PREFIX=$(dirname $(dirname $(which nginx)))
    PORT="${PORT:-8000}"
    
    # Configure PHP-FPM
    FPM_CONF="/tmp/php-fpm.conf"
    cat << 'EOF' > "$FPM_CONF"
[global]
daemonize = yes
error_log = /dev/stderr

[www]
listen = 127.0.0.1:9000
user = nobody
group = nogroup
pm = dynamic
pm.max_children = 30
pm.start_servers = 6
pm.min_spare_servers = 4
pm.max_spare_servers = 12
pm.max_requests = 1000
clear_env = no
catch_workers_output = yes
request_terminate_timeout = 60s
EOF

    # Configure Nginx
    NGINX_CONF="/tmp/nginx.conf"
    cat << EOF > "$NGINX_CONF"
worker_processes auto;
daemon off;
pid /tmp/nginx.pid;
error_log /dev/stderr warn;

events {
    worker_connections 1024;
}

http {
    include ${NGINX_PREFIX}/conf/mime.types;
    default_type application/octet-stream;
    
    access_log /dev/stdout;
    error_log /dev/stderr;
    
    sendfile on;
    tcp_nopush on;
    tcp_nodelay on;
    keepalive_timeout 65;
    client_max_body_size 64M;

    server {
        listen ${PORT};
        listen [::]:${PORT};
        server_name _;
        root /app/public;

        add_header X-Frame-Options "SAMEORIGIN";
        add_header X-Content-Type-Options "nosniff";

        index index.php index.html;
        charset utf-8;

        location / {
            try_files \$uri \$uri/ /index.php?\$query_string;
        }

        location = /favicon.ico { access_log off; log_not_found off; }
        location = /robots.txt  { access_log off; log_not_found off; }

        error_page 404 /index.php;

        location ~ \.php$ {
            fastcgi_pass 127.0.0.1:9000;
            fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
            fastcgi_param PATH_INFO \$fastcgi_path_info;
            include ${NGINX_PREFIX}/conf/fastcgi_params;
            fastcgi_connect_timeout 30s;
            fastcgi_send_timeout 60s;
            fastcgi_read_timeout 60s;
            fastcgi_buffer_size 128k;
            fastcgi_buffers 4 256k;
            fastcgi_busy_buffers_size 256k;
        }

        location ~ /\.(?!well-known).* {
            deny all;
        }
    }
}
EOF

    # Start PHP-FPM
    echo "Starting PHP-FPM daemon..."
    php-fpm -y "$FPM_CONF"
    
    # Start Nginx in foreground
    echo "Starting Nginx on port ${PORT}..."
    exec nginx -c "$NGINX_CONF"
else
    echo "Nginx/PHP-FPM not detected, fallback to artisan serve on port ${PORT:-8000}..."
    exec php artisan serve --host=0.0.0.0 --port="${PORT:-8000}"
fi

#!/usr/bin/env bash
set -eu

echo "+ Set user owner"
chown www-data.www-data /var/www/storage/oaut*

echo "run nginx"
nginx -c /etc/nginx/nginx.conf -t
service nginx start

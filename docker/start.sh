#!/bin/bash

# Start php-fpm in the background
php-fpm &

# Start PM2 to manage other processes (ex: Node.js app or anything else)
pm2-runtime start /var/www/docker/ecosystem.config.js

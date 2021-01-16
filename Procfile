web: vendor/bin/heroku-php-nginx  -C nginx_app.conf public/
worker: php artisan queue:restart && php artisan queue:work --tries=3
worker_broadcast: php artisan queue:work -- queue=broadcast --tries=3
websocet: php artisan websockets:serve
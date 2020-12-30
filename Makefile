setup:
	composer install
	cp -n .env.example .env|| true
	php artisan key:gen --ansi
	#touch database/database.sqlite
	#php artisan migrate
	#php artisan db:seed
	npm install

watch:
	npm run watch

migrate:
	php artisan migrate

console:
	php artisan tinker

log:
	tail -f storage/logs/laravel.log

test:
	php artisan test

deploy:
	git push heroku

lint:
	composer run-script phpcs

lint-fix:
	composer phpcbf

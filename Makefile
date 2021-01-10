setup:
	composer install
	cp -n .env.example .env|| true
	php artisan key:gen --ansi
	sudo systemctl start postgresql.service
	sudo -u postgres psql --command="CREATE USER test_user PASSWORD 'test'" --command="\du"
	sudo -u postgres createdb --owner=test_user test_db
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

test_cover
	composer phpunit -- --coverage-clover ./coverage.xml

deploy:
	git push heroku

lint:
	composer run-script phpcs

lint-fix:
	composer phpcbf

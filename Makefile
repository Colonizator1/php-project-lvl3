setup:
	composer install
	cp -n .env.example .env|| true
	php artisan key:gen --ansi
	sudo systemctl start postgresql.service
	sudo -u postgres psql --command="CREATE USER test_user PASSWORD 'test'" --command="\du"
	sudo -u postgres createdb --owner=test_user test_db  --command="\l"
	config(['database.connections.pgsql.database' => 'test_db'])
	config(['database.connections.pgsql.username' => 'test_user'])
	config(['database.connections.pgsql.password' => 'test'])
	php artisan migrate:status
	php artisan migrate
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

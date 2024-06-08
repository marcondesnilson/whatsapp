IMAGE_NAME = "tabela-nutricao"

up: start composer migrate permissao key limpar-cache

start:
	docker compose up -d

down:
	docker compose down

migrate:
	sleep 10 && docker compose exec app php artisan migrate --force

migrate-rollback:
	docker compose exec app php artisan migrate:rollback

migrate-seed:
	docker compose exec app artisan migrate:fresh --seeder=Database\\Seeders\\dev\\DatabaseSeeder

schedule:
	docker compose exec app php artisan schedule:run

composer:
	sleep 5 && docker compose exec app composer install --no-interaction --no-progress --no-suggest || true

db:
	docker compose exec mysql bash

exec:
	docker compose exec app bash

limpar-cache:
	docker compose exec app php artisan config:clear && \
	docker compose exec app php artisan route:clear && \
	docker compose exec app php artisan view:clear && \
	docker compose exec app php artisan event:clear && \
    docker compose exec app php artisan config:cache && \
	docker compose exec app php artisan cache:clear

build:
	docker build -t $(IMAGE_NAME) .
	# docker push $(IMAGE_NAME)

permissao:
	docker compose exec app chmod -R 777 /var/www/storage/framework/views/
	docker compose exec app chmod -R 777 /var/www/storage/framework/cache/
	docker compose exec app chmod -R 777 /var/www/storage/logs/
	docker compose exec app chmod -R 664 /var/www/database/database.sqlite

key:
	docker compose exec app php artisan key:generate

#instalar docker
#https://docs.docker.com/engine/install/ubuntu/

#deixar publico
#https://localtunnel.github.io/www/

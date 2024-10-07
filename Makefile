IMAGE_NAME = "whatsapp_app"

up: start composer migrate limpar-cache permissao key pm2-start

restart: pm2-stop down up

start:
	docker compose up -d

down:
	docker compose down

migrate:
	sleep 10 && docker compose exec whatsapp_app php artisan migrate --force

migrate-rollback:
	docker compose exec whatsapp_app php artisan migrate:rollback

migrate-seed:
	docker compose exec whatsapp_app artisan migrate:fresh --seeder=Database\\Seeders\\dev\\DatabaseSeeder

schedule:
	docker compose exec whatsapp_app php artisan schedule:run

composer:
	sleep 5 && docker compose exec whatsapp_app composer install --no-interaction --no-progress --no-suggest || true

db:
	docker compose exec mysql bash

exec:
	docker compose exec whatsapp_app bash

limpar-cache:
	docker compose exec whatsapp_app php artisan config:clear && \
	docker compose exec whatsapp_app php artisan route:clear && \
	docker compose exec whatsapp_app php artisan view:clear && \
	docker compose exec whatsapp_app php artisan event:clear && \
	docker compose exec whatsapp_app php artisan cache:clear

build:
	docker build -t $(IMAGE_NAME) .
	# docker push $(IMAGE_NAME)

permissao:
	docker compose exec whatsapp_app chmod -R 777 /var/www/storage/framework/views/
	docker compose exec whatsapp_app chmod -R 777 /var/www/storage/framework/cache/
	docker compose exec whatsapp_app chmod -R 777 /var/www/storage/logs

pm2-start:
	docker compose exec whatsapp_app pm2 start docker/ecosystem.config.js
pm2-stop:
	docker compose exec whatsapp_app pm2 delete docker/ecosystem.config.js
pm2-restart:
	docker compose exec whatsapp_app pm2 restart docker/ecosystem.config.js
pm2-log:
	docker compose exec whatsapp_app pm2 logs

key:
	docker compose exec whatsapp_app php artisan key:generate

#instalar docker
#https://docs.docker.com/engine/install/ubuntu/

#deixar publico
#https://localtunnel.github.io/www/

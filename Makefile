include .env

init:
	cp .env.example .env
	make init-wo-env-cp

init-win:
	copy .env.example .env
	make init-wo-env-cp

init-wo-env-cp:
	make docker-compose
	docker-compose run --no-deps --rm composer sh -c "COMPOSER_MIRROR_PATH_REPOS=1 composer install --ignore-platform-reqs --prefer-dist"
	docker-compose run --no-deps --rm npm npm --prefix /app install
	docker build -t advplus-app-local -f ./docker/dev/.dockerfile .
	make up
	make art key:generate
	make art "passport:keys --force"
	make cache
	make exec "sh /usr/local/bin/wait-for-db.sh php /app/artisan migrate"
	make art db:seed
	make link-storage
	make cache
	make ide-helper
	make stop

build:
	docker-compose build

up:
	docker-compose up -d app
# 	make ide-helper
	make cache
stop:
	docker-compose stop

docker-compose:
	docker-compose -f docker-compose.base.yml -f docker-compose.dev.yml config > docker-compose.yml

exec:
	docker exec -u $(FPM_USER):$(FPM_GROUP) -it advplus-app $(filter-out $@,$(MAKECMDGOALS))
art:
	docker exec -u $(FPM_USER):$(FPM_GROUP) -it advplus-app php artisan $(filter-out $@,$(MAKECMDGOALS))

link-storage:
	docker exec advplus-app php artisan storage:link

cache:
	docker exec advplus-app php artisan optimize:clear

csfix:
	docker exec advplus-app vendor/bin/php-cs-fixer fix

db-import:
	docker exec -i advplus-mariadb sh -c 'exec mysql -uroot -p"$$MYSQL_ROOT_PASSWORD" -e "DROP DATABASE IF EXISTS $(db); CREATE DATABASE $(db);"'
	docker exec -i advplus-mariadb sh -c "exec mysql -uroot -p"$$MYSQL_ROOT_PASSWORD" $(db)" < $(file)

db-import-winwsl:
	docker exec -i advplus-mariadb sh -c 'exec mysql -uroot -p"$$MYSQL_ROOT_PASSWORD" -e "DROP DATABASE IF EXISTS $(db); CREATE DATABASE $(db);"'
	docker exec -i advplus-mariadb sh -c 'exec mysql -uroot -p"$$MYSQL_ROOT_PASSWORD" $(db)' < $(file)

gitlab-reg-login:
	docker login registry.gitlab.com

base-image-build:
	docker build -t registry.gitlab.com/parasol-software/advplus/advplus-v2/php:8.1-nginx -f ./docker/base-image/Dockerfile .
base-image-push:
	docker push registry.gitlab.com/parasol-software/advplus/advplus-v2/php --all-tags

ide-helper:
	make art ide-helper:generate
	make art ide-helper:meta
	make art "ide-helper:model -N"

npm:
	docker-compose run --rm npm npm $(filter-out $@,$(MAKECMDGOALS))

%:
	@:

db-obf:

	docker exec -i advplus-mariadb sh -c 'exec mysql -uroot -p"$$MYSQL_ROOT_PASSWORD" -e "DROP DATABASE IF EXISTS temp_obf; CREATE DATABASE temp_obf;"'

	docker exec -i advplus-mariadb sh -c "exec mysql -uroot -p"$$MYSQL_ROOT_PASSWORD" temp_obf" < $(INPUT_DUMP)

	docker exec -e DB_DATABASE=temp_obf -u $(FPM_USER):$(FPM_GROUP) -it advplus-app php artisan obfuscate:data

	docker exec -i advplus-mariadb sh -c "exec mysqldump -uroot -p"$$MYSQL_ROOT_PASSWORD" temp_obf" > $(OUTPUT_DUMP)

	docker exec -i advplus-mariadb sh -c 'exec mysql -uroot -p"$$MYSQL_ROOT_PASSWORD" -e "DROP DATABASE temp_obf;"'

#make db-obf INPUT_DUMP=/home/adv/dumps/advplus.2023-10-27-15.00.sql OUTPUT_DUMP=/home/adv/dumps/obf.2023-10-27-15.00.sql


jjj:
	@ echo "It's empty task."

ci:
	composer validate && \
	make lint

build:
	composer install && \
	npm ci && \
	npx gulp

release:
	make ci && \
	composer install --no-dev && \
	npm ci && \
	npx gulp release && \
	\
	mkdir -p ./dist && \
	rm -rf ./dist/* && \
	\
	cp ./composer.json ./dist && \
	cp ./composer.lock ./dist && \
	cp ./package.json ./dist && \
	cp ./package-lock.json ./dist && \
	cp ./README.md ./dist && \
	cp ./szed.php ./dist && \
	cp ./webpack.config.js ./dist && \
	cp -r ./assets ./dist && \
	cp -r ./inc ./dist && \
	cp -r ./vendor ./dist && \
	cp -r ./views ./dist && \
	\
	make build

## Common lint command
lint:
	make lint-php && \
	make lint-js && \
	make lint-css

## PHP code-style
lint-php:
	@ vendor/bin/phpcs -s

lint-php-summary:
	@ vendor/bin/phpcs -s --report=summary

lint-php-report:
	@ vendor/bin/phpcs --report-file=custom/phpcs-report.txt

lint-php-fix:
	@ vendor/bin/phpcbf

## Javascript code-style
lint-js:
	@ npx eslint .

lint-js-fix:
	@ npx eslint --fix .

## CSS code-style
lint-css:
	@ npx gulp lint-css

lint-css-fix:
	@ npx gulp lint-css-fix

## Tests
#test-e2e:
#	cd ./custom/dev-env && \
#	docker-compose exec -w "/project" test_php vendor/bin/codecept build && \
#	docker-compose exec -w "/project" test_php vendor/bin/codecept run acceptance
#
#vnc:
#	# sudo apt-get -y install tigervnc-common
#	# vncpasswd ./tests/.vnc-passwd
#	# password is "secret" (default for Selenium docker-images)
#	vncviewer -passwd ./tests/.vnc-passwd localhost::5900 &
#
#dev-env--shell-test:
#	cd ./custom/dev-env && docker-compose exec test_php bash

## Development environment

### Setup
dev-env--up:
	make wp-core-download
	make dev-env--download
	cd ./custom/dev-env && make up
	@ echo "\nWaiting for mysql..."
	sleep 5
	make dev-env--install

wp-core-download:
	rm -rf ./custom/wp-core
	git clone --depth=1 --branch=5.8.3 git@github.com:WordPress/WordPress.git ./custom/wp-core
	rm -rf ./custom/wp-core/.git

dev-env--download:
	rm -fr ./custom/dev-env && \
	mkdir -p ./custom/dev-env && \
	cd ./custom/dev-env && \
	git clone -b 5.4.42 --depth=1 -- git@github.com:wodby/docker4wordpress.git . && \
	rm ./docker-compose.override.yml && \
	cp ../../tools/dev-env/docker-compose.yml . && \
	cp ../../tools/dev-env/.env . && \
	cp ../../tools/dev-env/wp-config.php ../wp-core/

dev-env--install:
	cd ./custom/dev-env && \
	make wp 'core install --url="http://szed.docker.local:8000/" --title="Dev site" --admin_user="admin" --admin_password="admin" --admin_email="admin@docker.local" --skip-email' && \
	make wp 'plugin activate szed' && \
	\
	docker-compose exec mariadb mysql -uroot -ppassword -e "create database wordpress_test;" && \
	docker-compose exec mariadb mysql -uroot -ppassword -e "GRANT ALL PRIVILEGES ON wordpress_test.* TO 'wordpress'@'%';" && \
	docker-compose exec test_php wp core install --url="http://test.szed.docker.local:8000/" --title="Testing site" --admin_user="admin" --admin_password="admin" --admin_email="admin@docker.local" --skip-email && \
	docker-compose exec test_php wp plugin activate szed

### Regular commands
dev-env--start:
	cd ./custom/dev-env && make start

dev-env--stop:
	cd ./custom/dev-env && make stop

dev-env--prune:
	cd ./custom/dev-env && make prune

dev-env--restart:
	cd ./custom/dev-env && make stop
	cd ./custom/dev-env && make start

dev-env--shell:
	cd ./custom/dev-env && make shell

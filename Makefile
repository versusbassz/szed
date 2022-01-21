default:
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

default:
	@ echo "It's empty task."

ci:
	composer validate

build:
	composer validate
	composer install
	npm ci
	npx gulp

release:
	composer validate
	composer install --no-dev
	npm ci
	npx gulp release

	mkdir -p ./dist
	rm -rf ./dist/*

	cp ./composer.json ./dist
	cp ./composer.lock ./dist
	cp ./package.json ./dist
	cp ./package-lock.json ./dist
	cp ./README.md ./dist
	cp ./szed.php ./dist
	cp ./webpack.config.js ./dist
	cp ./webpack.config.js ./dist
	cp -r ./assets ./dist
	cp -r ./inc ./dist
	cp -r ./vendor ./dist
	cp -r ./views ./dist

	make build

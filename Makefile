default:
	@ echo "It's empty task."

ci:
	composer validate

build:
	composer validate
	composer install
	npm ci

app_name=tramita
build_directory=$(CURDIR)/build
sign_directory=$(build_directory)/sign
cert_directory=$(HOME)/.nextcloud/certificates

all: dev-setup build-js-production

dev-setup: composer-install npm-install

composer-install:
	composer install --prefer-dist

npm-install:
	npm ci

build-js:
	npm run dev

build-js-production:
	npm run build

watch:
	npm run watch

lint:
	npm run lint
	composer run lint

lint-fix:
	npm run lint:fix
	composer run cs:fix

test:
	composer run test

clean:
	rm -rf js/
	rm -rf node_modules/
	rm -rf vendor/
	rm -rf build/

appstore:
	mkdir -p $(sign_directory)/$(app_name)
	rsync -a \
		--exclude=/.git \
		--exclude=/.github \
		--exclude=/build \
		--exclude=/docs \
		--exclude=/node_modules \
		--exclude=/src \
		--exclude=/tests \
		--exclude=/.eslintrc.js \
		--exclude=/webpack.js \
		--exclude=/package.json \
		--exclude=/package-lock.json \
		--exclude=/composer.json \
		--exclude=/composer.lock \
		--exclude=/Makefile \
		--exclude=/.php-cs-fixer.dist.php \
		--exclude=/.gitignore \
		$(CURDIR)/ $(sign_directory)/$(app_name)
	tar -czf $(build_directory)/$(app_name).tar.gz \
		-C $(sign_directory) $(app_name)

.PHONY: all dev-setup composer-install npm-install build-js build-js-production watch lint lint-fix test clean appstore

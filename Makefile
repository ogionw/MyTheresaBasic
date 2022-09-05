install:
	composer create-project symfony/skeleton:"6.1.*" MyTheresaBasic
	cp ProductController.php MyTheresaBasic/src/Controller/ProductController.php
	cp productsDefault.json MyTheresaBasic/public/products.json
	cp productsDefault.json MyTheresaBasic/productsDefault.json
	cd MyTheresaBasic; \
	composer require api -n; \
	composer require --dev symfony/test-pack --dev; \
	composer require symfony/browser-kit symfony/http-client --dev
	mkdir MyTheresaBasic/tests/Api;\
	cp AcceptanceTest.php MyTheresaBasic/tests/Api/AcceptanceTest.php
start:
	cd MyTheresaBasic; \
	symfony server:start -d; \
	symfony open:local
test:
	cd MyTheresaBasic; \
	bin/phpunit --stop-on-failure
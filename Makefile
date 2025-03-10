.PHONY: setup-db-dev setup setup-db setup-dev

setup-db-dev:
	@echo "Delete old database if exists..."
	symfony console doctrine:database:drop --force --if-exists

	@echo "Create new database..."
	symfony console doctrine:database:create

	@echo "Apply migrations..."
	symfony console doctrine:migrations:migrate --no-interaction

	@echo "Apply fixtures..."
	symfony console doctrine:fixtures:load --no-interaction

	@echo "Done!"

setup-db:
	@echo "Delete old database if exists..."
	symfony console doctrine:database:drop --force --if-exists

	@echo "Create new database..."
	symfony console doctrine:database:create

	@echo "Apply migrations..."
	symfony console doctrine:migrations:migrate --no-interaction

	@echo "Done!"

setup-dev:
	@echo "Installing composer dependencies..."
	composer install

	@echo "Installing node dependencies..."
	npm install

	@echo "Building assets..."
	npm run build

	@echo "Setting up database..."
	$(MAKE) setup-db-dev

	@echo "Done!"

setup:
	@echo "Installing composer dependencies..."
	composer install

	@echo "Installing node dependencies..."
	npm install

	@echo "Building assets..."
	npm run build

	@echo "Setting up database..."
	$(MAKE) setup-db

	@echo "Done!"

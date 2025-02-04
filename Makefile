.PHONY: setup-db setup

setup-db:
	@echo "Delete old database if exists..."
	symfony console doctrine:database:drop --force --if-exists

	@echo "Create new database..."
	symfony console doctrine:database:create

	@echo "Apply migrations..."
	symfony console doctrine:migrations:migrate --no-interaction

	@echo "Apply fixtures..."
	symfony console doctrine:fixtures:load --no-interaction

	@echo "Done!"

setup:
	@echo "Installing dependencies..."
	composer install

	@echo "Setting up database..."
	$(MAKE) setup-db

	@echo "Done!"

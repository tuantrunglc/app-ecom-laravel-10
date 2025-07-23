.PHONY: help build up down restart logs shell db-shell migrate seed install cache-clear

help: ## Hiển thị trợ giúp
	@echo "Available commands:"
	@echo "  build       - Build Docker containers"
	@echo "  up          - Start containers"
	@echo "  down        - Stop containers"
	@echo "  restart     - Restart containers"
	@echo "  logs        - View application logs"
	@echo "  shell       - Access application shell"
	@echo "  db-shell    - Access database shell"
	@echo "  migrate     - Run database migrations"
	@echo "  seed        - Run database seeders"
	@echo "  install     - Install dependencies"
	@echo "  cache-clear - Clear all caches"

build: ## Build containers
	docker-compose build

up: ## Start containers
	docker-compose up -d

down: ## Stop containers
	docker-compose down

restart: ## Restart containers
	docker-compose restart

logs: ## View logs
	docker-compose logs -f app

shell: ## Access app shell
	docker-compose exec app bash

db-shell: ## Access database shell
	docker-compose exec db mysql -uroot -proot_password

migrate: ## Run migrations
	docker-compose exec app php artisan migrate

seed: ## Run seeders
	docker-compose exec app php artisan db:seed

install: ## Install dependencies
	docker-compose exec app composer install

cache-clear: ## Clear caches
	docker-compose exec app php artisan optimize:clear

setup: ## Complete setup
	cp .env.docker .env
	docker-compose up -d --build
	sleep 10
	docker-compose exec app composer install
	docker-compose exec app php artisan key:generate
	docker-compose exec app php artisan migrate --force
	docker-compose exec app php artisan db:seed --force
	docker-compose exec app php artisan storage:link
	docker-compose exec app php artisan optimize:clear
	docker-compose exec app php artisan config:cache

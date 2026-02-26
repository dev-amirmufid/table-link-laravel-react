.PHONY: help build up down restart logs migrate seed fresh logs-backend logs-mysql

help:
	@echo "========================================="
	@echo "  TableLink Docker Management"
	@echo "========================================="
	@echo ""
	@echo "Available commands:"
	@echo "  make build         - Build Docker images"
	@echo "  make up           - Start all containers"
	@echo "  make down         - Stop all containers"
	@echo "  make restart      - Restart all containers"
	@echo "  make logs         - View all container logs"
	@echo "  make logs-backend - View backend container logs"
	@echo "  make logs-mysql   - View MySQL container logs"
	@echo "  make migrate      - Run database migrations"
	@echo "  make docs         - Generate API documentation"
	@echo "  make seed         - Seed database with sample data"
	@echo "  make fresh        - Fresh migrate + seed database"
	@echo "  make shell        - Enter backend container shell"
	@echo ""

build:
	docker-compose build --no-cache

up:
	docker-compose up -d

down:
	docker-compose down

restart:
	docker-compose restart

logs:
	docker-compose logs -f

logs-backend:
	docker-compose logs -f backend

logs-mysql:
	docker-compose logs -f mysql

migrate:
	docker exec tablelink_backend php artisan migrate --force

seed:
	docker exec tablelink_backend php artisan db:seed --force

fresh:
	docker exec tablelink_backend php artisan migrate:fresh --seed --force

docs:
	docker exec tablelink_backend php artisan l5-swagger:generate

shell:
	docker exec -it tablelink_backend sh

# Development commands
dev-up:
	docker-compose up -d

dev-down:
	docker-compose down

dev-logs:
	docker-compose logs -f

all: up

up:
	docker-compose up -d --build

down:
	docker-compose down

re: down up

logs:
	docker-compose logs -f

clean:
	docker-compose down -v --rmi local

.PHONY: all up down re logs clean

up:
	docker compose up -d --build

down:
	docker compose down

npm:
	npm install
	npm run dev

db-install:
	docker compose exec -it php bin/console d:s:u --force
	docker compose exec -it php bin/console d:m:s:u

start: up npm db-install
	docker compose exec -it php bin/console gos:websocket:server -vvv

up:
	mkdir -p mysql-data
	chmod a+rw mysql-data
	docker-compose up

down:
	docker-compose down

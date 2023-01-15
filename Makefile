up:
	mkdir -p mysql-data
	chmod a+rw mysql-data
	sudo docker-compose up

down:
	docker-compose down

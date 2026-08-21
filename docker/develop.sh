#!/bin/bash

#docker run -v docker start mysql
# docker run --name hsb-mysql -e MYSQL_ROOT_PASSWORD=my_crazy_super_secret_root_password -e MYSQL_DATABASE=hsbit -e MYSQL_USER=hsbit -e MYSQL_PASSWORD=whateverdood -d mysql
docker run -d hsb-mysql
#docker run -d -v ~/Documents/hsbyhead/hsb-it/:/var/www/html -p $(boot2docker ip)::80   --link hsb-mysql:mysql --name=hsbit hsbit
docker run --link hsb-mysql:mysql -d -p 40000:80 --name=hsb-it -v ~/Documents/hsbyhead/hsb-it/:/var/www/html \
-v ~/Documents/hsbyhead/hsb-it-storage:/var/lib/hsbit --env-file docker.env hsb-test

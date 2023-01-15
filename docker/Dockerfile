FROM ubuntu:16.04

RUN apt-get update
RUN apt-get install -y php7.0-mysql apache2 php libapache2-mod-php

WORKDIR /var/www/html
EXPOSE 80

CMD ["apache2ctl", "-D", "FOREGROUND"]

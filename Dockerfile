FROM ubuntu:16.04

RUN apt-get update
RUN apt-get install -y apache2 php libapache2-mod-php php-mcrypt php-mysql

WORKDIR /var/www/html
EXPOSE 80
CMD ["apache2-foreground"]

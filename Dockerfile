FROM ubuntu

RUN export DEBIAN_FRONTEND=noninteractive

RUN ln -fs /usr/share/zoneinfo/Europe/Madrid /etc/localtime
RUN apt-get update
RUN apt-get install -y -q apache2 php libapache2-mod-php php-mysql

EXPOSE 80
CMD /usr/sbin/apache2ctl -D FOREGROUND
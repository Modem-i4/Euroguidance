FROM ubuntu:22.04

ENV DEBIAN_FRONTEND=noninteractive

RUN apt-get update && \
    apt-get install -y software-properties-common lsb-release && \
    add-apt-repository ppa:ondrej/php -y && \
    apt-get update && \
    apt-get install -y apache2 php8.4 libapache2-mod-php8.4 php8.4-mysql php8.4-xml php8.4-curl php8.4-mbstring php8.4-zip php8.4-gd unzip && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite

COPY /apache/000-default.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html

CMD ["apachectl", "-D", "FOREGROUND"]

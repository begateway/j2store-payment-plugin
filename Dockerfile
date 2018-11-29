FROM joomla:3.8.7-php7.0-apache

RUN apt-get update && \
    apt-get install -y --no-install-recommends unzip wget && \
    rm -rf /var/lib/apt/lists/*

# j2store
RUN wget -c -t 0 -O j2store.zip http://www.j2store.org/latest.html && \
    mkdir /usr/src/j2store && \
    unzip j2store.zip -d /usr/src/j2store && \
    rm j2store.zip && \
    chown -R www-data:www-data /usr/src/j2store

RUN mkdir -p /var/www/html/plugins/j2store/payment_begateway && \
    chown -R www-data.www-data /var/www/html/plugins && \
    chmod 0755 /var/www/html/plugins/j2store/payment_begateway

# Utiliser une image de base avec Apache, PHP et MySQL (par exemple, ubuntu:20.04)
FROM ubuntu

# Mettre à jour les paquets et installer les logiciels nécessaires
RUN apt-get update > /dev/null
RUN DEBIAN_FRONTEND=noninteractive apt-get -y install apache2 php libapache2-mod-php > /dev/null

# Copier les fichiers de configuration Apache dans le conteneur
COPY config/apache-config.conf /etc/apache2/sites-available/000-default.conf

RUN rm /var/www/html/index.html

# Copier les fichiers PHP de votre application dans le conteneur (par exemple, dans /var/www/html)
RUN mkdir /var/www/html/pics
COPY sources/pics/* /var/www/html/pics/
COPY sources/index.php /var/www/html/index.php
COPY sources/robots.txt /var/www/html/robots.txt
COPY sources/stylesheet.css /var/www/html/stylesheet.css

# Exposer le port 80 pour le serveur web
EXPOSE 80

# Démarrer les services Apache et MySQL lorsque le conteneur est lancé
CMD service apache2 start && tail -f /dev/null

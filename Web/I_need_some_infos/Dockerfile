# Utiliser une image de base avec Apache, PHP et MySQL (par exemple, ubuntu:20.04)
FROM ubuntu

# Mettre à jour les paquets et installer les logiciels nécessaires
RUN apt-get update && apt-get -y upgrade
RUN DEBIAN_FRONTEND=noninteractive apt-get -y install apache2 php mysql-server php-mysql libapache2-mod-php

# Copier les fichiers de configuration Apache dans le conteneur
COPY config/apache-config.conf /etc/apache2/sites-available/000-default.conf

RUN rm /var/www/html/index.html

# Copier les fichiers PHP de votre application dans le conteneur (par exemple, dans /var/www/html)
COPY sources/* /var/www/html/

# Définir le mot de passe root MySQL (personnalisez selon vos besoins)
# RUN echo "mysql-server mysql-server/root_password password your_password" | debconf-set-selections
# RUN echo "mysql-server mysql-server/root_password_again password your_password" | debconf-set-selections

# Exposer le port 80 pour le serveur web
EXPOSE 80

# Démarrer les services Apache et MySQL lorsque le conteneur est lancé
CMD service apache2 start && service mysql start && tail -f /dev/null

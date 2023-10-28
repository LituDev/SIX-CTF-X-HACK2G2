# Utiliser une image de base avec Apache, PHP et MySQL (par exemple, ubuntu:20.04)
FROM ubuntu

# Mettre à jour les paquets et installer les logiciels nécessaires
RUN apt-get update --fix-missing
RUN DEBIAN_FRONTEND=noninteractive apt-get -y install apache2 php libapache2-mod-php default-jdk curl sudo groff

WORKDIR /var/www/html/
RUN rm /var/www/html/index.html

# Copier les fichiers PHP de votre application dans le conteneur (par exemple, dans /var/www/html)
COPY web/* /var/www/html/

# Interdire de lister /tmp
RUN chmod 333 /tmp/

# Création de merciol
RUN useradd -ms /bin/bash merciol
RUN chown -R root:root /home/merciol/
RUN chmod 555 /home/merciol

# Mise en place du flag
COPY flag.txt /home/merciol/flag.txt
RUN chown root:merciol  /home/merciol/flag.txt
RUN chmod 440  /home/merciol/flag.txt



RUN echo "www-data ALL=(merciol) NOPASSWD: /usr/bin/pic" >> /etc/sudoers

# Exposer le port 80 pour le serveur web
EXPOSE 80

# Démarrer les services Apache et MySQL lorsque le conteneur est lancé
CMD service apache2 start && tail -f /dev/null

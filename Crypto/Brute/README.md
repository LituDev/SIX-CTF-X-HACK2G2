# Brute

Auteur : Neoreo  
Type : Crypto 
***Difficulté : Facile***

### Enoncé : 

Retrouve le mot de passe de l'utilisateur root

Format du flag IUT{password_MD5(password)}
Exemple : IUT{football_37b4e2d82900d5e94b8da524fbeb33c0}

Commande linux pour générer le hash :  
echo -n MOTDEPASSEICI | md5sum
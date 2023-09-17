# Brute

On récupère deux fichiers `passwd` et `shadow`.  
Ce sont deux fichiers contenant des informations sur les utilisateurs, shadow contient notamment les mots de passes chiffrés des utilisateurs (SHA-512 Crypt).  
Pour cracker le mot de passe, on utilise le classico-classique Rockyou.txt  
En réalité, seul le hash du mot de passe est important :  
`$6$iODd0YaH$BA2G28eil/ZUZAV5uNaiNPE0Pa6XHWUFp7uNTp2mooxwa4UzhfC0kjpzPimy1slPNm9r/9soRw8KqrSgfDPfI0`

***Plusieurs possibilités***  

***Avec HashCat***  
```bash
# On créé un fichier contenant le hash
echo '$6$iODd0YaH$BA2G28eil/ZUZAV5uNaiNPE0Pa6XHWUFp7uNTp2mooxwa4UzhfC0kjpzPimy1slPNm9r/9soRw8KqrSgfDPfI0' > hash.txt
# On applique le commande
# -m 1800 -> exprime le type du hash (c.f. https://hashcat.net/wiki/doku.php?id=example_hashes)
# -o précise l'output
# -a 0 précise l'attaque par wordlist
hashcat -m 1800 -o cleartext.txt -a 0 hash.txt /usr/share/wordlists/rockyou.txt
```

ou

***Avec Jhon the Ripper***  
```bash
# On doit fusionner les deux fichiers avec unshadow
unshadow passwd shadow > unshadowed.txt
# On crack depuis le fichier
john --format=sha512crypt --wordlist=/usr/share/wordlists/rockyou.txt unshadow
```

## FLAG : IUT{estrellita_c26af9f32815ec696fc19aedde845107}

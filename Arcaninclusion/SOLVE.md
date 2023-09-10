# Solve - Arcaninclusion

Lorsqu'on arrive sur le site en question, nous pouvons voir qu'il permet d'afficher 10 images de Pokémon. En examinant l'URL, nous remarquons que l'argument img est utilisé pour spécifier quelle image afficher.

***Exemple d'URLs :***


http://localhost:8080/index.php?img=Pokemon2.jpeg  
http://localhost:8080/index.php?img=Pokemon3.jpeg

Nous pouvons donc déjà, en plus de l'indice du titre, soupçonner une ***LFI (Local File Inclusion)*** qui pourrait nous permettre de récupérer le code source, comme le demande l'énoncé.

Dans ce type de situation, où j'ai personnellement fait plusieurs erreurs par le passé, nous cherchons à lire des fichiers tels que /etc/passwd, qui sont lisibles par tout le monde. Cependant, ici, notre objectif est de ***trouver le code source de la page (celui de index.php)*** pour récupérer le flag. Nous allons donc nous concentrer sur cette tâche. 

***En examinant le code source, nous constatons que les images sont encodées en base64.*** Cela nous donne déjà des indices sur la manière dont nous pourrions récupérer le code plus tard.

***Notre objectif est donc de trouver la charge utile (payload) appropriée à insérer dans l'argument img.***
En utilisant gobuster, nous découvrons en moins de 2 secondes qu'un ***dossier nommé "pics/" existe***, et nous pouvons supposer qu'il contient les images en raison de son nom.

Commande gobuster utilisée :  

```bash
gobuster dir -u http://localhost:8080/ -w /usr/share/wordlists/seclists/Discovery/Web-Content/directory-list-2.3-big.txt
```
Résultat : 
```
===============================================================
Starting gobuster in directory enumeration mode
===============================================================
/pics                 (Status: 301) [Size: 311] [--> http://localhost:8080/pics/]
```

Ce dossier contient un fichier "index.php" qui redirige vers le premier "index.php", il n'est donc pas facile d'accéder aux images en naviguant uniquement dans le dossier de cette manière :   
http://localhost:8080/pics/     
Cependant, nous pouvons le vérifier en modifiant l'URL comme suit et en constatant que l'image s'affiche :   
http://localhost:8080/pics/Pokemon1.jpeg  

Nous pouvons donc en déduire que le code PHP cherche les images dans le dossier "pics".

Donc, pour trouver la charge utile (payload) appropriée, il suffit de remonter d'un niveau dans l'arborescence en utilisant l'URL suivante :   
***http://localhost:8080/index.php?img=../index.php***  


Le code source d'index.php se trouve maintenant en base64 dans le code source de la page. Il suffit de le décoder à l'aide d'outils comme CyberChef, disponible ici :   
https://gchq.github.io/CyberChef/.

Une simple recherche avec CTRL + F pour "IUT{" nous permet de trouver le flag.

### Flag : IUT{L0C4L_F1L3_1NCLU510N_15_4_PR377Y_C0MM0N_VULN}

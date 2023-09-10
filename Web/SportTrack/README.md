L'association sportive a sorti une nouvelle application web pour ses participants.  
On vous a demandé de la testé, retrouver le nom prénom lié à l'administrateur.

> Format du flag: ``IUT{nom_prenom}`` où nom et prénom sont en minuscule et sans accent.


## Solution

Il est avant tout nécessaire de récupérer l'email, qui se trouvait juste en commentaire dans le header des pages.

On peut voir dans le code source une brèche au niveau d'une requête SQL. Est-ce un manque d'attention ou de flemmardise venant du développeur ?  

Toujours est-il que ceci va nous permettre d'exécuter n'importe quelle requête SQL, dont celles nous permettant de récupérer le nom et prénom de l'administrateur.  

L'injection du code malveillant ne pouvait pas se trouver au niveau du formulaire de connexion, les requêtes y sont préparées, cependant lors de l'upload d'un fichier json, les champs n'étant pas entièrement vérifié et la requête non préparée, la faille était là. 

Dans le champs date, il nous suffisait de mettre une chaîne de ce type: 

```
', (SELECT nom || ' ' || prenom FROM utilisateurs WHERE email = 'admin@sporttrack.fr'), 'monemail@email.com') --
```

Plusieurs variantes de la requête était possible. L'extraction des données pouvaient aussi se faire à travers plusieurs requêtes.

Vous retrouverez le fichier json utilisé pour la résolution du challenge à la racine du dépôt.
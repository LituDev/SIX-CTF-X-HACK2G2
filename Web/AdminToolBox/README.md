# AdminToolBox

Une fois le challenge déployer, nous pouvions remarquer qu'il y avait un NGINX en facade de plusieurs micro-services séparé l'un de l'autre.  

Ils étaient tous flaggable indépendamment les uns des autres.

## Etape 1

> Accéder au /secret.txt du service d'authentification

En regardant le code source, nous remarquions que ce micro-service utilisait une façon très simple de savoir quelle route était demandé. Cette route est passé par l'intermédiaire de la query HTTP `route`. Ainsi, `http://chall.sixctf.fr?route=test` demandait la route test.

Le système avait aussi une façon bien à lui et pas sécurisé de savoir les routes possibles. En effet, il venait lister toutes les routes du dossier `routes/` mais ne servait uniquement lors de retour d'erreur et non à vérifié.  
Enfin, à l'aide d'une méthode appelé Path Traversal, il nous était possible d'insérer un nom de route de sorte qu'il remonte dans l'arborescence des fichiers et qu'il ne pointe plus vers le dossier `routes/` mais vers le fichier `secret.txt` disponible à la racine du serveur.

A la base, si nous donnions juste `http://chall.sixctf.fr?route=login`, le script allait faire une recherche avec : `__DIR__/routes/login*` permettait ainsi de trouver le fichier `routes/login.php` et de l'exécuter. Ainsi, en envoyant : `?route=../../secret.txt` nous remontions suffisament l'arborescence pour le trouver. Pour trouver le nombre de `../` à mettre, il était possible de tester plusieurs fois pour continuer de remonter jusqu'à trouver le fichier.  

## Etape 2 

**/!\ Attention, cette étape a finalement été retiré car comportait trop de bugs /!\\**

## Etape 3

> Trouver un moyen de pénétrer dans le service du calculateur et récupérer le contenu du /secret.txt

Le calculateur était un service permettant l'interprétation d'équation mathématique et de calcul à l'aide d'eval.  
On peut voir que des filtes y était appliqué pour permettre de ne pas faire n'importe quoi, cependant, le développeur à fait une erreur ! 

Il retire les espaces et remplace les `,` et `.` et `=` en `==`, mais, pour vérifier si le calcul ne contient que chiffres et caractère il suit la logique suivante: 
- Si le calcul contient un `==` alors il prend juste la partie gauche du `==`.
- Si le calcul n'en contient pas, alors il prend tout le calcul.

Ainsi, il nous était possible de placer le code malveillant dans la partie de droite du `==` et de le faire exécuter.

Une autre légère difficulté était que lors d'équation, le calculateur ne retournait que si le résultat était vrai ou faux, donc il nous fallait passer par un autre moyen pour afficher le résultat de notre code. On pouvait utiliser, par exemple, la fonction `print_r` qui permet d'afficher le contenu d'une variable.

Au final, on pouvait soumettre `1=print(exec("cat".chr(32)."/secret.txt"))` et le tour était joué ou bien `1=print(file_get_contents("/secret.txt"))`.
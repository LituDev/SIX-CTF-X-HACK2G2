# Solve

D'abord, j'espère que vous avez bien aimé ce chall car il m'a pris plus de 35h à faire - Eh ouais l'aventure était longue :)

Je l'ai créé à partir d'un Windows mais il me semble qu'il est aussi possible de le faire sous UNIX.

On démarre donc avec un fichier dont, à première vue, l'extension ne ressemble pas à grand chose  

En faisant un `head` sur le fichier on remarque la première ligne 

```bash
head Sauvegarde.dst
```
```
DeSmuME SState
```
Notez que ce fichier est un `State`
En se renseignant, on apprend que DeSmuME est un émulateur de jeux Nintendo DS  
On peut donc commencer par télécharger l'émulateur à cette adresse (pour Windows) : https://sourceforge.net/projects/desmume/

En se renseignant d'avantage, on apprend que DeSmuME fonctionne avec des ROMs qui contiennent les fameux jeux Nintendo DS.  
Maintenant il faut déterminer quel jeu est le bon...  
Si vous aviez pris l'indice, vous saviez que c'était un des pokémons suivants `Diamant`, `Perle` ou `Platine`  
Dans l'autre cas, il fallait ajouter `Or HeartGold`, `Argent SoulSilver` ainsi que les pokémons de la 5ème génération, c'est à dire `Noir` et `Blanc` et `Noir2` et `Blanc2`.

Il était possible de les essayer une par une car la manoeuvre est rapide, il faut télécharger les ROMs et les charger depuis DeSmuME `File -> Open ROM`. Une fois la ROM chargée, il faut essayer de charger la sauvegarde `File -> Load State From`.  
Si un message d'erreur s'affiche alors la ROM n'est pas la bonne, sinon, le jeu se lance.  
La ROM correcte est donc celle ci : https://www.rpgamers.fr/rom-2651-pokemon-version-platine.html

Une fois la ROM trouvée et le jeu lancé, pour une question de practicité, on peut changer la configuration des touches `Config -> Control Config`



Maintenant, la dernière étape, il faut trouver le fameux Motisma.  
Il n'est pas présent dans l'équipe... ça aurait été trop simple ;)
L'idée à avoir était de regarder dans le PC du centre Pokémon où sont stockés tous les Pokémons du joueur. 
(J'aurai pas poussé le chall jusqu'à aller chercher les Pokemons déposés dans la garderie quand même)
Le Motisma est présent et le flag est son nom entouré du wrapper IUT{}, c'est à dire 

## IUT{J41-4D0RE}



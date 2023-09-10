On vient de nous faire part que les sujets de mathématiques étaient finalisés et stockés dans le bureau du responsable du module.  
Vous avez réussi à vous procurer les plans de l'IUT sur le Dark Web et devez tracer le chemin pour rejoindre ce bureau depuis l'entrée située en haut du plan.  
Ce plan nécessite une préparation minutieuse car il devra s'effectuer dans le noir au milieu de ce labyrinthe pour ne pas déclencher l'alarme.  

## Principe

Les murs sont matérialisés par des ``#`` et un espace correspond à un couloir, espaces vides.  
Le but est de trouver un chemin menant de l'entrée se trouvant en haut du labyrinthe à la sortie se trouvant en bas du labyrinthe.  
> Ces entrées et sorties peuvent se trouver n'importe où sur le côté qui convient (haut pour l'entrée et bas pour la sortie).

**Les 3 premiers labyrinthes seront identiques pour pratiquer vos tests**

Répéter l'opération tant que le serveur ne vous a pas donné le flag.

## Format de retour

En prenant en compte le fait que chaque caractère correspond a un point dans un plan à deux dimensions, le point tout en haut à gauche à pour coordonnées ``(0, 0)``.  
Le format de retour sera une liste ordonnée des coordonnées des points à visiter pour rejoindre la sortie incluse.  
Chaque point sera séparé d'un ``|`` et les coordonnées seront séparées par une virgule.

### Exemple:

Pour le plan ci-dessous: 
```
# #######
# #     #
# # #####
# #     #
# ##### #
#     # #
# ##### #
#       #
####### #
```

La réponse à donner sera: ``1,7|7,7|7,8``
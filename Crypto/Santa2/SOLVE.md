# SOLVE - Santa 2, 

Utilise l'outil hashcat pour résoudre un hash spécifique. Il comporte plusieurs paramètres importants :
Notez qu'on sait qu'il commence par 5AN7A

```bash
hashcat -m 0 -a 3 chall.txt 5AN7A?a?a?a?a?a
```
Les options utilisées sont les suivantes :

-m : Spécifie le type de hachage à considérer (0 dans ce cas, car il s'agit d'un hachage MD5). Vous pouvez en apprendre davantage sur les types de hachage disponibles ici.
-a : Détermine le type d'attaque par force brute.
    Le chiffre 3 indique une attaque en force brute pour chaque caractère.
chall.txt : Fichier contenant le hachage à décrypter.
5AN7A?a?a?a?a?a :
    Ce motif signifie que le hachage commence par 5AN7A suivi de 5 caractères.
    La notation ?a indique que la valeur de chaque caractère peut être n'importe quoi, des chiffres aux lettres et caractères spéciaux.

## FLAG : IUT{5AN7A_B0SS}
# Solve -Santa 1

On se retrouve avec un hash dont on ne connait pas la fonction de hashage

En cherchant le type de hash sur https://hashes.com/en/tools/hash_identifier, le site nous retourne 

```
5f10744cd6eccb7784f662759be012c9 - 5AN7A - Possible algorithms: MD5
```

Le hash est trouvé.

On peut aussi le faire avec hashcat, je vous laisse regarder la solution de Santa 2. il suffit de changer le fichier contenant le hash et de préciser `?a?a?a?a?a` et non `5AN7A?a?a?a?a?a`

## Flag : IUT{5AN7A}
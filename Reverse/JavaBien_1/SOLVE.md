# Solve - JavaBien_1
## Solution 1 
Décompiler le fichier .class en ligne

Trouver la String qui permet de déchiffrer le message 
- On voit que la longueur doit être de 13 caractères
- On voit que le caractère en index 0 doit être un j
- On voit que le caractère en index 2 doit être un v
- On voit que tous les caractères doivent être des 'a' hormis ceux d'index 0 et 2

On peut en déduire le mot de passe valide : javaaaaaaaaaa

```bash
java MaClass javaaaaaaaaaa
```

### Flag IUT{javaaaaaaaaaa}
